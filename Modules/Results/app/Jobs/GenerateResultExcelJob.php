<?php

namespace Modules\Results\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Results\Entities\ResultPublication;
use Modules\Results\Services\ResultExportService;
use Illuminate\Support\Facades\Log;

class GenerateResultExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ResultPublication $publication,
        public array $applications,
        public string $phase
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ResultExportService $exportService): void
    {
        Log::info('Iniciando generación de Excel de resultados', [
            'publication_id' => $this->publication->id,
            'phase' => $this->phase,
            'applications_count' => count($this->applications),
        ]);

        try {
            // Generar Excel
            $export = $exportService->exportToExcel(
                $this->publication,
                $this->applications,
                $this->phase
            );

            Log::info('Excel de resultados generado exitosamente', [
                'publication_id' => $this->publication->id,
                'export_id' => $export->id,
                'file_path' => $export->file_path,
                'file_size' => $export->getFormattedSize(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error generando Excel de resultados', [
                'publication_id' => $this->publication->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de generación de Excel falló definitivamente', [
            'publication_id' => $this->publication->id,
            'phase' => $this->phase,
            'error' => $exception->getMessage(),
        ]);
    }
}
