<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;

class VerifyPublicSectorExperiences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cas:verify-public-experiences {--posting= : ID de la convocatoria} {--dry-run : Simular sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar automáticamente las experiencias de sector público para postulaciones aptas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postingId = $this->option('posting');
        $isDryRun = $this->option('dry-run');

        $this->info('=== VERIFICACIÓN DE EXPERIENCIAS SECTOR PÚBLICO ===');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('MODO DRY-RUN: No se guardarán cambios');
            $this->newLine();
        }

        // Query base para postulaciones aptas
        $query = Application::where('status', ApplicationStatus::ELIGIBLE)
            ->where('is_eligible', true)
            ->with('experiences');

        // Filtrar por convocatoria si se especifica
        if ($postingId) {
            $query->whereHas('jobProfile', fn($q) => $q->where('job_posting_id', $postingId));
            $this->info("Filtrando por convocatoria ID: {$postingId}");
        }

        $applications = $query->get();

        $this->info("Postulaciones aptas encontradas: {$applications->count()}");
        $this->newLine();

        $stats = [
            'postulaciones_procesadas' => 0,
            'experiencias_verificadas' => 0,
            'experiencias_ya_verificadas' => 0,
        ];

        $progressBar = $this->output->createProgressBar($applications->count());
        $progressBar->start();

        foreach ($applications as $app) {
            $publicExperiences = $app->experiences()
                ->where('is_public_sector', true)
                ->get();

            if ($publicExperiences->isEmpty()) {
                $progressBar->advance();
                continue;
            }

            foreach ($publicExperiences as $experience) {
                if ($experience->is_verified) {
                    $stats['experiencias_ya_verificadas']++;
                } else {
                    if (!$isDryRun) {
                        $experience->is_verified = true;
                        $experience->verification_notes = 'Verificado automáticamente - Postulación apta';
                        $experience->save();
                    }
                    $stats['experiencias_verificadas']++;
                }
            }

            $stats['postulaciones_procesadas']++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('=== RESUMEN ===');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Postulaciones procesadas', $stats['postulaciones_procesadas']],
                ['Experiencias verificadas', $stats['experiencias_verificadas']],
                ['Experiencias ya verificadas', $stats['experiencias_ya_verificadas']],
                ['Total experiencias sector público', $stats['experiencias_verificadas'] + $stats['experiencias_ya_verificadas']],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('Ejecuta el comando sin --dry-run para aplicar los cambios');
        } else {
            $this->newLine();
            $this->info('✓ Experiencias verificadas exitosamente');
        }

        return Command::SUCCESS;
    }
}
