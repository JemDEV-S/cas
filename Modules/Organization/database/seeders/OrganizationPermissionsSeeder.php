<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($permissions as $name) {
            $exists = DB::table('permissions')->where('name', $name)->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'module' => 'organization',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
                $this->command->line("  ✓ Creado: {$name}");
            } else {
                $skipped++;
                $this->command->line("  - Ya existe: {$name}");
            }
        }

        $this->command->info("✓ Permisos procesados: {$created} creados, {$skipped} ya existían");
    }
}
