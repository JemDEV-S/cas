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

class InvalidCareerIdsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected Collection $records;
    protected array $validCareers;

    public function __construct(Collection $records, array $validCareers = [])
    {
        $this->records = $records;
        $this->validCareers = $validCareers;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'ID (No modificar)',
            'Código Postulación',
            'Postulante',
            'DNI',
            'Career ID Inválido',
            'Tipo de Grado',
            'Campo de Carrera',
            'Título del Grado',
            'Institución',
            'NUEVO CAREER ID (Completar)',
        ];
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->application->code ?? 'N/A',
            $record->application->full_name ?? 'N/A',
            $record->application->dni ?? 'N/A',
            $record->career_id,
            $record->degree_type,
            $record->career_field ?? '',
            $record->degree_title ?? '',
            $record->institution_name ?? '',
            '', // Columna vacía para que el usuario complete
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->records->count() + 1;

        // Estilo para el encabezado
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Resaltar columna J (NUEVO CAREER ID) en amarillo
        $sheet->getStyle("J2:J{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Columna A (ID) en gris claro - no modificar
        $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9D9D9'],
            ],
        ]);

        // Columna E (Career ID Inválido) en rojo claro
        $sheet->getStyle("E2:E{$lastRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFCCCC'],
            ],
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Altura de fila del encabezado
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Agregar hoja de referencia con carreras válidas
        return [];
    }

    public function title(): string
    {
        return 'Registros Inválidos';
    }
}
