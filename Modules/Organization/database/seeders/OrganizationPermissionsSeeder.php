<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Lista de permisos
        $permissions = [
            'organization.view.units',
            'organization.view.unit',
            'organization.view.tree',
            'organization.create.unit',
            'organization.update.unit',
            'organization.move.unit',
            'organization.delete.unit',
            'organization.restore.unit',
            'organization.force-delete.unit',
            'organization.manage.hierarchy',
            'organization.export.data',
        ];

        $created = 0;
        $skipped = 0;

        foreach ($permissions as $permissionName) {
            // Verificar si ya existe
            $exists = DB::table('permissions')
                ->where('name', $permissionName)
                ->exists();

            if (!$exists) {
                // Generar slug desde el name
                $slug = Str::slug($permissionName);

                // Insertar con TODOS los campos requeridos
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $permissionName,
                    'slug' => $slug,
                    'module' => 'organization', // ⭐ Agregar module
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
                $this->command->line("  ✓ Creado: {$permissionName}");
            } else {
                $skipped++;
                $this->command->line("  - Ya existe: {$permissionName}");
            }
        }

        $this->command->info("✓ Permisos procesados: {$created} creados, {$skipped} ya existían");
    }
}
