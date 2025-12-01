<?php

namespace Modules\Document\Services;

use Modules\Document\Entities\DocumentTemplate;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DocumentAudit;
use Modules\Document\Events\DocumentGenerated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentService
{
    public function __construct(
        protected TemplateRendererService $templateRenderer,
        protected SignatureService $signatureService
    ) {}

    /**
     * Genera un documento desde un template
     */
    public function generateFromTemplate(
        DocumentTemplate $template,
        $documentable,
        array $data = [],
        ?string $userId = null
    ): GeneratedDocument {
        return DB::transaction(function () use ($template, $documentable, $data, $userId) {
            $userId = $userId ?? auth()->id();

            // Renderizar el contenido del template
            $renderedHtml = $this->templateRenderer->render($template->content, $data);

            // Generar código único
            $code = $this->generateDocumentCode($template, $documentable);

            // Crear el documento
            $document = GeneratedDocument::create([
                'code' => $code,
                'document_template_id' => $template->id,
                'documentable_id' => $documentable->id,
                'documentable_type' => get_class($documentable),
                'title' => $data['title'] ?? $template->name,
                'content' => json_encode($data),
                'rendered_html' => $renderedHtml,
                'status' => 'draft',
                'generated_by' => $userId,
                'generated_at' => now(),
                'signature_required' => $template->requiresSignature(),
                'signature_status' => $template->requiresSignature() ? 'pending' : null,
                'metadata' => [
                    'template_code' => $template->code,
                    'template_name' => $template->name,
                ],
            ]);

            // Generar PDF
            $pdfPath = $this->generatePDF($document, $renderedHtml, $template);
            $document->update(['pdf_path' => $pdfPath]);

            // Registrar auditoría
            DocumentAudit::log(
                $document->id,
                'created',
                $userId,
                'Documento generado desde template: ' . $template->name
            );

            // Disparar evento
            event(new DocumentGenerated($document, $userId));

            return $document->fresh();
        });
    }

    /**
     * Genera un PDF desde el HTML renderizado
     */
    public function generatePDF(GeneratedDocument $document, string $html, ?DocumentTemplate $template = null): string
    {
        $template = $template ?? $document->template;

        // Configurar PDF
        $pdf = Pdf::loadHTML($html);

        // Configurar tamaño y orientación
        $paperSize = $template->paper_size ?? 'A4';
        $orientation = $template->orientation ?? 'portrait';
        $pdf->setPaper($paperSize, $orientation);

        // Configurar márgenes si existen
        if ($template->margins) {
            $margins = $template->margins;
            $pdf->setOption('margin-top', $margins['top'] ?? 20);
            $pdf->setOption('margin-right', $margins['right'] ?? 15);
            $pdf->setOption('margin-bottom', $margins['bottom'] ?? 20);
            $pdf->setOption('margin-left', $margins['left'] ?? 15);
        }

        // Generar nombre de archivo
        $filename = $this->generatePdfFilename($document);
        $path = "documents/{$document->id}/{$filename}";

        // Guardar PDF
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Regenera el PDF de un documento
     */
    public function regeneratePDF(GeneratedDocument $document): string
    {
        // Validar que el documento no tenga ninguna firma (completa o parcial)
        if ($document->hasAnySignature()) {
            throw new \Exception('No se puede regenerar un documento que tiene firmas. Los documentos con firmas deben mantener su integridad.');
        }

        // Validar que no tenga firmas en proceso
        if ($document->signature_status === 'in_progress') {
            throw new \Exception('No se puede regenerar un documento con firmas en proceso. Cancele el flujo de firmas primero.');
        }

        $template = $document->template;
        $data = json_decode($document->content, true);

        $renderedHtml = $this->templateRenderer->render($template->content, $data);
        $document->update(['rendered_html' => $renderedHtml]);

        $pdfPath = $this->generatePDF($document, $renderedHtml, $template);
        $document->update(['pdf_path' => $pdfPath]);

        DocumentAudit::log(
            $document->id,
            'updated',
            auth()->id(),
            'PDF regenerado'
        );

        return $pdfPath;
    }

    /**
     * Descarga un documento
     */
    public function download(GeneratedDocument $document, bool $signed = false)
    {
        // Determinar qué versión descargar
        if ($signed) {
            $path = $document->getLatestSignedPath() ?? $document->pdf_path;
        } else {
            $path = $document->pdf_path;
        }

        if (!$path || !Storage::disk('private')->exists($path)) {
            throw new \Exception('El archivo PDF no existe');
        }

        DocumentAudit::log(
            $document->id,
            'downloaded',
            auth()->id(),
            'Documento descargado' . ($signed ? ' (versión firmada)' : '')
        );

        return Storage::disk('private')->download($path, $document->code . '.pdf');
    }

    /**
     * Visualiza un documento
     */
    public function view(GeneratedDocument $document, bool $signed = false)
    {
        // Determinar qué versión mostrar
        if ($signed) {
            $path = $document->getLatestSignedPath() ?? $document->pdf_path;
        } else {
            $path = $document->pdf_path;
        }

        if (!$path || !Storage::disk('private')->exists($path)) {
            throw new \Exception('El archivo PDF no existe');
        }

        DocumentAudit::log(
            $document->id,
            'viewed',
            auth()->id(),
            'Documento visualizado' . ($signed ? ' (versión firmada)' : '')
        );

        return Storage::disk('private')->response($path);
    }

    /**
     * Elimina un documento
     */
    public function delete(GeneratedDocument $document): bool
    {
        return DB::transaction(function () use ($document) {
            $userId = auth()->id();

            // Eliminar archivos físicos
            if ($document->pdf_path) {
                Storage::disk('private')->delete($document->pdf_path);
            }
            if ($document->signed_pdf_path) {
                Storage::disk('private')->delete($document->signed_pdf_path);
            }

            // Registrar auditoría
            DocumentAudit::log(
                $document->id,
                'deleted',
                $userId,
                'Documento eliminado'
            );

            // Soft delete
            return $document->delete();
        });
    }

    /**
     * Genera un código único para el documento
     */
    protected function generateDocumentCode(DocumentTemplate $template, $documentable): string
    {
        $prefix = strtoupper(substr($template->code, 0, 3));
        $year = now()->year;
        $month = now()->format('m');

        // Contar documentos del mismo tipo este mes
        $count = GeneratedDocument::where('document_template_id', $template->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return "{$prefix}-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genera el nombre del archivo PDF
     */
    protected function generatePdfFilename(GeneratedDocument $document): string
    {
        return $document->code . '_' . now()->format('YmdHis') . '.pdf';
    }
}
