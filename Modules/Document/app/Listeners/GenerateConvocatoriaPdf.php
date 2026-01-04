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

        // 2. Obtener perfiles aprobados o activos con todas las relaciones necesarias
        $approvedProfiles = $jobPosting->jobProfiles()
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
                return $profile->organizationalUnit?->name ?? 'ZZZZ'; // Los sin unidad van al final
            })
            ->values(); // Reindexa el array después de ordenar

        if ($approvedProfiles->isEmpty()) {
            Log::warning('No hay perfiles aprobados o activos para generar convocatoria', [
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

        // 5. Verificar si hay jurados titulares activos
        $hasJurors = \Modules\Jury\Entities\JuryAssignment::where('job_posting_id', $jobPosting->id)
            ->where('member_type', \Modules\Jury\Enums\MemberType::TITULAR)
            ->where('is_active', true)
            ->exists();

        // 6. Crear workflow de firmas SOLO si hay jurados y el template lo requiere
        if ($template->requiresSignature() && $hasJurors) {
            $this->signatureService->createWorkflow(
                document: $document,
                signers: [], // Se asignarán en el siguiente listener
                workflowType: $template->getSignatureWorkflowType()
            );

            Log::info('Workflow de firmas creado, esperando asignación de jurados', [
                'job_posting_id' => $jobPosting->id,
                'document_id' => $document->id,
            ]);
        } else {
            Log::info('No se requieren firmas o no hay jurados disponibles', [
                'job_posting_id' => $jobPosting->id,
                'document_id' => $document->id,
                'has_jurors' => $hasJurors,
                'requires_signature' => $template->requiresSignature(),
            ]);
        }

        // 7. Disparar evento de documento generado
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
            'template_code' => 'TPL_CONVOCATORIA_COMPLETA',
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
                    // Justificación y vigencia del contrato
                    'justificacion' => mb_strtoupper($profile->justification_text ?? ''),
                    'vigencia_contrato' => mb_strtoupper($profile->contract_duration ?? '3 MESES'),
                ];
            })->toArray(),
        ];
    }

    /**
     * Convierte array de strings a mayúsculas
     */
    private function toUpperArray(array|null $data): array
    {
        if (empty($data) || !is_array($data)) {
            return [];
        }

        // Asegurar que sea un array secuencial con valores válidos
        return array_values(array_filter(
            array_map(fn($item) => is_string($item) ? mb_strtoupper($item) : (is_array($item) ? '' : $item), $data),
            fn($item) => !empty($item)
        ));
    }
}
