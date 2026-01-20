<?php

namespace Modules\Application\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class ValidCareersReferenceExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected Collection $careers;

    public function __construct(Collection $careers)
    {
        $this->careers = $careers;
    }

    public function collection()
    {
        return $this->careers;
    }

    public function headings(): array
    {
        return [
            'ID (Usar este valor)',
            'Código',
            'Nombre',
            'Nombre Corto',
            'Categoría SUNEDU',
            'Grupo de Categoría',
            'Requiere Colegiatura',
            'Activo',
        ];
    }

    public function map($career): array
    {
        return [
            $career->id,
            $career->code,
            $career->name,
            $career->short_name ?? '',
            $career->sunedu_category ?? '',
            $career->category_group ?? '',
            $career->requires_colegiatura ? 'Sí' : 'No',
            $career->is_active ? 'Sí' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->careers->count() + 1;

        // Estilo para el encabezado
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'], // Verde
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Columna A (ID) resaltada en verde claro
        $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C6EFCE'],
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Altura de fila del encabezado
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }

    public function title(): string
    {
        return 'Carreras Válidas (Referencia)';
    }
}
