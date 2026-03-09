<?php

declare(strict_types=1);

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Modules\User\Entities\User;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Services\DocumentService;
use Modules\JobPosting\Events\JobPostingPublicationRequested;

class RegenerateConvocatoriaDocument extends Command
{
    protected $signature = 'convocatoria:regenerate-document
                            {job-posting-id : ID de la convocatoria}
                            {--force : Forzar regeneración eliminando firmas existentes}';

    protected $description = 'Regenera el documento consolidado de una convocatoria';

    public function __construct(
        private readonly DocumentService $documentService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jobPostingId = $this->argument('job-posting-id');
        $force = $this->option('force');

        // Autenticar usuario para comandos de consola (generated_by requiere user_id)
        if (!Auth::check()) {
            $user = User::first();
            if ($user) {
                Auth::login($user);
            }
        }

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
            $this->warn("⚠️  No existe documento previo. Generando nuevo...");
            return $this->generateNew($jobPosting);
        }

        $this->info("📄 Documento encontrado: {$document->code}");

        // Verificar firmas
        if ($document->hasAnySignature()) {
            if (!$force) {
                $this->error("❌ El documento tiene firmas realizadas");
                $this->warn("   Use --force para regenerar eliminando las firmas");
                return Command::FAILURE;
            }

            if (!$this->confirm('⚠️  Esto eliminará TODAS las firmas existentes. ¿Continuar?')) {
                $this->info('Operación cancelada');
                return Command::SUCCESS;
            }

            $this->deleteSignatures($document);
        }

        // Regenerar documento
        try {
            $this->info('🔄 Regenerando documento...');

            $this->documentService->regeneratePDF($document);

            $this->info('✅ Documento regenerado exitosamente');
            $this->line("   Ruta: {$document->fresh()->pdf_path}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Genera un documento nuevo disparando el evento
     */
    private function generateNew(JobPosting $jobPosting): int
    {
        try {
            ob_start();
            event(new JobPostingPublicationRequested($jobPosting));
            ob_end_clean();

            $this->info('Documento generado exitosamente');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            ob_end_clean();
            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Elimina workflow y firmas
     */
    private function deleteSignatures(GeneratedDocument $document): void
    {
        $this->warn('🗑️  Eliminando firmas...');

        if ($document->signatureWorkflow) {
            $document->signatureWorkflow->signatures()->delete();
            $document->signatureWorkflow->delete();
        }

        $document->update([
            'signed_pdf_path' => null,
            'signature_status' => 'pending',
            'current_signer_id' => null,
            'signatures_completed' => 0,
            'total_signatures_required' => 0,
        ]);

        $this->info('   Firmas eliminadas');
    }
}
