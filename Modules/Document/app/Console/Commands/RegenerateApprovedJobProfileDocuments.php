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

            // Preparar datos (usando la misma lógica del listener)
            $data = $this->prepareDocumentData($jobProfile);

            // Si el documento tiene firmas y NO queremos mantenerlas, resetear flujo
            if ($existingDocument->hasAnySignature() && !$keepSignatures) {
                $this->resetSignatureWorkflow($existingDocument);
            }

            // Regenerar el contenido HTML
            $renderedHtml = $this->templateRenderer->render($template->content, $data);

            // Actualizar el documento
            $existingDocument->update([
                'title' => $data['title'] ?? $existingDocument->title,
                'content' => json_encode($data),
                'rendered_html' => $renderedHtml,
            ]);

            // Generar nuevo PDF (sin la validación de firmas)
            $pdfPath = $this->documentService->generatePDF($existingDocument, $renderedHtml, $template);
            $existingDocument->update(['pdf_path' => $pdfPath]);

            // Registrar auditoría
            DocumentAudit::log(
                $existingDocument->id,
                'updated',
                'system', // Usuario del sistema para operaciones masivas
                'Documento regenerado masivamente por comando artisan (corrigiendo error en contenido PDF)'
            );

            // Si reseteamos firmas, crear nuevo flujo
            if ($existingDocument->hasAnySignature() && !$keepSignatures) {
                $this->createSignatureWorkflow($existingDocument, $jobProfile, $template);
            }

            Log::info('Documento regenerado exitosamente', [
                'job_profile_id' => $jobProfile->id,
                'document_id' => $existingDocument->id,
                'document_code' => $existingDocument->code,
                'signatures_reset' => !$keepSignatures,
            ]);
        });
    }

    protected function resetSignatureWorkflow(GeneratedDocument $document): void
    {
        // Eliminar todas las firmas existentes
        $document->signatures()->delete();

        // Resetear campos relacionados con firmas
        $document->update([
            'signed_pdf_path' => null,
            'signature_status' => $document->signature_required ? 'pending' : null,
            'current_signer_id' => null,
            'signatures_completed' => 0,
        ]);

        Log::info('Flujo de firmas reseteado', [
            'document_id' => $document->id,
            'document_code' => $document->code,
        ]);
    }

    protected function createSignatureWorkflow(GeneratedDocument $document, JobProfile $jobProfile, DocumentTemplate $template): void
    {
        $signers = [];

        // Obtener firmantes desde la configuración del template
        $templateSigners = $template->signers_config ?? [];

        foreach ($templateSigners as $signer) {
            // Resolver el user_id dinámicamente según el rol
            $userId = $this->resolveSignerUserId($signer, $jobProfile);

            if ($userId) {
                $signers[] = [
                    'user_id' => $userId,
                    'type' => $signer['type'] ?? 'firma',
                    'role' => $signer['role'] ?? 'Firmante',
                ];
            }
        }

        if (empty($signers)) {
            Log::warning('No se encontraron firmantes para el documento', [
                'document_id' => $document->id,
                'job_profile_id' => $jobProfile->id,
            ]);
            return;
        }

        // Crear el flujo de firmas
        $workflow = $this->signatureService->createWorkflow(
            $document,
            $signers,
            $template->signature_workflow_type ?? 'sequential'
        );

        Log::info('Flujo de firmas recreado exitosamente', [
            'document_id' => $document->id,
            'workflow_id' => $workflow->id,
            'signers_count' => count($signers),
        ]);
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
        $this->info("📊 Perfiles procesados:  {$this->processedCount}");
        $this->info("✅ Exitosos:             {$this->successCount}");
        $this->info("⏭️  Omitidos:             {$this->skippedCount}");
        $this->error("❌ Errores:              {$this->errorCount}");
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
            $this->info('✨ Regeneración completada exitosamente');
        }
    }
}
