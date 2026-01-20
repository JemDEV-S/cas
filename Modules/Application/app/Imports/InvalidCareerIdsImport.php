<?php

namespace Modules\Application\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Modules\Application\Entities\ApplicationAcademic;
use Modules\Application\Entities\AcademicCareer;

class InvalidCareerIdsImport implements WithMultipleSheets
{
    protected InvalidCareerIdsSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new InvalidCareerIdsSheetImport();
    }

    public function sheets(): array
    {
        return [
            0 => $this->sheetImport, // Solo la primera hoja
        ];
    }

    public function getResults(): array
    {
        return $this->sheetImport->getResults();
    }

    public function getSummary(): array
    {
        return $this->sheetImport->getSummary();
    }
}

class InvalidCareerIdsSheetImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    protected array $validCareerIds;
    protected array $results = [];
    protected int $updated = 0;
    protected int $skipped = 0;
    protected int $errors = 0;

    public function __construct()
    {
        $this->validCareerIds = AcademicCareer::pluck('id')->toArray();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque el índice empieza en 0 y hay encabezado

            // Obtener valores de las columnas
            $id = $row['id_no_modificar'] ?? $row['id'] ?? null;
            $newCareerId = $row['nuevo_career_id_completar'] ?? $row['nuevo_career_id'] ?? null;

            // Limpiar espacios
            $id = trim((string) $id);
            $newCareerId = trim((string) $newCareerId);

            // Si no hay nuevo career_id, saltar
            if (empty($newCareerId)) {
                $this->skipped++;
                $this->results[] = [
                    'row' => $rowNumber,
                    'id' => $id,
                    'status' => 'SALTADO',
                    'message' => 'Sin nuevo career_id especificado',
                ];
                continue;
            }

            // Verificar que el ID existe
            if (empty($id)) {
                $this->errors++;
                $this->results[] = [
                    'row' => $rowNumber,
                    'id' => 'N/A',
                    'status' => 'ERROR',
                    'message' => 'ID de registro vacío',
                ];
                continue;
            }

            // Verificar que el nuevo career_id es válido
            if (!in_array($newCareerId, $this->validCareerIds)) {
                $this->errors++;
                $this->results[] = [
                    'row' => $rowNumber,
                    'id' => $id,
                    'status' => 'ERROR',
                    'message' => "Career ID '{$newCareerId}' no existe en academic_careers",
                ];
                continue;
            }

            // Buscar el registro
            $record = ApplicationAcademic::find($id);
            if (!$record) {
                $this->errors++;
                $this->results[] = [
                    'row' => $rowNumber,
                    'id' => $id,
                    'status' => 'ERROR',
                    'message' => 'Registro ApplicationAcademic no encontrado',
                ];
                continue;
            }

            // Actualizar
            $oldCareerId = $record->career_id;
            $record->career_id = $newCareerId;
            $record->save();

            $this->updated++;
            $this->results[] = [
                'row' => $rowNumber,
                'id' => $id,
                'status' => 'ACTUALIZADO',
                'message' => "career_id: {$oldCareerId} → {$newCareerId}",
            ];
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getErrorsCount(): int
    {
        return $this->errors;
    }

    public function getSummary(): array
    {
        return [
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'total' => $this->updated + $this->skipped + $this->errors,
        ];
    }
}
