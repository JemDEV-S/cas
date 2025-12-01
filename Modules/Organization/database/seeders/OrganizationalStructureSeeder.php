<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Organization\Entities\OrganizationalUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationalStructureSeeder extends Seeder
{
    protected array $map = [];

    public function run(): void
    {
        $this->command->info('🌱 Cargando organigrama desde JSON...');

        // Limpiar tablas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('organizational_unit_closure')->truncate();
        DB::table('organizational_units')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cargar JSON
        $json = file_get_contents(__DIR__ . '/json/organigrama_json.json');
        $data = json_decode($json, true);

        // Crear unidades
        $this->createUnits($data);

        // Reconstruir closure
        $this->rebuildClosureTable();

        $this->command->info('✅ Organigrama cargado exactamente desde JSON.');
    }

    protected function createUnits(array $node, ?string $parentId = null, int $level = 0): string
    {
        $code = $node['codigo'] ?? '00000';

        // Si ya existe, saltamos
        if (OrganizationalUnit::where('code', $code)->exists()) {
            $this->command->warn("⚠ Código duplicado saltado: {$code}");
            return null;
        }

        $id = (string) Str::uuid();

        $unit = OrganizationalUnit::create([
            'id' => $id,
            'code' => $code,
            'name' => $node['dependencia'] ?? $node['organizacion'],
            'description' => 'Unidad orgánica del municipio',
            'type' => $this->getTypeFromLevel($level),
            'parent_id' => $parentId,
            'level' => $level,
            'path' => '', // se actualizará después
            'order' => 0,
            'is_active' => true,
            'metadata' => [
                'responsable' => $node['responsable'] ?? null,
                'local' => $node['local'] ?? null,
                'padre' => $node['padre'] ?? null,
            ],
        ]);

        $this->map[$code] = $unit;

        // Procesar hijos
        $children = $node['hijos'] ?? $node['sub_unidades'] ?? [];
        foreach ($children as $child) {
            $this->createUnits($child, $id, $level + 1);
        }

        return $id;
    }

    protected function getTypeFromLevel(int $level): string
    {
        return match ($level) {
            0, 1 => 'organo',
            2 => 'area',
            default => 'sub_unidad',
        };
    }

    protected function rebuildClosureTable(): void
    {
        DB::table('organizational_unit_closure')->truncate();

        $units = OrganizationalUnit::all();

        foreach ($units as $unit) {
            DB::table('organizational_unit_closure')->insert([
                'ancestor_id' => $unit->id,
                'descendant_id' => $unit->id,
                'depth' => 0,
            ]);

            if ($unit->parent_id) {
                $ancestors = DB::table('organizational_unit_closure')
                    ->where('descendant_id', $unit->parent_id)
                    ->get();

                foreach ($ancestors as $ancestor) {
                    DB::table('organizational_unit_closure')->insert([
                        'ancestor_id' => $ancestor->ancestor_id,
                        'descendant_id' => $unit->id,
                        'depth' => $ancestor->depth + 1,
                    ]);
                }
            }
        }

        // Actualizar paths
        foreach ($units as $unit) {
            $ancestors = $unit->ancestors()->pluck('code')->push($unit->code);
            $unit->update(['path' => $ancestors->join('/')]);
        }
    }
}
