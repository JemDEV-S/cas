<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DryRunEvaluationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected Collection $evaluations;
    protected string $postingCode;
    protected string $postingTitle;

    public function __construct(Collection $evaluations, string $postingCode, string $postingTitle)
    {
        $this->evaluations = $evaluations;
        $this->postingCode = $postingCode;
        $this->postingTitle = $postingTitle;
    }

    public function collection(): Collection
    {
        return $this->evaluations;
    }

    public function title(): string
    {
        return 'Evaluación DryRun';
    }

    public function headings(): array
    {
        return [
            'N°',
            'Código Postulación',
            'DNI',
            'Apellidos y Nombres',
            'Perfil/Cargo',
            'Resultado',
            'Formación Académica',
            'Detalle Formación',
            'Exp. General',
            'Detalle Exp. General',
            'Exp. Específica',
            'Detalle Exp. Específica',
            'Colegiatura',
            'Detalle Colegiatura',
            'Cursos',
            'Detalle Cursos',
            'Razones de No Aptitud',
        ];
    }

    public function map($row): array
    {
        return [
            $row['number'],
            $row['application_code'],
            $row['dni'],
            $row['full_name'],
            $row['job_profile'],
            $row['result'],
            $row['academics_status'],
            $row['academics_detail'],
            $row['general_exp_status'],
            $row['general_exp_detail'],
            $row['specific_exp_status'],
            $row['specific_exp_detail'],
            $row['colegiatura_status'],
            $row['colegiatura_detail'],
            $row['courses_status'],
            $row['courses_detail'],
            $row['reasons'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // N°
            'B' => 18,  // Código
            'C' => 12,  // DNI
            'D' => 35,  // Nombre
            'E' => 30,  // Perfil
            'F' => 12,  // Resultado
            'G' => 12,  // Formación
            'H' => 40,  // Detalle Formación
            'I' => 12,  // Exp General
            'J' => 35,  // Detalle Exp General
            'K' => 12,  // Exp Específica
            'L' => 35,  // Detalle Exp Específica
            'M' => 12,  // Colegiatura
            'N' => 30,  // Detalle Colegiatura
            'O' => 12,  // Cursos
            'P' => 35,  // Detalle Cursos
            'Q' => 60,  // Razones
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->evaluations->count() + 1;

        // Estilo para encabezados
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle("A1:Q{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Alineación vertical centrada para todas las celdas
        $sheet->getStyle("A2:Q{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A2:Q{$lastRow}")->getAlignment()->setWrapText(true);

        // Colorear filas según resultado
        for ($row = 2; $row <= $lastRow; $row++) {
            $resultCell = $sheet->getCell("F{$row}")->getValue();

            if ($resultCell === 'NO APTO') {
                // Fila roja clara para NO APTO
                $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'],
                    ],
                ]);
            } else {
                // Fila verde clara para APTO
                $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCFCE7'],
                    ],
                ]);
            }

            // Colorear celdas de estado individual
            $statusColumns = ['G', 'I', 'K', 'M', 'O']; // Columnas de estado
            foreach ($statusColumns as $col) {
                $value = $sheet->getCell("{$col}{$row}")->getValue();
                if ($value === 'NO CUMPLE') {
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                    ]);
                } elseif ($value === 'CUMPLE') {
                    $sheet->getStyle("{$col}{$row}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '16A34A'], 'bold' => true],
                    ]);
                }
            }
        }

        // Altura de filas
        $sheet->getDefaultRowDimension()->setRowHeight(25);
        $sheet->getRowDimension(1)->setRowHeight(35);

        return [];
    }
}
