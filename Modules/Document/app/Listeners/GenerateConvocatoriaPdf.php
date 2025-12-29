<?php

declare(strict_types=1);

namespace Modules\Document\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\JobPosting\Events\JobPostingPublicationRequested;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Document\Entities\DocumentTemplate;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Events\DocumentGenerated;

class GenerateConvocatoriaPdf
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly SignatureService $signatureService
    ) {}

    public function handle(JobPostingPublicationRequested $event): void
    {
        $jobPosting = $event->jobPosting;

        // 1. Obtener plantilla activa
        $template = DocumentTemplate::where('code', 'TPL_CONVOCATORIA_COMPLETA')
            ->where('status', 'active')
            ->first();

        if (!$template) {
            Log::warning('Plantilla TPL_CONVOCATORIA_COMPLETA no encontrada', [
                'job_posting_id' => $jobPosting->id,
            ]);
            return;
        }

        // 2. Obtener perfiles aprobados con todas las relaciones necesarias
        $approvedProfiles = $jobPosting->jobProfiles()
            ->where('status', 'approved')
            ->with([
                'organizationalUnit',
                'requestingUnit.parent',
                'positionCode',
                'requestedBy',
                'reviewedBy',
                'approvedBy'
            ])
            ->get();

        if ($approvedProfiles->isEmpty()) {
            Log::warning('No hay perfiles aprobados para generar convocatoria', [
                'job_posting_id' => $jobPosting->id,
            ]);
            return;
        }

        // 3. Preparar datos para el PDF (texto en mayúsculas según estándar municipal)
        $data = $this->prepareConvocatoriaData($jobPosting, $approvedProfiles);

        // 4. Generar documento usando DocumentService
        $document = $this->documentService->generateFromTemplate(
            template: $template,
            documentable: $jobPosting,
            data: $data
        );

        // 5. Crear workflow de firmas secuencial (sin firmantes aún)
        if ($template->requiresSignature()) {
            $this->signatureService->createWorkflow(
                document: $document,
                signers: [], // Se asignarán en el siguiente listener
                workflowType: $template->getSignatureWorkflowType()
            );
        }

        // 6. Disparar evento de documento generado
        event(new DocumentGenerated($document, auth()->id()));

        Log::info('PDF de convocatoria completa generado exitosamente', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'profiles_count' => $approvedProfiles->count(),
            'total_vacancies' => $approvedProfiles->sum('total_vacancies'),
        ]);
    }

    /**
     * Prepara los datos para la generación del PDF
     */
    private function prepareConvocatoriaData(JobPosting $jobPosting, $approvedProfiles): array
    {
        return [
            'title' => "CONVOCATORIA {$jobPosting->code} - BASES INTEGRADAS",
            'convocatoria_codigo' => mb_strtoupper($jobPosting->code ?? ''),
            'convocatoria_nombre' => mb_strtoupper($jobPosting->name ?? ''),
            'proceso_nombre' => mb_strtoupper($jobPosting->selection_process_name ?? ''),
            'año' => $jobPosting->year,
            'total_perfiles' => $approvedProfiles->count(),
            'total_vacantes' => $approvedProfiles->sum('total_vacancies'),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'perfiles' => $approvedProfiles->map(function($profile) {
                return [
                    'codigo' => mb_strtoupper($profile->code ?? ''),
                    'titulo' => mb_strtoupper($profile->title ?? ''),
                    'nombre_perfil' => mb_strtoupper($profile->profile_name ?? ''),
                    'unidad_organica' => mb_strtoupper($profile->organizationalUnit?->name ?? ''),
                    'vacantes' => $profile->total_vacancies,
                    'tipo_contrato' => mb_strtoupper($profile->contract_type?->label() ?? ''),
                    'regimen_laboral' => mb_strtoupper($profile->work_regime?->label() ?? ''),
                    'nivel_educativo' => mb_strtoupper($profile->education_level_label ?? ''),
                    'experiencia_general' => $profile->general_experience_years?->years ?? 0,
                    'experiencia_especifica' => $profile->specific_experience_years?->years ?? 0,
                    'remuneracion' => $profile->formatted_salary ?? '',
                    'ubicacion' => mb_strtoupper($profile->work_location ?? ''),
                    'funciones_principales' => $this->toUpperArray($profile->main_functions ?? []),
                    'competencias_requeridas' => $this->toUpperArray($profile->required_competencies ?? []),
                    'conocimientos' => $this->toUpperArray($profile->knowledge_areas ?? []),
                ];
            })->toArray(),
        ];
    }

    /**
     * Convierte array de strings a mayúsculas
     */
    private function toUpperArray(array $data): array
    {
        return array_map(fn($item) => is_string($item) ? mb_strtoupper($item) : $item, $data);
    }
}
