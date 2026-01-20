<?php

namespace Modules\Application\Console;

use Illuminate\Console\Command;
use Modules\Application\Entities\ApplicationAcademic;
use Modules\Application\Entities\AcademicCareer;
use Modules\Application\Exports\InvalidCareerIdsMultiSheetExport;
use Modules\Application\Imports\InvalidCareerIdsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class FindInvalidCareerIdsCommand extends Command
{
    protected $signature = 'applications:find-invalid-careers
                            {--export= : Exportar a archivo Excel para corrección manual}
                            {--import= : Importar archivo Excel con correcciones}
                            {--dry-run : Simular importación sin guardar cambios}';

    protected $description = 'Encontrar y corregir ApplicationAcademic con career_id inválido';

    public function handle(): int
    {
        // Modo importación
        if ($importPath = $this->option('import')) {
            return $this->handleImport($importPath);
        }

        // Modo búsqueda/exportación
        return $this->handleExport();
    }

    protected function handleExport(): int
    {
        $this->info('🔍 Buscando ApplicationAcademic con career_id inválido...');
        $this->newLine();

        // Obtener todos los IDs válidos de academic_careers
        $validCareers = AcademicCareer::orderBy('name')->get();
        $validCareerIds = $validCareers->pluck('id')->toArray();

        $this->info("📊 Total de carreras válidas: " . count($validCareerIds));

        // Buscar ApplicationAcademic con career_id que no está en la lista válida
        $invalidRecords = ApplicationAcademic::whereNotNull('career_id')
            ->whereNotIn('career_id', $validCareerIds)
            ->with(['application:id,code,full_name,dni'])
            ->get();

        if ($invalidRecords->isEmpty()) {
            $this->info('✅ No se encontraron registros con career_id inválido.');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Se encontraron {$invalidRecords->count()} registros con career_id inválido:");
        $this->newLine();

        // Mostrar tabla resumen
        $tableData = $invalidRecords->map(fn($record) => [
            'id' => substr($record->id, 0, 8) . '...',
            'application_code' => $record->application->code ?? 'N/A',
            'applicant' => $record->application->full_name ?? 'N/A',
            'dni' => $record->application->dni ?? 'N/A',
            'career_id_invalid' => substr($record->career_id, 0, 8) . '...',
            'degree_type' => $record->degree_type,
            'career_field' => substr($record->career_field ?? 'N/A', 0, 20),
        ])->toArray();

        $this->table(
            ['ID', 'Código App', 'Postulante', 'DNI', 'Career ID (Inv)', 'Tipo Grado', 'Campo'],
            $tableData
        );

        // Agrupar por career_id inválido
        $groupedByCareer = $invalidRecords->groupBy('career_id');
        $this->newLine();
        $this->info('🔑 Career IDs inválidos únicos: ' . $groupedByCareer->count());

        foreach ($groupedByCareer as $careerId => $records) {
            $this->line("   - {$careerId}: {$records->count()} registro(s)");
        }

        // Exportar a Excel si se solicita
        if ($exportPath = $this->option('export')) {
            $this->newLine();
            $this->info('📁 Exportando a Excel...');

            // Asegurar extensión .xlsx
            if (!str_ends_with(strtolower($exportPath), '.xlsx')) {
                $exportPath .= '.xlsx';
            }

            Excel::store(
                new InvalidCareerIdsMultiSheetExport($invalidRecords, $validCareers),
                $exportPath,
                'local'
            );

            $fullPath = storage_path('app/' . $exportPath);
            $this->info("✅ Archivo exportado: {$fullPath}");
            $this->newLine();
            $this->info('📋 Instrucciones:');
            $this->line('   1. Abre el archivo Excel');
            $this->line('   2. Ve a la hoja "Carreras Válidas (Referencia)" y copia el ID de la carrera correcta');
            $this->line('   3. Ve a la hoja "Registros Inválidos" y pega en la columna J (NUEVO CAREER ID)');
            $this->newLine();
            $this->warn('   ⚠️  IMPORTANTE: Para evitar que Excel auto-incremente los IDs:');
            $this->line('      - Selecciona las celdas donde vas a pegar');
            $this->line('      - Usa "Pegar especial" (Ctrl+Shift+V) → "Solo valores"');
            $this->line('      - O pega con Ctrl+V y NO arrastres las celdas');
            $this->newLine();
            $this->line('   4. Guarda el archivo');
            $this->line('   5. Ejecuta: php artisan applications:find-invalid-careers --import=' . $exportPath . ' --dry-run');
            $this->line('   6. Si todo está correcto, ejecuta sin --dry-run para aplicar cambios');
        } else {
            $this->newLine();
            $this->info('💡 Para exportar a Excel y corregir manualmente:');
            $this->line('   php artisan applications:find-invalid-careers --export=invalid_careers.xlsx');
        }

        return Command::SUCCESS;
    }

    protected function handleImport(string $importPath): int
    {
        $this->info('📥 Importando correcciones desde Excel...');
        $this->newLine();

        // Verificar que el archivo existe
        $fullPath = storage_path('app/' . $importPath);

        if (!file_exists($fullPath)) {
            // Intentar ruta absoluta
            if (file_exists($importPath)) {
                $fullPath = $importPath;
            } else {
                $this->error("❌ Archivo no encontrado: {$importPath}");
                $this->line("   Rutas intentadas:");
                $this->line("   - {$fullPath}");
                $this->line("   - {$importPath}");
                return Command::FAILURE;
            }
        }

        $this->info("📄 Archivo: {$fullPath}");

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('🔍 Modo DRY RUN - No se guardarán cambios');
        }

        DB::beginTransaction();

        try {
            $import = new InvalidCareerIdsImport();
            // Solo importar la primera hoja (índice 0 = "Registros Inválidos")
            Excel::import($import, $fullPath, null, \Maatwebsite\Excel\Excel::XLSX);

            $results = $import->getResults();
            $summary = $import->getSummary();

            // Mostrar resultados detallados
            $this->newLine();
            $this->info('📊 Resultados de la importación:');

            $tableData = collect($results)->map(fn($r) => [
                'row' => $r['row'],
                'id' => substr($r['id'], 0, 12) . '...',
                'status' => $r['status'],
                'message' => $r['message'],
            ])->toArray();

            $this->table(['Fila', 'ID', 'Estado', 'Mensaje'], $tableData);

            // Resumen
            $this->newLine();
            $this->info('📈 Resumen:');
            $this->table(
                ['Métrica', 'Cantidad'],
                [
                    ['Total procesados', $summary['total']],
                    ['✅ Actualizados', $summary['updated']],
                    ['⏭️  Saltados (sin nuevo ID)', $summary['skipped']],
                    ['❌ Errores', $summary['errors']],
                ]
            );

            if ($dryRun) {
                DB::rollBack();
                $this->newLine();
                $this->warn('🔍 Modo DRY RUN: Los cambios NO fueron guardados');
                $this->info('Ejecuta sin --dry-run para aplicar los cambios');
            } else {
                if ($summary['updated'] > 0) {
                    if ($this->confirm("¿Confirmar {$summary['updated']} actualizaciones?", true)) {
                        DB::commit();
                        $this->newLine();
                        $this->info("✅ Se actualizaron {$summary['updated']} registros exitosamente");
                    } else {
                        DB::rollBack();
                        $this->warn('❌ Operación cancelada, no se guardaron cambios');
                    }
                } else {
                    DB::rollBack();
                    $this->warn('⚠️  No hubo registros para actualizar');
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la importación: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
