<?php

namespace Modules\Results\app\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Modules\Application\Entities\Application;

class WinnersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $posting;
    protected $winners;
    protected $rowNumber = 0;

    public function __construct($posting)
    {
        $this->posting = $posting;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener solo las aplicaciones GANADORAS de esta convocatoria
        // La relación es: JobPosting -> JobProfile -> Application
        $this->winners = Application::query()
            ->whereHas('jobProfile', function ($query) {
                $query->where('job_posting_id', $this->posting->id);
            })
            ->where('selection_result', 'GANADOR')
            ->with([
                'jobProfile.organizationalUnit',
                'jobProfile.positionCode',
            ])
            ->orderBy('final_ranking', 'asc')
            ->get();

        // Cargar manualmente los datos del User para cada aplicación
        foreach ($this->winners as $application) {
            $application->userData = \Modules\User\Entities\User::find($application->applicant_id);
        }

        return $this->winners;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'N°',
            'Código Postulación',
            'DNI',
            'Apellidos y Nombres',
            'Género',
            'Fecha Nacimiento',
            'Edad',
            'Correo Electrónico',
            'Teléfono',
            'Celular',
            'Dirección',
            'Distrito',
            'Provincia',
            'Departamento',
            'Código de Cargo',
            'Nombre de Cargo',
            'Perfil/Puesto',
            'Unidad Organizacional',
            'Vacante Asignada',
        ];
    }

    /**
     * @param mixed $application
     * @return array
     */
    public function map($application): array
    {
        $this->rowNumber++;

        // Calcular edad
        $age = null;
        if ($application->birth_date) {
            $birthDate = \Carbon\Carbon::parse($application->birth_date);
            $age = $birthDate->age;
        }

        // Obtener género desde el usuario (userData cargado manualmente)
        $gender = 'N/A';
        if (isset($application->userData) && $application->userData && !empty($application->userData->gender)) {
            $genderValue = $application->userData->gender;
            $genderMap = [
                'M' => 'Masculino',
                'F' => 'Femenino',
                'MASCULINO' => 'Masculino',
                'FEMENINO' => 'Femenino',
                'male' => 'Masculino',
                'female' => 'Femenino',
                'masculino' => 'Masculino',
                'femenino' => 'Femenino',
            ];
            $gender = $genderMap[strtoupper($genderValue)] ?? $genderMap[$genderValue] ?? $genderValue;
        }

        // Obtener datos geográficos desde el usuario (userData)
        $district = 'N/A';
        $province = 'N/A';
        $department = 'N/A';

        if (isset($application->userData) && $application->userData) {
            $district = !empty($application->userData->district) ? $application->userData->district : 'N/A';
            $province = !empty($application->userData->province) ? $application->userData->province : 'N/A';
            $department = !empty($application->userData->department) ? $application->userData->department : 'N/A';
        }

        // Obtener unidad organizacional
        $organizationalUnit = $application->jobProfile->organizationalUnit
            ? $application->jobProfile->organizationalUnit->name
            : 'N/A';

        // Obtener código de cargo y nombre
        $positionCode = $application->jobProfile->positionCode
            ? $application->jobProfile->positionCode->code
            : 'N/A';

        $positionName = $application->jobProfile->positionCode
            ? $application->jobProfile->positionCode->name
            : 'N/A';

        return [
            $this->rowNumber,
            $application->code,
            $application->dni,
            $application->full_name,
            $gender,
            $application->birth_date ? \Carbon\Carbon::parse($application->birth_date)->format('d/m/Y') : 'N/A',
            $age,
            $application->email,
            $application->phone ?? 'N/A',
            $application->mobile_phone ?? 'N/A',
            $application->address ?? 'N/A',
            $district,
            $province,
            $department,
            $positionCode,
            $positionName,
            $application->jobProfile->profile_name ?? 'N/A',
            $organizationalUnit,
            $application->assignedVacancy?->code ?? 'N/A',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
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
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Aplicar bordes a todas las celdas con datos
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Centrar columnas numéricas y de datos estructurados
                // A: N°, B: Código, C: DNI, E: Género, F: Fecha, G: Edad, O: Código de Cargo, S: Vacante
                $sheet->getStyle("A2:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("O2:O{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("S2:S{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alinear a la izquierda: nombres (D), correos (H), teléfonos (I-J), direcciones (K-N), cargo nombre (P), títulos (Q-R)
                $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("H2:N{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("P2:R{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Ajustar altura de fila de encabezados
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Congelar primera fila (encabezados)
                $sheet->freezePane('A2');

                // Resaltar todas las filas en verde (todos son ganadores)
                foreach ($this->winners as $index => $application) {
                    $row = $index + 2; // +2 porque empezamos en la fila 2 (1 es header)

                    // Verde para ganadores
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('C6EFCE');
                }

                // Agregar filtros automáticos
                $sheet->setAutoFilter("A1:{$highestColumn}1");
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Ganadores';
    }
}
