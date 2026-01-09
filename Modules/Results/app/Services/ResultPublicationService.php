<?php

namespace Modules\Results\Services;

use Modules\Results\Entities\ResultPublication;
use Modules\Results\Enums\PublicationStatusEnum;
use Modules\Results\Enums\PublicationPhaseEnum;
use Modules\Results\Jobs\GenerateResultExcelJob;
use Modules\Results\Jobs\SendResultNotificationsJob;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Entities\DocumentTemplate;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultPublicationService
{
    public function __construct(
        private DocumentService $documentService,
        private SignatureService $signatureService,
        private ResultExportService $exportService
    ) {}

    /**
     * Publicar resultados de Fase 4 (Elegibilidad)
     *
     * Flujo:
     * 1. Validar que no exista publicación activa
     * 2. Obtener datos de postulaciones
     * 3. Generar PDF desde template
     * 4. Crear ResultPublication en estado PENDING_SIGNATURE
     * 5. Iniciar flujo de firmas
     * 6. Cuando DocumentFullySigned → Cambiar a PUBLISHED
     */
    public function publishPhase4Results(
        JobPosting $posting,
        array $jurySigners,
        string $signatureMode = 'sequential',
        bool $sendNotifications = true
    ): ResultPublication {

        return DB::transaction(function () use ($posting, $jurySigners, $signatureMode, $sendNotifications) {

            // 1. Validar que no exista publicación activa
            $this->validateNoActivePublication($posting, PublicationPhaseEnum::PHASE_04);

            // 2. Obtener datos de postulaciones
            $applications = Application::whereHas('vacancy.jobProfile.jobPosting',
                    fn($q) => $q->where('id', $posting->id)
                )
                ->whereNotNull('eligibility_checked_at')
                ->with(['vacancy.jobProfile', 'applicant'])
                ->orderBy('full_name')
                ->get();

            if ($applications->isEmpty()) {
                throw new \Exception('No hay postulaciones evaluadas para publicar');
            }

            $stats = [
                'total' => $applications->count(),
                'eligible' => $applications->where('is_eligible', true)->count(),
                'not_eligible' => $applications->where('is_eligible', false)->count(),
            ];

            // 3. Preparar datos para template
            $templateData = $this->preparePhase4TemplateData($posting, $applications, $stats);

            // 4. Obtener template
            $template = DocumentTemplate::where('code', 'RESULT_ELIGIBILITY')->firstOrFail();

            // 5. Crear ResultPublication (estado inicial: draft)
            $publication = ResultPublication::create([
                'job_posting_id' => $posting->id,
                'phase' => PublicationPhaseEnum::PHASE_04,
                'status' => PublicationStatusEnum::DRAFT,
                'title' => "Resultados de Evaluación de Requisitos Mínimos - {$posting->code}",
                'description' => "Resultados de la evaluación de elegibilidad (APTO/NO APTO)",
                'total_applicants' => $stats['total'],
                'total_eligible' => $stats['eligible'],
                'total_not_eligible' => $stats['not_eligible'],
                'metadata' => [
                    'template_code' => $template->code,
                    'signature_mode' => $signatureMode,
                    'generated_at' => now()->toIso8601String(),
                    'statistics' => $stats,
                ],
            ]);

            // 6. Generar documento PDF (sin firmar aún)
            $document = $this->documentService->generateFromTemplate(
                $template,
                $publication,  // documentable = ResultPublication
                $templateData,
                auth()->id()
            );

            // 7. Vincular documento a publicación
            $publication->update([
                'generated_document_id' => $document->id,
                'status' => PublicationStatusEnum::PENDING_SIGNATURE,
            ]);

            // 8. Iniciar flujo de firmas
            $this->signatureService->createWorkflow(
                $document,
                $jurySigners,  // [['user_id' => 'xxx', 'role' => 'Jurado Titular'], ...]
                $signatureMode  // 'sequential' o 'parallel'
            );

            // 9. Generar Excel en background
            GenerateResultExcelJob::dispatch($publication, $applications->toArray(), 'PHASE_04');

            // 10. Log
            Log::info('Publicación de resultados FASE 4 iniciada', [
                'publication_id' => $publication->id,
                'posting_code' => $posting->code,
                'total_signers' => count($jurySigners),
                'signature_mode' => $signatureMode,
            ]);

            // Nota: Las notificaciones se envían DESPUÉS de que el documento esté firmado
            // Ver listener: OnDocumentFullySigned

            return $publication->fresh(['document', 'jobPosting']);
        });
    }

    /**
     * Publicar resultados de Fase 7 (Evaluación Curricular)
     */
    public function publishPhase7Results(
        JobPosting $posting,
        array $jurySigners,
        string $signatureMode = 'sequential'
    ): ResultPublication {

        return DB::transaction(function () use ($posting, $jurySigners, $signatureMode) {

            // 1. Validar que no exista publicación activa
            $this->validateNoActivePublication($posting, PublicationPhaseEnum::PHASE_07);

            // 2. Obtener evaluaciones con puntajes
            $applications = Application::whereHas('vacancy.jobProfile.jobPosting',
                    fn($q) => $q->where('id', $posting->id)
                )
                ->where('is_eligible', true)
                ->whereNotNull('curriculum_score')
                ->with(['vacancy.jobProfile', 'applicant'])
                ->get();

            if ($applications->isEmpty()) {
                throw new \Exception('No hay evaluaciones curriculares para publicar');
            }

            // Calcular ranking por puntaje curricular
            $rankedApplications = $applications->sortByDesc('curriculum_score')
                ->values()
                ->map(function($app, $index) {
                    $app->ranking = $index + 1;
                    return $app;
                });

            // 3. Preparar datos para template
            $templateData = $this->preparePhase7TemplateData($posting, $rankedApplications);

            // 4. Template diferente para Fase 7
            $template = DocumentTemplate::where('code', 'RESULT_CURRICULUM')->firstOrFail();

            // 5. Crear publicación
            $publication = ResultPublication::create([
                'job_posting_id' => $posting->id,
                'phase' => PublicationPhaseEnum::PHASE_07,
                'status' => PublicationStatusEnum::DRAFT,
                'title' => "Resultados de Evaluación Curricular - {$posting->code}",
                'description' => "Ranking de evaluación curricular con puntajes",
                'total_applicants' => $applications->count(),
                'metadata' => [
                    'template_code' => $template->code,
                    'signature_mode' => $signatureMode,
                    'top_score' => $rankedApplications->first()?->curriculum_score,
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);

            // 6. Generar documento
            $document = $this->documentService->generateFromTemplate(
                $template,
                $publication,
                $templateData,
                auth()->id()
            );

            // 7. Vincular y cambiar estado
            $publication->update([
                'generated_document_id' => $document->id,
                'status' => PublicationStatusEnum::PENDING_SIGNATURE,
            ]);

            // 8. Iniciar flujo de firmas
            $this->signatureService->createWorkflow(
                $document,
                $jurySigners,
                $signatureMode
            );

            // 9. Excel
            GenerateResultExcelJob::dispatch($publication, $rankedApplications->toArray(), 'PHASE_07');

            Log::info('Publicación de resultados FASE 7 iniciada', [
                'publication_id' => $publication->id,
                'posting_code' => $posting->code,
            ]);

            return $publication->fresh(['document', 'jobPosting']);
        });
    }

    /**
     * Publicar resultados de Fase 9 (Resultados Finales post-entrevista)
     */
    public function publishPhase9Results(
        JobPosting $posting,
        array $jurySigners,
        string $signatureMode = 'sequential'
    ): ResultPublication {

        return DB::transaction(function () use ($posting, $jurySigners, $signatureMode) {

            // 1. Validar que no exista publicación activa
            $this->validateNoActivePublication($posting, PublicationPhaseEnum::PHASE_09);

            // 2. Obtener resultados finales (con puntaje de entrevista)
            $applications = Application::whereHas('vacancy.jobProfile.jobPosting',
                    fn($q) => $q->where('id', $posting->id)
                )
                ->where('is_eligible', true)
                ->whereNotNull('final_score')
                ->with(['vacancy.jobProfile', 'applicant'])
                ->get();

            if ($applications->isEmpty()) {
                throw new \Exception('No hay resultados finales para publicar');
            }

            // Calcular ranking final
            $rankedApplications = $applications->sortByDesc('final_score')
                ->values()
                ->map(function($app, $index) {
                    $app->final_ranking = $index + 1;
                    $app->save();
                    return $app;
                });

            // 3. Preparar datos para template
            $templateData = $this->preparePhase9TemplateData($posting, $rankedApplications);

            // 4. Template para resultados finales
            $template = DocumentTemplate::where('code', 'RESULT_FINAL')->firstOrFail();

            // 5. Crear publicación
            $publication = ResultPublication::create([
                'job_posting_id' => $posting->id,
                'phase' => PublicationPhaseEnum::PHASE_09,
                'status' => PublicationStatusEnum::DRAFT,
                'title' => "Resultados Finales - {$posting->code}",
                'description' => "Ranking final post-entrevista personal",
                'total_applicants' => $applications->count(),
                'metadata' => [
                    'template_code' => $template->code,
                    'signature_mode' => $signatureMode,
                    'top_score' => $rankedApplications->first()?->final_score,
                    'generated_at' => now()->toIso8601String(),
                ],
            ]);

            // 6. Generar documento
            $document = $this->documentService->generateFromTemplate(
                $template,
                $publication,
                $templateData,
                auth()->id()
            );

            // 7. Vincular y cambiar estado
            $publication->update([
                'generated_document_id' => $document->id,
                'status' => PublicationStatusEnum::PENDING_SIGNATURE,
            ]);

            // 8. Iniciar flujo de firmas
            $this->signatureService->createWorkflow(
                $document,
                $jurySigners,
                $signatureMode
            );

            // 9. Excel
            GenerateResultExcelJob::dispatch($publication, $rankedApplications->toArray(), 'PHASE_09');

            Log::info('Publicación de resultados FASE 9 iniciada', [
                'publication_id' => $publication->id,
                'posting_code' => $posting->code,
            ]);

            return $publication->fresh(['document', 'jobPosting']);
        });
    }

    /**
     * Despublicar resultados (ocultar)
     * Solo si no hay firmas completadas
     */
    public function unpublishResults(ResultPublication $publication): void
    {
        if ($publication->document?->hasAnySignature()) {
            throw new \Exception('No se puede despublicar un documento que ya tiene firmas');
        }

        DB::transaction(function () use ($publication) {
            $publication->update([
                'status' => PublicationStatusEnum::UNPUBLISHED,
                'unpublished_at' => now(),
                'unpublished_by' => auth()->id(),
            ]);

            // Cancelar workflow de firmas si existe
            if ($publication->document) {
                $this->signatureService->cancelWorkflow(
                    $publication->document,
                    'Publicación cancelada por administrador'
                );
            }

            Log::info('Publicación despublicada', [
                'publication_id' => $publication->id,
                'phase' => $publication->phase->value,
            ]);
        });
    }

    /**
     * Re-publicar resultados
     */
    public function republishResults(ResultPublication $publication): void
    {
        if (!$publication->document?->isSigned()) {
            throw new \Exception('El documento debe estar firmado para publicar');
        }

        $publication->update([
            'status' => PublicationStatusEnum::PUBLISHED,
            'published_at' => now(),
            'published_by' => auth()->id(),
        ]);

        Log::info('Publicación republicada', [
            'publication_id' => $publication->id,
        ]);
    }

    /**
     * Validar que no exista publicación activa para la fase
     */
    private function validateNoActivePublication(JobPosting $posting, PublicationPhaseEnum $phase): void
    {
        $existing = ResultPublication::where('job_posting_id', $posting->id)
            ->where('phase', $phase)
            ->whereIn('status', [
                PublicationStatusEnum::PUBLISHED,
                PublicationStatusEnum::PENDING_SIGNATURE
            ])
            ->first();

        if ($existing) {
            throw new \Exception("Ya existe una publicación activa para {$phase->label()}");
        }
    }

    /**
     * Preparar datos para template Fase 4
     */
    private function preparePhase4TemplateData(JobPosting $posting, $applications, array $stats): array
    {
        return [
            'posting' => $posting,
            'title' => PublicationPhaseEnum::PHASE_04->documentTitle(),
            'subtitle' => $posting->code,
            'applications' => $applications,
            'aptos' => $applications->where('is_eligible', true),
            'no_aptos' => $applications->where('is_eligible', false),
            'stats' => $stats,
            'date' => now()->format('d/m/Y'),
            'phase' => 'FASE 4 - EVALUACIÓN DE REQUISITOS MÍNIMOS',
        ];
    }

    /**
     * Preparar datos para template Fase 7
     */
    private function preparePhase7TemplateData(JobPosting $posting, $rankedApplications): array
    {
        return [
            'posting' => $posting,
            'title' => PublicationPhaseEnum::PHASE_07->documentTitle(),
            'subtitle' => $posting->code,
            'applications' => $rankedApplications,
            'date' => now()->format('d/m/Y'),
            'phase' => 'FASE 7 - EVALUACIÓN CURRICULAR',
        ];
    }

    /**
     * Preparar datos para template Fase 9
     */
    private function preparePhase9TemplateData(JobPosting $posting, $rankedApplications): array
    {
        return [
            'posting' => $posting,
            'title' => PublicationPhaseEnum::PHASE_09->documentTitle(),
            'subtitle' => $posting->code,
            'applications' => $rankedApplications,
            'date' => now()->format('d/m/Y'),
            'phase' => 'FASE 9 - RESULTADOS FINALES',
        ];
    }
}
