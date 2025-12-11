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
            // Cargar relaciones necesarias
            $jobProfile = $event->jobProfile->load([
                'organizationalUnit',
                'requestingUnit.parent',
                'positionCode',
                'requestedBy',
                'reviewedBy',
                'approvedBy'
            ]);

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
     * IMPORTANTE: Todos los datos de texto se convierten a MAYÚSCULAS para el documento oficial
     */
    protected function prepareDocumentData($jobProfile): array
    {
        return [
            'title' => mb_strtoupper('Perfil de Puesto - ' . $jobProfile->profile_name),
            'code' => $jobProfile->code,
            'job_profile' => $jobProfile,

            // Datos básicos (en MAYÚSCULAS)
            'profile_title' => mb_strtoupper($jobProfile->title ?? ''),
            'profile_name' => mb_strtoupper($jobProfile->profile_name ?? ''),
            'position_code' => $jobProfile->positionCode?->code,
            'position_name' => mb_strtoupper($jobProfile->positionCode?->name ?? ''),

            // Unidad organizacional (en MAYÚSCULAS)
            'organizational_unit' => mb_strtoupper($jobProfile->organizationalUnit?->name ?? ''),
            'parent_organizational_unit' => mb_strtoupper($jobProfile->requestingUnit?->parent?->name ?? ''),
            'requesting_unit' => mb_strtoupper($jobProfile->requestingUnit?->name ?? ''),
            'required_position' => mb_strtoupper($jobProfile->positionCode?->name ?? ''),

            // Datos del puesto (en MAYÚSCULAS)
            'job_level' => mb_strtoupper($jobProfile->job_level ?? ''),
            'contract_type' => mb_strtoupper($jobProfile->contract_type ?? ''),
            'salary_range' => $jobProfile->salary_range,
            'salary_min' => $jobProfile->salary_min,
            'salary_max' => $jobProfile->salary_max,
            'description' => mb_strtoupper($jobProfile->description ?? ''),
            'mission' => mb_strtoupper($jobProfile->mission ?? ''),
            'working_conditions' => mb_strtoupper($jobProfile->working_conditions ?? ''),

            // Requisitos académicos (en MAYÚSCULAS)
            'education_level' => mb_strtoupper($jobProfile->education_level_label ?? ''),
            'career_field' => mb_strtoupper($jobProfile->career_field ?? ''),
            'title_required' => mb_strtoupper($jobProfile->title_required ?? ''),
            'colegiatura_required' => $jobProfile->colegiatura_required ? 'SÍ' : 'NO',

            // Experiencia (en MAYÚSCULAS)
            'general_experience_years' => mb_strtoupper($jobProfile->general_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
            'specific_experience_years' => mb_strtoupper($jobProfile->specific_experience_years?->toHuman() ?? 'SIN EXPERIENCIA'),
            'specific_experience_description' => mb_strtoupper($jobProfile->specific_experience_description ?? ''),
            'total_experience_years' => $jobProfile->total_experience_years,

            // Capacitación y conocimientos (arrays convertidos a MAYÚSCULAS)
            'required_courses' => $this->convertArrayToUpperCase($jobProfile->required_courses ?? []),
            'knowledge_areas' => $this->convertArrayToUpperCase($jobProfile->knowledge_areas ?? []),
            'required_competencies' => $this->convertArrayToUpperCase($jobProfile->required_competencies ?? []),

            // Funciones (array convertido a MAYÚSCULAS)
            'main_functions' => $this->convertArrayToUpperCase($jobProfile->main_functions ?? []),

            // Régimen laboral (en MAYÚSCULAS)
            'work_regime' => mb_strtoupper($jobProfile->work_regime_label ?? ''),
            'justification' => mb_strtoupper($jobProfile->justification_text ?? ''),

            // Contrato
            'contract_duration' => mb_strtoupper($jobProfile->contract_duration ?? '3 MESES'),
            'contract_start_date' => $jobProfile->contract_start_date?->format('d/m/Y'),
            'contract_end_date' => $jobProfile->contract_end_date?->format('d/m/Y'),
            'work_location' => mb_strtoupper($jobProfile->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO'),
            'selection_process_name' => mb_strtoupper($jobProfile->selection_process_name ?? 'PROCESO DE SELECCIÓN CAS'),

            // Vacantes
            'total_vacancies' => $jobProfile->total_vacancies,

            // Requisitos generales (desde PositionCode) (en MAYÚSCULAS)
            'requisitos_generales' => mb_strtoupper($jobProfile->getRequisitosGenerales() ?? ''),
            'position_min_experience' => $jobProfile->positionCode?->min_professional_experience,
            'position_specific_experience' => $jobProfile->positionCode?->min_specific_experience,

            // Salario formateado
            'formatted_salary' => $jobProfile->formatted_salary,
            'base_salary' => $jobProfile->positionCode?->base_salary,

            // Aprobación (nombres en MAYÚSCULAS)
            'requested_by' => mb_strtoupper($jobProfile->requestedBy?->getFullNameAttribute() ?? ''),
            'requested_at' => $jobProfile->requested_at?->format('d/m/Y'),
            'reviewed_by' => mb_strtoupper($jobProfile->reviewedBy?->getFullNameAttribute() ?? ''),
            'reviewed_at' => $jobProfile->reviewed_at?->format('d/m/Y'),
            'approved_by' => mb_strtoupper($jobProfile->approvedBy?->getFullNameAttribute() ?? ''),
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
     * Convierte todos los elementos de un array a MAYÚSCULAS
     * Maneja tanto arrays simples como arrays asociativos
     */
    protected function convertArrayToUpperCase(array $data): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return mb_strtoupper($item);
            }
            if (is_array($item)) {
                return $this->convertArrayToUpperCase($item);
            }
            return $item;
        }, $data);
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
