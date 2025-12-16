<?php

namespace Modules\JobProfile\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Modules\JobProfile\Entities\JobProfile;

class BudgetReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithColumnFormatting
{
    protected $jobPostingId;
    protected $totalBudget = 0;

    public function __construct($jobPostingId = null)
    {
        $this->jobPostingId = $jobPostingId;
    }

    /**
     * Obtiene la colección de perfiles aprobados
     */
    public function collection()
    {
        $query = JobProfile::with(['positionCode', 'organizationalUnit', 'jobPosting'])
            ->where('status', 'approved');

        // Filtrar por convocatoria si se especifica
        if ($this->jobPostingId) {
            $query->where('job_posting_id', $this->jobPostingId);
        }

        return $query->orderBy('job_posting_id')
            ->orderBy('organizational_unit_id')
            ->get();
    }

    /**
     * Define los encabezados del reporte
     */
    public function headings(): array
    {
        return [
            'Código Convocatoria',
            'Título Convocatoria',
            'Código Perfil',
            'Título del Perfil',
            'Cargo',
            'Código de Cargo',
            'Unidad Organizacional',
            'Salario Base',
            'Nº Vacantes',
            'Subtotal Mensual',
            'Meses',
            'Total Presupuesto',
            'Estado',
        ];
    }

    /**
     * Mapea cada fila con los datos correspondientes
     */
    public function map($jobProfile): array
    {
        $baseSalary = $jobProfile->positionCode->base_salary ?? 0;
        $vacancies = $jobProfile->total_vacancies ?? 1;
        $contractMonths = 3; // Por defecto 3 meses

        $subtotalMensual = $baseSalary * $vacancies;
        $totalPresupuesto = $subtotalMensual * $contractMonths;

        // Acumular el total general
        $this->totalBudget += $totalPresupuesto;

        return [
            $jobProfile->jobPosting->code ?? 'N/A',
            $jobProfile->jobPosting->title ?? 'N/A',
            $jobProfile->code,
            $jobProfile->title,
            $jobProfile->positionCode->name ?? 'N/A',
            $jobProfile->positionCode->code ?? 'N/A',
            $jobProfile->organizationalUnit->name ?? 'N/A',
            $baseSalary,
            $vacancies,
            $subtotalMensual,
            $contractMonths,
            $totalPresupuesto,
            $jobProfile->status_label,
        ];
    }

    /**
     * Aplica estilos al worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo para el encabezado (fila 1)
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Agregar fila de totales al final
        $lastRow = $sheet->getHighestRow() + 1;
        $sheet->setCellValue("A{$lastRow}", 'TOTAL GENERAL');
        $sheet->setCellValue("L{$lastRow}", $this->totalBudget);

        // Estilo para la fila de totales
        $sheet->getStyle("A{$lastRow}:M{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        // Centrar algunas columnas
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C:C')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I:I')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K:K')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M:M')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * Formato de columnas
     */
    public function columnFormats(): array
    {
        return [
            'H' => '#,##0.00', // Salario Base
            'J' => '#,##0.00', // Subtotal Mensual
            'L' => '#,##0.00', // Total Presupuesto
        ];
    }

    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return 'Presupuesto Convocatoria';
    }
}
