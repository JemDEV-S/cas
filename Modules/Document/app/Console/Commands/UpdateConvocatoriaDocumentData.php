<?php

declare(strict_types=1);

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Services\TemplateRendererService;
use Illuminate\Support\Facades\DB;

class UpdateConvocatoriaDocumentData extends Command
{
    protected $signature = 'convocatoria:update-document-data
                            {job-posting-id : ID de la convocatoria}';

    protected $description = 'Actualiza los datos JSON del documento de convocatoria y regenera el PDF';

    public function __construct(
        private readonly TemplateRendererService $templateRenderer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jobPostingId = $this->argument('job-posting-id');

        // Buscar convocatoria
        $jobPosting = JobPosting::find($jobPostingId);

        if (!$jobPosting) {
            $this->error("❌ Convocatoria #{$jobPostingId} no encontrada");
            return Command::FAILURE;
        }

        $this->info("📋 Convocatoria: {$jobPosting->code} - {$jobPosting->name}");

        // Buscar documento existente
        $document = GeneratedDocument::where('documentable_type', JobPosting::class)
            ->where('documentable_id', $jobPostingId)
            ->whereHas('template', fn($q) => $q->where('code', 'TPL_CONVOCATORIA_COMPLETA'))
            ->first();

        if (!$document) {
            $this->error("❌ No se encontró el documento de convocatoria");
            return Command::FAILURE;
        }

        $this->info("📄 Documento encontrado: {$document->code}");

        try {
            DB::beginTransaction();

            // Obtener perfiles actualizados
            $profiles = $jobPosting->jobProfiles()
                ->whereIn('status', ['approved', 'active'])
                ->with([
                    'organizationalUnit',
                    'requestingUnit.parent',
                    'positionCode',
                    'requestedBy',
                    'reviewedBy',
                    'approvedBy'
                ])
                ->get()
                ->sortBy(function($profile) {
                    return $profile->organizationalUnit?->name ?? 'ZZZZ';
                })
                ->values();

            if ($profiles->isEmpty()) {
                $this->error("❌ No hay perfiles para actualizar");
                DB::rollBack();
                return Command::FAILURE;
            }

            $this->info("📊 Perfiles encontrados: {$profiles->count()}");

            // Preparar datos actualizados
            $data = $this->prepareConvocatoriaData($jobPosting, $profiles);

            // Actualizar el contenido JSON del documento
            $document->update([
                'content' => json_encode($data),
            ]);

            // Renderizar nuevo HTML
            $renderedHtml = $this->templateRenderer->render(
                $document->template->content,
                $data
            );

            $document->update(['rendered_html' => $renderedHtml]);

            // Generar nuevo PDF usando DomPDF
            $this->info('🔄 Generando PDF...');

            $pdf = \PDF::loadHTML($renderedHtml)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => false,
                    'defaultFont' => 'Arial',
                ]);

            // Guardar PDF
            $directory = "documents/{$document->id}";
            \Storage::disk('private')->makeDirectory($directory);

            $timestamp = now()->format('YmdHis');
            $filename = "{$document->code}_{$timestamp}.pdf";
            $pdfPath = "{$directory}/{$filename}";

            \Storage::disk('private')->put($pdfPath, $pdf->output());

            $document->update(['pdf_path' => $pdfPath]);

            DB::commit();

            $this->info('✅ Documento actualizado exitosamente');
            $this->line("   Ruta: {$document->pdf_path}");
            $this->line("   Perfiles incluidos: {$profiles->count()}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error: {$e->getMessage()}");
            $this->line($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function prepareConvocatoriaData(JobPosting $jobPosting, $profiles): array
    {
        return [
            'template_code' => 'TPL_CONVOCATORIA_COMPLETA',
            'title' => "CONVOCATORIA {$jobPosting->code} - BASES INTEGRADAS",
            'convocatoria_codigo' => mb_strtoupper($jobPosting->code ?? ''),
            'convocatoria_nombre' => mb_strtoupper($jobPosting->name ?? ''),
            'proceso_nombre' => mb_strtoupper($jobPosting->selection_process_name ?? ''),
            'año' => $jobPosting->year,
            'total_perfiles' => $profiles->count(),
            'total_vacantes' => $profiles->sum('total_vacancies'),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'perfiles' => $profiles->map(function($profile) {
                return [
                    'codigo' => mb_strtoupper($profile->code ?? ''),
                    'titulo' => mb_strtoupper($profile->title ?? ''),
                    'nombre_perfil' => mb_strtoupper($profile->profile_name ?? ''),
                    'codigo_cargo' => mb_strtoupper($profile->positionCode?->code ?? ''),
                    'nombre_cargo' => mb_strtoupper($profile->positionCode?->name ?? ''),
                    'unidad_organica' => mb_strtoupper($profile->organizationalUnit?->name ?? ''),
                    'vacantes' => $profile->total_vacancies,
                    'tipo_contrato' => mb_strtoupper($profile->contract_type ? \Modules\JobProfile\Enums\ContractTypeEnum::from($profile->contract_type)->label() : ''),
                    'regimen_laboral' => mb_strtoupper($profile->work_regime_label ?? ''),
                    'nivel_educativo' => mb_strtoupper($profile->education_level_label ?? ''),
                    'area_estudios' => mb_strtoupper($profile->career_field ?? ''),
                    'colegiatura_requerida' => $profile->colegiatura_required ? 'SÍ' : 'NO',
                    'experiencia_general' => mb_strtoupper($profile->general_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
                    'experiencia_especifica' => mb_strtoupper($profile->specific_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
                    'experiencia_especifica_descripcion' => mb_strtoupper($profile->specific_experience_description ?? ''),
                    'remuneracion' => $profile->formatted_salary ?? '',
                    'ubicacion' => mb_strtoupper($profile->work_location ?? ''),
                    'funciones_principales' => $this->toUpperArray(
                        is_array($profile->main_functions) ? $profile->main_functions : []
                    ),
                    'competencias_requeridas' => $this->toUpperArray(
                        is_array($profile->required_competencies) ? $profile->required_competencies : []
                    ),
                    'conocimientos' => $this->toUpperArray(
                        is_array($profile->knowledge_areas) ? $profile->knowledge_areas : []
                    ),
                    'capacitaciones' => $this->toUpperArray(
                        is_array($profile->required_courses) ? $profile->required_courses : []
                    ),
                    // NUEVOS CAMPOS
                    'justificacion' => mb_strtoupper($profile->justification_text ?? ''),
                    'vigencia_contrato' => mb_strtoupper($profile->contract_duration ?? '3 MESES'),
                ];
            })->toArray(),
        ];
    }

    private function toUpperArray(array|null $data): array
    {
        if (empty($data) || !is_array($data)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn($item) => is_string($item) ? mb_strtoupper($item) : (is_array($item) ? '' : $item), $data),
            fn($item) => !empty($item)
        ));
    }
}
