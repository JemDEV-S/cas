<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\JobProfile\Entities\JobProfile;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Entities\DocumentTemplate;
use Modules\Document\Entities\DocumentAudit;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Services\TemplateRendererService;

class RegenerateApprovedJobProfileDocuments extends Command
{
    protected $signature = 'documents:regenerate-approved-job-profiles
                            {--dry-run : Ejecutar en modo simulación sin realizar cambios}
                            {--keep-signatures : Mantener firmas existentes (NO recomendado)}
                            {--profile-id= : Regenerar solo un perfil específico por ID}';

    protected $description = 'Regenera documentos de todos los perfiles aprobados, reseteando el flujo de firmas';

    protected int $processedCount = 0;
    protected int $successCount = 0;
    protected int $errorCount = 0;
    protected int $skippedCount = 0;
    protected int $workflowsResetCount = 0;
    protected int $workflowsCreatedCount = 0;
    protected array $errors = [];

    public function __construct(
        protected DocumentService $documentService,
        protected SignatureService $signatureService,
        protected TemplateRendererService $templateRenderer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Iniciando regeneración de documentos de perfiles aprobados...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $keepSignatures = $this->option('keep-signatures');
        $specificProfileId = $this->option('profile-id');

        if ($dryRun) {
            $this->warn('⚠️  MODO SIMULACIÓN - No se realizarán cambios reales');
            $this->newLine();
        }

        if ($keepSignatures) {
            $this->warn('⚠️  ADVERTENCIA: Las firmas existentes se mantendrán pero perderán validez técnica');
            if (!$this->confirm('¿Estás seguro de continuar manteniendo las firmas?', false)) {
                $this->error('❌ Operación cancelada por el usuario');
                return 1;
            }
            $this->newLine();
        }

        // Obtener perfiles aprobados
        $query = JobProfile::query()
            ->where('status', 'approved')
            ->with([
                'organizationalUnit',
                'requestingUnit.parent',
                'positionCode',
                'requestedBy',
                'reviewedBy',
                'approvedBy'
            ]);

        if ($specificProfileId) {
            $query->where('id', $specificProfileId);
        }

        $jobProfiles = $query->get();

        if ($jobProfiles->isEmpty()) {
            $this->warn('⚠️  No se encontraron perfiles aprobados para procesar');
            return 0;
        }

        $this->info("📋 Se encontraron {$jobProfiles->count()} perfiles aprobados");
        $this->newLine();

        if (!$dryRun && !$this->confirm("¿Deseas continuar con la regeneración de {$jobProfiles->count()} documentos?", true)) {
            $this->error('❌ Operación cancelada por el usuario');
            return 1;
        }

        $this->newLine();
        $progressBar = $this->output->createProgressBar($jobProfiles->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        foreach ($jobProfiles as $jobProfile) {
            $this->processedCount++;
            $progressBar->setMessage("Procesando: {$jobProfile->code}");

            try {
                $this->processJobProfile($jobProfile, $dryRun, $keepSignatures);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'profile_code' => $jobProfile->code,
                    'profile_id' => $jobProfile->id,
                    'error' => $e->getMessage(),
                ];

                Log::error('Error al regenerar documento de perfil', [
                    'job_profile_id' => $jobProfile->id,
                    'job_profile_code' => $jobProfile->code,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->displaySummary($dryRun);

        return $this->errorCount > 0 ? 1 : 0;
    }

    protected function processJobProfile(JobProfile $jobProfile, bool $dryRun, bool $keepSignatures): void
    {
        // Buscar documento existente
        $existingDocument = GeneratedDocument::where('documentable_type', 'Modules\JobProfile\Entities\JobProfile')
            ->where('documentable_id', $jobProfile->id)
            ->whereHas('template', function ($query) {
                $query->where('code', 'TPL_JOB_PROFILE');
            })
            ->first();

        if (!$existingDocument) {
            $this->skippedCount++;
            Log::info('Perfil aprobado sin documento generado', [
                'job_profile_id' => $jobProfile->id,
                'job_profile_code' => $jobProfile->code,
            ]);
            return;
        }

        if ($dryRun) {
            // Modo simulación - solo logging
            $hasSignatures = $existingDocument->hasAnySignature();
            Log::info('[DRY-RUN] Se regeneraría documento', [
                'job_profile_id' => $jobProfile->id,
                'document_id' => $existingDocument->id,
                'has_signatures' => $hasSignatures,
                'would_reset_signatures' => !$keepSignatures && $hasSignatures,
            ]);
            return;
        }

        DB::transaction(function () use ($jobProfile, $existingDocument, $keepSignatures) {
            // Obtener template
            $template = DocumentTemplate::where('code', 'TPL_JOB_PROFILE')
                ->where('status', 'active')
                ->firstOrFail();

            // Verificar si tiene firmas ANTES de resetear
            $hadSignatures = $existingDocument->hasAnySignature();
            $requiresSignature = $template->requiresSignature();

            // PASO 1: SIEMPRE resetear el flujo de firmas si NO queremos mantenerlas
            // Esto es necesario incluso si no tenía firmas, para limpiar cualquier workflow pendiente
            if (!$keepSignatures) {
                $this->resetSignatureWorkflow($existingDocument);
                if ($hadSignatures) {
                    $this->workflowsResetCount++;
                }
            }

            // PASO 2: Preparar datos con la información actualizada del perfil
            $data = $this->prepareDocumentData($jobProfile);

            // PASO 3: Regenerar el contenido HTML con los datos corregidos
            $renderedHtml = $this->templateRenderer->render($template->content, $data);

            // PASO 4: Actualizar el documento con el nuevo contenido
            // Establecer el estado correcto según el flujo original de generación
            $existingDocument->update([
                'title' => $data['title'] ?? $existingDocument->title,
                'content' => json_encode($data),
                'rendered_html' => $renderedHtml,
                // Resetear a 'draft' como cuando se genera por primera vez
                'status' => 'draft',
                'signature_status' => $requiresSignature ? 'pending' : null,
                'signature_required' => $requiresSignature,
            ]);

            // PASO 5: Generar nuevo PDF con el contenido corregido
            $pdfPath = $this->documentService->generatePDF($existingDocument, $renderedHtml, $template);
            $existingDocument->update(['pdf_path' => $pdfPath]);

            // PASO 6: Registrar auditoría de la regeneración
            DocumentAudit::log(
                $existingDocument->id,
                'updated',
                'system',
                'Documento regenerado masivamente por comando artisan (corrigiendo error en contenido PDF)'
            );

            // PASO 7: Crear el workflow de firmas si el template lo requiere
            // Esto replica el comportamiento del listener GenerateJobProfileDocument
            if ($requiresSignature && !$keepSignatures) {
                $this->createSignatureWorkflow($existingDocument, $jobProfile, $template);
                $this->workflowsCreatedCount++;
            }

            Log::info('Documento regenerado exitosamente', [
                'job_profile_id' => $jobProfile->id,
                'document_id' => $existingDocument->id,
                'document_code' => $existingDocument->code,
                'had_signatures' => $hadSignatures,
                'requires_signature' => $requiresSignature,
                'workflow_reset' => !$keepSignatures,
                'workflow_created' => $requiresSignature && !$keepSignatures,
                'final_status' => $existingDocument->fresh()->status,
                'final_signature_status' => $existingDocument->fresh()->signature_status,
            ]);
        });
    }

    /**
     * Resetea completamente el flujo de firmas de un documento
     * Elimina: SignatureWorkflow, DigitalSignatures y resetea campos del documento
     */
    protected function resetSignatureWorkflow(GeneratedDocument $document): void
    {
        $deletedSignatures = 0;
        $deletedWorkflows = 0;

        // 1. Eliminar workflow de firmas existente
        $workflow = $document->signatureWorkflow()->first();
        if ($workflow) {
            $workflow->delete();
            $deletedWorkflows++;
            Log::info('SignatureWorkflow eliminado', [
                'document_id' => $document->id,
                'workflow_id' => $workflow->id,
                'workflow_status' => $workflow->status,
            ]);
        }

        // 2. Eliminar todas las firmas digitales existentes
        $signatures = $document->signatures()->get();
        foreach ($signatures as $signature) {
            Log::info('DigitalSignature eliminada', [
                'document_id' => $document->id,
                'signature_id' => $signature->id,
                'user_id' => $signature->user_id,
                'status' => $signature->status,
                'signed_at' => $signature->signed_at,
            ]);
            $signature->delete();
            $deletedSignatures++;
        }

        // 3. Resetear campos relacionados con firmas en el documento
        // NOTA: NO cambiamos el status a 'draft', lo dejamos en su estado actual
        // El SignatureService.createWorkflow() lo actualizará a 'pending_signature' correctamente
        $document->update([
            'signed_pdf_path' => null,
            'signature_status' => null, // Resetear a null, el workflow lo actualizará
            'current_signer_id' => null,
            'signatures_completed' => 0,
            'total_signatures_required' => 0,
        ]);

        // 4. Registrar auditoría
        DocumentAudit::log(
            $document->id,
            'workflow_reset',
            'system',
            "Flujo de firmas reseteado masivamente: {$deletedWorkflows} workflow(s), {$deletedSignatures} firma(s) eliminadas"
        );

        Log::info('Flujo de firmas reseteado completamente', [
            'document_id' => $document->id,
            'document_code' => $document->code,
            'workflows_deleted' => $deletedWorkflows,
            'signatures_deleted' => $deletedSignatures,
        ]);
    }

    /**
     * Crea un nuevo workflow de firmas para el documento
     * Utiliza SignatureService para mantener consistencia con el flujo normal
     */
    protected function createSignatureWorkflow(GeneratedDocument $document, JobProfile $jobProfile, DocumentTemplate $template): void
    {
        $signers = [];

        // Obtener firmantes desde la configuración del template
        $templateSigners = $template->signers_config ?? [];

        Log::info('Preparando firmantes para nuevo workflow', [
            'document_id' => $document->id,
            'template_signers_count' => count($templateSigners),
        ]);

        foreach ($templateSigners as $index => $signer) {
            // Resolver el user_id dinámicamente según el rol
            $userId = $this->resolveSignerUserId($signer, $jobProfile);

            if ($userId) {
                $signers[] = [
                    'user_id' => $userId,
                    'type' => $signer['type'] ?? 'firma',
                    'role' => $signer['role'] ?? 'Firmante',
                ];

                Log::info('Firmante agregado al nuevo workflow', [
                    'document_id' => $document->id,
                    'order' => $index + 1,
                    'user_id' => $userId,
                    'role' => $signer['role'] ?? 'Firmante',
                    'type' => $signer['type'] ?? 'firma',
                ]);
            } else {
                Log::warning('No se pudo resolver firmante', [
                    'document_id' => $document->id,
                    'role_key' => $signer['role_key'] ?? 'unknown',
                    'signer_config' => $signer,
                ]);
            }
        }

        if (empty($signers)) {
            Log::error('No se encontraron firmantes válidos para el documento', [
                'document_id' => $document->id,
                'document_code' => $document->code,
                'job_profile_id' => $jobProfile->id,
                'job_profile_code' => $jobProfile->code,
                'template_code' => $template->code,
            ]);
            throw new \Exception("No se pudieron resolver los firmantes para el documento {$document->code}");
        }

        // Crear el flujo de firmas usando el servicio oficial
        $workflowType = $template->signature_workflow_type ?? 'sequential';

        Log::info('Creando nuevo SignatureWorkflow', [
            'document_id' => $document->id,
            'workflow_type' => $workflowType,
            'signers_count' => count($signers),
        ]);

        $workflow = $this->signatureService->createWorkflow(
            $document,
            $signers,
            $workflowType
        );

        // Verificar que se crearon las firmas digitales
        $createdSignatures = $document->signatures()->count();

        Log::info('Flujo de firmas recreado exitosamente', [
            'document_id' => $document->id,
            'document_code' => $document->code,
            'workflow_id' => $workflow->id,
            'workflow_type' => $workflowType,
            'workflow_status' => $workflow->status,
            'signers_count' => count($signers),
            'digital_signatures_created' => $createdSignatures,
            'current_signer_id' => $document->current_signer_id,
        ]);

        // Registrar auditoría adicional
        DocumentAudit::log(
            $document->id,
            'workflow_recreated',
            'system',
            "Nuevo flujo de firmas creado: {$workflowType}, {$createdSignatures} firmantes configurados"
        );
    }

    protected function resolveSignerUserId(array $signerConfig, JobProfile $jobProfile): ?string
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

    protected function prepareDocumentData(JobProfile $jobProfile): array
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

    protected function displaySummary(bool $dryRun): void
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('                    RESUMEN DE EJECUCIÓN                ');
        $this->info('═══════════════════════════════════════════════════════');

        if ($dryRun) {
            $this->warn('Modo: SIMULACIÓN (no se realizaron cambios)');
        } else {
            $this->info('Modo: PRODUCCIÓN (cambios aplicados)');
        }

        $this->newLine();
        $this->info('📄 DOCUMENTOS:');
        $this->info("   • Procesados:         {$this->processedCount}");
        $this->info("   • Exitosos:           {$this->successCount}");
        $this->info("   • Omitidos:           {$this->skippedCount}");
        if ($this->errorCount > 0) {
            $this->error("   • Errores:            {$this->errorCount}");
        } else {
            $this->info("   • Errores:            {$this->errorCount}");
        }

        $this->newLine();
        $this->info('🔄 FLUJOS DE FIRMA:');
        $this->info("   • Workflows resetea.. {$this->workflowsResetCount}");
        $this->info("   • Workflows creados:  {$this->workflowsCreatedCount}");

        $this->info('═══════════════════════════════════════════════════════');

        if (!empty($this->errors)) {
            $this->newLine();
            $this->error('❌ ERRORES ENCONTRADOS:');
            foreach ($this->errors as $error) {
                $this->error("  • {$error['profile_code']} (ID: {$error['profile_id']}): {$error['error']}");
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn('💡 Para ejecutar los cambios reales, ejecuta el comando sin --dry-run');
        } else {
            if ($this->errorCount === 0) {
                $this->info('✨ Regeneración completada exitosamente');
                if ($this->workflowsCreatedCount > 0) {
                    $this->newLine();
                    $this->warn("⚠️  IMPORTANTE: Se crearon {$this->workflowsCreatedCount} nuevos workflows de firma.");
                    $this->warn('   Los firmantes recibirán notificaciones para firmar los documentos.');
                }
            } else {
                $this->error('⚠️  Regeneración completada con errores. Revisa el log para más detalles.');
            }
        }
    }
}
