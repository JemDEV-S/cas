<?php

namespace Modules\Results\Services;

use Modules\Results\Entities\ResultPublication;
use Modules\Results\Entities\ResultExport;
use Modules\Results\Enums\PublicationPhaseEnum;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ResultExportService
{
    /**
     * Exportar resultados a Excel
     */
    public function exportToExcel(
        ResultPublication $publication,
        array $applications,
        string $phase
    ): ResultExport {

        $spreadsheet = $this->createSpreadsheet($publication, $applications, $phase);

        // Generar nombre de archivo
        $fileName = $this->generateFileName($publication, 'xlsx');
        $filePath = "results/exports/{$fileName}";

        // Guardar archivo
        $writer = new Xlsx($spreadsheet);
        $fullPath = storage_path("app/public/{$filePath}");

        // Crear directorio si no existe
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($fullPath);

        $fileSize = filesize($fullPath);
        $rowsCount = count($applications);

        // Guardar registro de exportación
        $export = ResultExport::create([
            'result_publication_id' => $publication->id,
            'format' => 'excel',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'rows_count' => $rowsCount,
            'exported_by' => auth()->id() ?? 'system',
            'exported_at' => now(),
            'metadata' => [
                'phase' => $phase,
                'sheet_count' => 1,
            ],
        ]);

        // Actualizar publicación con ruta de Excel
        $publication->update(['excel_path' => $filePath]);

        return $export;
    }

    /**
     * Exportar a CSV
     */
    public function exportToCsv(
        ResultPublication $publication,
        array $applications,
        string $phase
    ): ResultExport {

        $spreadsheet = $this->createSpreadsheet($publication, $applications, $phase);

        $fileName = $this->generateFileName($publication, 'csv');
        $filePath = "results/exports/{$fileName}";

        $writer = new CsvWriter($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $fullPath = storage_path("app/public/{$filePath}");

        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($fullPath);

        $fileSize = filesize($fullPath);
        $rowsCount = count($applications);

        $export = ResultExport::create([
            'result_publication_id' => $publication->id,
            'format' => 'csv',
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'rows_count' => $rowsCount,
            'exported_by' => auth()->id() ?? 'system',
            'exported_at' => now(),
            'metadata' => [
                'phase' => $phase,
                'delimiter' => ';',
                'encoding' => 'UTF-8',
            ],
        ]);

        return $export;
    }

    /**
     * Crear spreadsheet según la fase
     */
    private function createSpreadsheet(
        ResultPublication $publication,
        array $applications,
        string $phase
    ): Spreadsheet {

        return match($phase) {
            'PHASE_04' => $this->createPhase4Spreadsheet($publication, $applications),
            'PHASE_07' => $this->createPhase7Spreadsheet($publication, $applications),
            'PHASE_09' => $this->createPhase9Spreadsheet($publication, $applications),
            default => throw new \Exception("Fase no válida: {$phase}"),
        };
    }

    /**
     * Crear Excel para Fase 4 (Elegibilidad)
     */
    private function createPhase4Spreadsheet(ResultPublication $publication, array $applications): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resultados Elegibilidad');

        // Encabezado
        $headers = ['N°', 'Código', 'Apellidos y Nombres', 'DNI', 'Vacante', 'Resultado', 'Motivo de No Elegibilidad'];
        $sheet->fromArray($headers, null, 'A1');

        // Estilo del encabezado
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Datos
        $row = 2;
        foreach ($applications as $index => $app) {
            $sheet->fromArray([
                $index + 1,
                $app['code'] ?? 'N/A',
                $app['full_name'] ?? '',
                $app['dni'] ?? '',
                $app['vacancy']['code'] ?? '',
                $app['is_eligible'] ? 'APTO' : 'NO APTO',
                $app['ineligibility_reason'] ?? '',
            ], null, "A{$row}");

            // Color de fila según resultado
            $fillColor = $app['is_eligible'] ? 'C6EFCE' : 'FFC7CE';
            $sheet->getStyle("F{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($fillColor);

            $row++;
        }

        // Ajustar anchos de columna
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Agregar estadísticas al final
        $row += 2;
        $sheet->setCellValue("A{$row}", 'ESTADÍSTICAS');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue("A{$row}", "Total postulantes: {$publication->total_applicants}");
        $row++;
        $sheet->setCellValue("A{$row}", "Total APTOS: {$publication->total_eligible}");
        $row++;
        $sheet->setCellValue("A{$row}", "Total NO APTOS: {$publication->total_not_eligible}");

        return $spreadsheet;
    }

    /**
     * Crear Excel para Fase 7 (Evaluación Curricular)
     */
    private function createPhase7Spreadsheet(ResultPublication $publication, array $applications): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Evaluación Curricular');

        // Encabezado
        $headers = ['Ranking', 'Código', 'Apellidos y Nombres', 'DNI', 'Vacante', 'Puntaje Curricular', 'Observaciones'];
        $sheet->fromArray($headers, null, 'A1');

        // Estilo del encabezado
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Datos
        $row = 2;
        foreach ($applications as $index => $app) {
            $sheet->fromArray([
                $app['ranking'] ?? ($index + 1),
                $app['code'] ?? '',
                $app['full_name'] ?? '',
                $app['dni'] ?? '',
                $app['vacancy']['code'] ?? '',
                number_format($app['curriculum_score'] ?? 0, 2),
                '',
            ], null, "A{$row}");

            // Resaltar top 3
            if (($app['ranking'] ?? ($index + 1)) <= 3) {
                $sheet->getStyle("A{$row}:G{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFEB9C');
            }

            $row++;
        }

        // Ajustar anchos
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Crear Excel para Fase 9 (Resultados Finales)
     */
    private function createPhase9Spreadsheet(ResultPublication $publication, array $applications): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resultados Finales');

        // Encabezado
        $headers = [
            'Ranking',
            'Código',
            'Apellidos y Nombres',
            'DNI',
            'Vacante',
            'Puntaje Curricular',
            'Puntaje Entrevista',
            'Bonificación',
            'Puntaje Final',
            'Observaciones'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Estilo del encabezado
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Datos
        $row = 2;
        foreach ($applications as $index => $app) {
            $sheet->fromArray([
                $app['final_ranking'] ?? ($index + 1),
                $app['code'] ?? '',
                $app['full_name'] ?? '',
                $app['dni'] ?? '',
                $app['vacancy']['code'] ?? '',
                number_format($app['curriculum_score'] ?? 0, 2),
                number_format($app['interview_score'] ?? 0, 2),
                number_format($app['special_condition_bonus'] ?? 0, 2),
                number_format($app['final_score'] ?? 0, 2),
                '',
            ], null, "A{$row}");

            // Resaltar ganador
            if (($app['final_ranking'] ?? ($index + 1)) === 1) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
            }

            $row++;
        }

        // Ajustar anchos
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFileName(ResultPublication $publication, string $extension): string
    {
        $phase = $publication->phase->value;
        $postingCode = str_replace(['/', ' '], '_', $publication->jobPosting->code ?? 'SIN_CODIGO');
        $timestamp = now()->format('Y-m-d_His');

        return "{$phase}_{$postingCode}_{$timestamp}.{$extension}";
    }
}
