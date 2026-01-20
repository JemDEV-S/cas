<?php

namespace Modules\Document\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobProfile\Entities\JobProfile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EligibilityReportService
{
    public function __construct()
    {
        //
    }

    /**
     * Obtener postulaciones evaluadas filtradas por DNI (solo la más reciente)
     */
    public function getFilteredApplications(string $jobPostingId): Collection
    {
        // Obtener IDs de perfiles que pertenecen a esta convocatoria
        $profileIds = JobProfile::where('job_posting_id', $jobPostingId)->pluck('id');

        if ($profileIds->isEmpty()) {
            return collect();
        }

        // Subconsulta para obtener la postulación más reciente por DNI
        $latestApplications = DB::table('applications')
            ->select('dni', DB::raw('MAX(created_at) as max_created_at'))
            ->whereIn('job_profile_id', $profileIds)
            ->whereNotNull('is_eligible')
            ->whereNull('deleted_at')
            ->groupBy('dni');

        // Obtener las postulaciones más recientes con todas sus relaciones
        $applications = Application::query()
            ->joinSub($latestApplications, 'latest', function ($join) {
                $join->on('applications.dni', '=', 'latest.dni')
                    ->on('applications.created_at', '=', 'latest.max_created_at');
            })
            ->whereIn('applications.job_profile_id', $profileIds)
            ->whereNotNull('applications.is_eligible')
            ->with([
                'jobProfile.organizationalUnit',
                'jobProfile.requestingUnit',
                'jobProfile.positionCode',
                'latestEvaluation',
            ])
            ->get();

        return $applications;
    }

    /**
     * Organizar postulaciones por Unidad Organizacional > Perfil > Postulaciones
     */
    public function organizeByStructure(Collection $applications): array
    {
        $structure = [];

        foreach ($applications as $application) {
            $jobProfile = $application->jobProfile;
            if (!$jobProfile) {
                continue;
            }

            // Usar requesting_unit o organizational_unit
            $unit = $jobProfile->requestingUnit ?? $jobProfile->organizationalUnit;
            $unitId = $unit?->id ?? 'sin_unidad';
            $unitName = $unit?->name ?? 'Sin Unidad Asignada';
            $unitCode = $unit?->code ?? 'N/A';

            // Inicializar unidad si no existe
            if (!isset($structure[$unitId])) {
                $structure[$unitId] = [
                    'id' => $unitId,
                    'code' => $unitCode,
                    'name' => $unitName,
                    'order' => $unit?->order ?? 999,
                    'profiles' => [],
                    'stats' => [
                        'total' => 0,
                        'eligible' => 0,
                        'not_eligible' => 0,
                    ],
                ];
            }

            $profileId = $jobProfile->id;

            // Inicializar perfil si no existe
            if (!isset($structure[$unitId]['profiles'][$profileId])) {
                $structure[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $jobProfile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => [
                        'total' => 0,
                        'eligible' => 0,
                        'not_eligible' => 0,
                    ],
                ];
            }

            // Agregar postulación
            $structure[$unitId]['profiles'][$profileId]['applications'][] = $application;

            // Actualizar estadísticas del perfil
            $structure[$unitId]['profiles'][$profileId]['stats']['total']++;
            if ($application->is_eligible) {
                $structure[$unitId]['profiles'][$profileId]['stats']['eligible']++;
            } else {
                $structure[$unitId]['profiles'][$profileId]['stats']['not_eligible']++;
            }

            // Actualizar estadísticas de la unidad
            $structure[$unitId]['stats']['total']++;
            if ($application->is_eligible) {
                $structure[$unitId]['stats']['eligible']++;
            } else {
                $structure[$unitId]['stats']['not_eligible']++;
            }
        }

        // Ordenar postulaciones dentro de cada perfil (APTOS primero, luego NO APTOS)
        foreach ($structure as &$unit) {
            foreach ($unit['profiles'] as &$profile) {
                usort($profile['applications'], function ($a, $b) {
                    // Primero por elegibilidad (APTOS primero)
                    if ($a->is_eligible !== $b->is_eligible) {
                        return $b->is_eligible <=> $a->is_eligible;
                    }
                    // Luego por nombre
                    return strcmp($a->full_name, $b->full_name);
                });
            }

            // Convertir profiles de array asociativo a indexado
            $unit['profiles'] = array_values($unit['profiles']);
        }

        // Ordenar unidades por orden y nombre
        uasort($structure, function ($a, $b) {
            if ($a['order'] !== $b['order']) {
                return $a['order'] <=> $b['order'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return array_values($structure);
    }

    /**
     * Calcular estadísticas globales
     */
    public function calculateGlobalStats(array $organizedData): array
    {
        $stats = [
            'total' => 0,
            'eligible' => 0,
            'not_eligible' => 0,
            'units_count' => count($organizedData),
            'profiles_count' => 0,
        ];

        foreach ($organizedData as $unit) {
            $stats['total'] += $unit['stats']['total'];
            $stats['eligible'] += $unit['stats']['eligible'];
            $stats['not_eligible'] += $unit['stats']['not_eligible'];
            $stats['profiles_count'] += count($unit['profiles']);
        }

        return $stats;
    }

    /**
     * Generar reporte completo
     */
    public function generateReport(JobPosting $jobPosting, ?string $userId = null): array
    {
        // Obtener y filtrar postulaciones
        $applications = $this->getFilteredApplications($jobPosting->id);

        // Organizar por estructura
        $organizedData = $this->organizeByStructure($applications);

        // Calcular estadísticas globales
        $globalStats = $this->calculateGlobalStats($organizedData);

        return [
            'posting' => $jobPosting,
            'title' => 'RESULTADO DE EVALUACION DE ELEGIBILIDAD',
            'subtitle' => $jobPosting->title ?? 'Proceso de Seleccion CAS ' . $jobPosting->year,
            'phase' => 'Evaluacion de Elegibilidad',
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i'),
            'stats' => $globalStats,
            'units' => $organizedData,
            'generated_at' => now(),
        ];
    }

    /**
     * Generar PDF del reporte
     */
    public function generatePdf(JobPosting $jobPosting, ?string $outputPath = null): string
    {
        $reportData = $this->generateReport($jobPosting);

        // Renderizar el template blade directamente
        $html = View::make('document::templates.result_eligibility_mdsj', $reportData)->render();

        // Generar PDF con DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('defaultFont', 'Arial');

        // Generar nombre de archivo
        $fileName = sprintf(
            'Resultado_Elegibilidad_%s_%s.pdf',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        $directory = $outputPath ?? storage_path('app/reports/eligibility');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Guardar PDF
        $pdf->save($filePath);

        return $filePath;
    }

    /**
     * Generar Excel del reporte
     */
    public function generateExcel(JobPosting $jobPosting, ?string $outputPath = null): string
    {
        $reportData = $this->generateReport($jobPosting);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // Hoja 1: Resumen General
        $this->createSummarySheet($spreadsheet, $reportData);

        // Hoja 2: Detalle por Unidad Organizacional
        $this->createDetailSheet($spreadsheet, $reportData);

        // Hoja 3: Lista completa de postulantes
        $this->createFullListSheet($spreadsheet, $reportData);

        // Guardar archivo
        $fileName = sprintf(
            'Resultado_Elegibilidad_%s_%s.xlsx',
            str_replace(['/', '\\', ' '], '_', $jobPosting->code),
            now()->format('Y-m-d_His')
        );

        $directory = $outputPath ?? storage_path('app/reports/eligibility');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Crear hoja de resumen
     */
    private function createSummarySheet(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Resumen');

        $row = 1;

        // Encabezado institucional
        $sheet->setCellValue("A{$row}", 'MUNICIPALIDAD DISTRITAL DE SAN JERONIMO');
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $sheet->setCellValue("A{$row}", 'Oficina de Recursos Humanos');
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Título del reporte
        $sheet->setCellValue("A{$row}", $data['title']);
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Información del proceso
        $sheet->setCellValue("A{$row}", 'Convocatoria:');
        $sheet->setCellValue("B{$row}", $data['posting']->code);
        $sheet->setCellValue("D{$row}", 'Fecha:');
        $sheet->setCellValue("E{$row}", $data['date']);
        $row++;

        $sheet->setCellValue("A{$row}", 'Titulo:');
        $sheet->setCellValue("B{$row}", $data['posting']->title ?? 'CAS ' . $data['posting']->year);
        $sheet->mergeCells("B{$row}:E{$row}");
        $row += 2;

        // Estadísticas globales
        $sheet->setCellValue("A{$row}", 'ESTADISTICAS GENERALES');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);
        $row++;

        $statsHeaders = ['Indicador', 'Cantidad', 'Porcentaje'];
        $sheet->fromArray($statsHeaders, null, "A{$row}");
        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        $total = $data['stats']['total'];
        $statsData = [
            ['Total Postulantes', $total, '100%'],
            ['APTOS', $data['stats']['eligible'], $total > 0 ? number_format(($data['stats']['eligible'] / $total) * 100, 1) . '%' : '0%'],
            ['NO APTOS', $data['stats']['not_eligible'], $total > 0 ? number_format(($data['stats']['not_eligible'] / $total) * 100, 1) . '%' : '0%'],
            ['Unidades Organizacionales', $data['stats']['units_count'], '-'],
            ['Perfiles de Puesto', $data['stats']['profiles_count'], '-'],
        ];

        foreach ($statsData as $statRow) {
            $sheet->fromArray($statRow, null, "A{$row}");
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);

            // Colorear filas de APTOS y NO APTOS
            if ($statRow[0] === 'APTOS') {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6F6D5']],
                ]);
            } elseif ($statRow[0] === 'NO APTOS') {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FED7D7']],
                ]);
            }
            $row++;
        }

        $row += 2;

        // Resumen por Unidad Organizacional
        $sheet->setCellValue("A{$row}", 'RESUMEN POR UNIDAD ORGANIZACIONAL');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
        ]);
        $row++;

        $unitHeaders = ['Codigo', 'Unidad Organizacional', 'Perfiles', 'Total', 'Aptos', 'No Aptos'];
        $sheet->fromArray($unitHeaders, null, "A{$row}");
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        foreach ($data['units'] as $unit) {
            $sheet->fromArray([
                $unit['code'],
                $unit['name'],
                count($unit['profiles']),
                $unit['stats']['total'],
                $unit['stats']['eligible'],
                $unit['stats']['not_eligible'],
            ], null, "A{$row}");

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $row++;
        }

        // Ajustar anchos de columna
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(12);
    }

    /**
     * Crear hoja de detalle por unidad
     */
    private function createDetailSheet(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Detalle por Unidad');

        $row = 1;

        // Encabezado
        $sheet->setCellValue("A{$row}", 'DETALLE DE RESULTADOS POR UNIDAD ORGANIZACIONAL');
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $sheet->setCellValue("A{$row}", "Convocatoria: {$data['posting']->code} | Fecha: {$data['date']}");
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        foreach ($data['units'] as $unit) {
            // Encabezado de Unidad
            $sheet->setCellValue("A{$row}", "UNIDAD: {$unit['name']}");
            $sheet->mergeCells("A{$row}:I{$row}");
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D5A87']],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ]);
            $row++;

            foreach ($unit['profiles'] as $profile) {
                // Encabezado de Perfil
                $sheet->setCellValue("A{$row}", "Perfil: {$profile['code']} - {$profile['title']}");
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("H{$row}", "Vacantes: {$profile['vacancies']}");
                $sheet->mergeCells("H{$row}:I{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                ]);
                $row++;

                // Encabezados de la tabla de postulantes
                $headers = ['N', 'Codigo', 'DNI', 'Apellidos y Nombres', 'Resultado', 'Motivo'];
                $sheet->fromArray($headers, null, "A{$row}");
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $row++;

                $counter = 1;
                foreach ($profile['applications'] as $app) {
                    $sheet->fromArray([
                        $counter++,
                        $app->code,
                        $app->dni,
                        strtoupper($app->full_name),
                        $app->is_eligible ? 'APTO' : 'NO APTO',
                        $app->is_eligible ? '' : ($app->ineligibility_reason ?? 'Sin especificar'),
                    ], null, "A{$row}");

                    $fillColor = $app->is_eligible ? 'C6F6D5' : 'FED7D7';
                    $sheet->getStyle("E{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    $row++;
                }

                // Subtotales del perfil
                $sheet->setCellValue("C{$row}", "Subtotal:");
                $sheet->setCellValue("D{$row}", "Total: {$profile['stats']['total']} | Aptos: {$profile['stats']['eligible']} | No Aptos: {$profile['stats']['not_eligible']}");
                $sheet->mergeCells("D{$row}:F{$row}");
                $sheet->getStyle("C{$row}:F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'italic' => true, 'size' => 9],
                ]);
                $row += 2;
            }

            $row++;
        }

        // Ajustar anchos
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(50);
    }

    /**
     * Crear hoja con lista completa
     */
    private function createFullListSheet(Spreadsheet $spreadsheet, array $data): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Lista Completa');

        $row = 1;

        // Encabezado
        $sheet->setCellValue("A{$row}", 'LISTA COMPLETA DE POSTULANTES EVALUADOS');
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Encabezados de la tabla
        $headers = ['N', 'Codigo', 'DNI', 'Apellidos y Nombres', 'Unidad Organizacional', 'Perfil', 'Resultado', 'Motivo'];
        $sheet->fromArray($headers, null, "A{$row}");
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        $counter = 1;
        foreach ($data['units'] as $unit) {
            foreach ($unit['profiles'] as $profile) {
                foreach ($profile['applications'] as $app) {
                    $sheet->fromArray([
                        $counter++,
                        $app->code,
                        $app->dni,
                        strtoupper($app->full_name),
                        $unit['name'],
                        $profile['code'],
                        $app->is_eligible ? 'APTO' : 'NO APTO',
                        $app->is_eligible ? '' : ($app->ineligibility_reason ?? ''),
                    ], null, "A{$row}");

                    $fillColor = $app->is_eligible ? 'C6F6D5' : 'FED7D7';
                    $sheet->getStyle("G{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]],
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    // Fila con color alterno
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F7FAFC']],
                        ]);
                    }

                    $row++;
                }
            }
        }

        // Ajustar anchos
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(45);

        // Filtros automáticos
        $lastRow = $row - 1;
        $sheet->setAutoFilter("A3:H{$lastRow}");
    }
}
