<?php

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\Document\Services\EligibilityReportService;
use Modules\JobPosting\Entities\JobPosting;

class GenerateEligibilityReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'report:eligibility
                            {posting_id : ID o codigo de la convocatoria}
                            {--pdf : Generar reporte en PDF}
                            {--excel : Generar reporte en Excel}
                            {--all : Generar PDF y Excel}
                            {--output= : Directorio de salida personalizado}
                            {--preview : Solo mostrar preview de datos sin generar archivos}';

    /**
     * The console command description.
     */
    protected $description = 'Genera reporte de elegibilidad organizado por Unidad Organizacional > Perfil > Postulaciones';

    protected EligibilityReportService $reportService;

    public function __construct(EligibilityReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $postingId = $this->argument('posting_id');
        $generatePdf = $this->option('pdf') || $this->option('all');
        $generateExcel = $this->option('excel') || $this->option('all');
        $outputPath = $this->option('output');
        $preview = $this->option('preview');

        // Si no se especifica formato, generar ambos
        if (!$generatePdf && !$generateExcel && !$preview) {
            $generatePdf = true;
            $generateExcel = true;
        }

        // Buscar convocatoria por ID o codigo
        $posting = JobPosting::where('id', $postingId)
            ->orWhere('code', $postingId)
            ->first();

        if (!$posting) {
            $this->error("Convocatoria no encontrada: {$postingId}");
            return 1;
        }

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║     GENERADOR DE REPORTE DE ELEGIBILIDAD - MDSJ             ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info("Convocatoria: {$posting->code} - {$posting->title}");
        $this->info("Año: {$posting->year}");
        $this->info('');

        // Obtener datos del reporte
        $this->info('Obteniendo y filtrando postulaciones...');

        try {
            $reportData = $this->reportService->generateReport($posting);
        } catch (\Exception $e) {
            $this->error("Error al obtener datos: {$e->getMessage()}");
            return 1;
        }

        // Mostrar estadísticas
        $this->displayStatistics($reportData);

        // Modo preview
        if ($preview) {
            $this->displayPreview($reportData);
            return 0;
        }

        // Generar archivos
        $generatedFiles = [];

        if ($generateExcel) {
            $this->info('');
            $this->info('Generando Excel...');

            try {
                $excelPath = $this->reportService->generateExcel($posting, $outputPath);
                $generatedFiles['excel'] = $excelPath;
                $this->info("  Excel generado: {$excelPath}");
            } catch (\Exception $e) {
                $this->error("  Error generando Excel: {$e->getMessage()}");
            }
        }

        if ($generatePdf) {
            $this->info('');
            $this->info('Generando PDF...');

            try {
                $pdfPath = $this->reportService->generatePdf($posting);
                $generatedFiles['pdf'] = $pdfPath;
                $this->info("  PDF generado: {$pdfPath}");
            } catch (\Exception $e) {
                $this->error("  Error generando PDF: {$e->getMessage()}");
            }
        }

        // Resumen final
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('RESUMEN DE GENERACION');
        $this->info('═══════════════════════════════════════════════════════════════');

        if (!empty($generatedFiles)) {
            $this->info('Archivos generados exitosamente:');
            foreach ($generatedFiles as $type => $path) {
                $this->line("  [{$type}] {$path}");
            }
        } else {
            $this->warn('No se generaron archivos.');
        }

        $this->info('');

        return 0;
    }

    /**
     * Mostrar estadísticas del reporte
     */
    private function displayStatistics(array $data): void
    {
        $stats = $data['stats'];

        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('ESTADISTICAS GENERALES');
        $this->info('═══════════════════════════════════════════════════════════════');

        $this->table(
            ['Indicador', 'Cantidad', 'Porcentaje'],
            [
                ['Total Postulantes (filtrados por DNI)', $stats['total'], '100%'],
                ['APTOS', $stats['eligible'], $stats['total'] > 0 ? number_format(($stats['eligible'] / $stats['total']) * 100, 1) . '%' : '0%'],
                ['NO APTOS', $stats['not_eligible'], $stats['total'] > 0 ? number_format(($stats['not_eligible'] / $stats['total']) * 100, 1) . '%' : '0%'],
                ['Unidades Organizacionales', $stats['units_count'], '-'],
                ['Perfiles de Puesto', $stats['profiles_count'], '-'],
            ]
        );

        $this->info('');
        $this->info('RESUMEN POR UNIDAD ORGANIZACIONAL');
        $this->info('───────────────────────────────────────────────────────────────');

        $unitRows = [];
        foreach ($data['units'] as $unit) {
            $unitRows[] = [
                $unit['code'],
                \Illuminate\Support\Str::limit($unit['name'], 35),
                count($unit['profiles']),
                $unit['stats']['total'],
                $unit['stats']['eligible'],
                $unit['stats']['not_eligible'],
            ];
        }

        $this->table(
            ['Codigo', 'Unidad Organizacional', 'Perfiles', 'Total', 'Aptos', 'No Aptos'],
            $unitRows
        );
    }

    /**
     * Mostrar preview detallado
     */
    private function displayPreview(array $data): void
    {
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('PREVIEW DETALLADO');
        $this->info('═══════════════════════════════════════════════════════════════');

        foreach ($data['units'] as $unit) {
            $this->info('');
            $this->line("<fg=cyan;options=bold>▶ UNIDAD: {$unit['name']}</>");
            $this->line("  Codigo: {$unit['code']} | Total: {$unit['stats']['total']} | Aptos: {$unit['stats']['eligible']} | No Aptos: {$unit['stats']['not_eligible']}");

            foreach ($unit['profiles'] as $profile) {
                $this->line('');
                $this->line("  <fg=yellow>├─ PERFIL: {$profile['code']} - {$profile['title']}</>");
                $this->line("  │  Codigo Cargo: {$profile['position_code']} | Vacantes: {$profile['vacancies']}");
                $this->line("  │  Postulantes: {$profile['stats']['total']} (Aptos: {$profile['stats']['eligible']}, No Aptos: {$profile['stats']['not_eligible']})");

                // Mostrar primeros 5 postulantes como muestra
                $shown = 0;
                foreach ($profile['applications'] as $app) {
                    if ($shown >= 5) {
                        $remaining = count($profile['applications']) - 5;
                        $this->line("  │     ... y {$remaining} postulantes mas");
                        break;
                    }

                    $status = $app->is_eligible ? '<fg=green>APTO</>' : '<fg=red>NO APTO</>';
                    $this->line("  │     {$app->code} | {$app->dni} | " . \Illuminate\Support\Str::limit($app->full_name, 25) . " | {$status}");
                    $shown++;
                }
            }
        }

        $this->info('');
        $this->warn('Modo PREVIEW: No se generaron archivos. Use --pdf, --excel o --all para generar.');
    }
}
