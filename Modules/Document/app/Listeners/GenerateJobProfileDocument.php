<?php

namespace Modules\Document\Listeners;

use Modules\JobProfile\Events\JobProfileApproved;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Entities\DocumentTemplate;
use Illuminate\Support\Facades\Log;

class GenerateJobProfileDocument
{
    public function __construct(
        protected DocumentService $documentService,
        protected SignatureService $signatureService
    ) {}

    /**
     * Maneja el evento JobProfileApproved y genera el documento oficial
     */
    public function handle(JobProfileApproved $event): void
    {
        try {
            $jobProfile = $event->jobProfile;

            // Obtener template de perfil de puesto
            $template = DocumentTemplate::where('code', 'TPL_JOB_PROFILE')
                ->where('status', 'active')
                ->first();

            if (!$template) {
                Log::warning('Template TPL_JOB_PROFILE no encontrado', [
                    'job_profile_id' => $jobProfile->id,
                ]);
                return;
            }

            // Preparar datos para el documento
            $data = $this->prepareDocumentData($jobProfile);

            // Generar el documento
            $document = $this->documentService->generateFromTemplate(
                $template,
                $jobProfile,
                $data,
                $event->approvedBy
            );

            Log::info('Documento de perfil generado exitosamente', [
                'job_profile_id' => $jobProfile->id,
                'document_id' => $document->id,
                'document_code' => $document->code,
            ]);

            // Si el template requiere firma, crear flujo de firmas
            if ($template->requiresSignature()) {
                $this->createSignatureWorkflow($document, $jobProfile);
            }

        } catch (\Exception $e) {
            Log::error('Error al generar documento de perfil', [
                'job_profile_id' => $event->jobProfile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Prepara los datos del job profile para el template
     */
    protected function prepareDocumentData($jobProfile): array
    {
        return [
            'title' => 'Perfil de Puesto - ' . $jobProfile->profile_name,
            'code' => $jobProfile->code,
            'job_profile' => $jobProfile,

            // Datos básicos
            'profile_title' => $jobProfile->title,
            'profile_name' => $jobProfile->profile_name,
            'position_code' => $jobProfile->positionCode?->code,
            'position_name' => $jobProfile->positionCode?->name,

            // Unidad organizacional
            'organizational_unit' => $jobProfile->organizationalUnit?->name,
            'parent_organizational_unit' => $jobProfile->requestingUnit?->parent?->name,
            'requesting_unit' => $jobProfile->requestingUnit?->name,
            'required_position' => $jobProfile->positionCode?->name,

            // Datos del puesto
            'job_level' => $jobProfile->job_level,
            'contract_type' => $jobProfile->contract_type,
            'salary_range' => $jobProfile->salary_range,
            'salary_min' => $jobProfile->salary_min,
            'salary_max' => $jobProfile->salary_max,
            'description' => $jobProfile->description,
            'mission' => $jobProfile->mission,
            'working_conditions' => $jobProfile->working_conditions,

            // Requisitos académicos
            'education_level' => $jobProfile->education_level_label,
            'career_field' => $jobProfile->career_field,
            'title_required' => $jobProfile->title_required,
            'colegiatura_required' => $jobProfile->colegiatura_required ? 'Sí' : 'No',

            // Experiencia
            'general_experience_years' => $jobProfile->general_experience_years?->toHuman() ?? 'Sin experiencia',
            'specific_experience_years' => $jobProfile->specific_experience_years?->toHuman() ?? 'Sin experiencia',
            'specific_experience_description' => $jobProfile->specific_experience_description,
            'total_experience_years' => $jobProfile->total_experience_years,

            // Capacitación y conocimientos
            'required_courses' => $jobProfile->required_courses ?? [],
            'knowledge_areas' => $jobProfile->knowledge_areas ?? [],
            'required_competencies' => $jobProfile->required_competencies ?? [],

            // Funciones
            'main_functions' => $jobProfile->main_functions ?? [],

            // Régimen laboral
            'work_regime' => $jobProfile->work_regime_label,
            'justification' => $jobProfile->justification_text,

            // Contrato
            'contract_duration' => $jobProfile->contract_duration,
            'contract_start_date' => $jobProfile->contract_start_date?->format('d/m/Y'),
            'contract_end_date' => $jobProfile->contract_end_date?->format('d/m/Y'),
            'work_location' => $jobProfile->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO',
            'selection_process_name' => $jobProfile->selection_process_name ?? 'PROCESO DE SELECCIÓN CAS',

            // Vacantes
            'total_vacancies' => $jobProfile->total_vacancies,

            // Requisitos generales (desde PositionCode)
            'requisitos_generales' => $jobProfile->getRequisitosGenerales(),
            'position_min_experience' => $jobProfile->positionCode?->min_professional_experience,
            'position_specific_experience' => $jobProfile->positionCode?->min_specific_experience,

            // Salario formateado
            'formatted_salary' => $jobProfile->formatted_salary,
            'base_salary' => $jobProfile->positionCode?->base_salary,

            // Aprobación
            'requested_by' => $jobProfile->requestedBy?->getFullNameAttribute(),
            'requested_at' => $jobProfile->requested_at?->format('d/m/Y'),
            'reviewed_by' => $jobProfile->reviewedBy?->getFullNameAttribute(),
            'reviewed_at' => $jobProfile->reviewed_at?->format('d/m/Y'),
            'approved_by' => $jobProfile->approvedBy?->getFullNameAttribute(),
            'approved_at' => $jobProfile->approved_at?->format('d/m/Y'),

            // Fechas
            'generation_date' => now()->format('d/m/Y'),
            'generation_time' => now()->format('H:i:s'),
            'current_year' => now()->year,

            // Datos estructurados para el Anexo 2
            'anexo2' => $jobProfile->anexo2_data,
            'published_profile' => $jobProfile->published_profile_data,
        ];
    }

    /**
     * Crea el flujo de firmas para el documento
     */
    protected function createSignatureWorkflow($document, $jobProfile): void
    {
        $signers = [];

        // Obtener firmantes desde la configuración del template
        $templateSigners = $document->template->signers_config ?? [];

        foreach ($templateSigners as $signer) {
            // Resolver el user_id dinámicamente según el rol
            $userId = $this->resolveSignerUserId($signer, $jobProfile);

            if ($userId) {
                $signers[] = [
                    'user_id' => $userId,
                    'type' => $signer['type'] ?? 'firma',
                    'role' => $signer['role'] ?? 'Firmante',
                ];

                Log::info('Firmante agregado al flujo', [
                    'document_id' => $document->id,
                    'user_id' => $userId,
                    'role' => $signer['role'] ?? 'Firmante',
                    'type' => $signer['type'] ?? 'firma',
                ]);
            } else {
                Log::warning('No se pudo resolver firmante', [
                    'document_id' => $document->id,
                    'role_key' => $signer['role_key'] ?? 'unknown',
                ]);
            }
        }

        if (empty($signers)) {
            Log::warning('No se encontraron firmantes para el documento', [
                'document_id' => $document->id,
                'job_profile_id' => $jobProfile->id,
                'template_code' => $document->template->code,
            ]);
            return;
        }

        // Crear el flujo de firmas
        $workflow = $this->signatureService->createWorkflow(
            $document,
            $signers,
            $document->template->signature_workflow_type ?? 'sequential'
        );

        Log::info('Flujo de firmas creado exitosamente', [
            'document_id' => $document->id,
            'workflow_id' => $workflow->id,
            'signers_count' => count($signers),
            'workflow_type' => $document->template->signature_workflow_type ?? 'sequential',
        ]);
    }

    /**
     * Resuelve el ID del usuario firmante según el rol
     */
    protected function resolveSignerUserId(array $signerConfig, $jobProfile): ?string
    {
        // Mapeo de roles a campos del job profile
        $roleMapping = [
            'requested_by' => $jobProfile->requested_by,
            'reviewed_by' => $jobProfile->reviewed_by,
            'approved_by' => $jobProfile->approved_by,
        ];

        $roleKey = $signerConfig['role_key'] ?? null;
        return $roleKey ? ($roleMapping[$roleKey] ?? null) : null;
    }
}
