<?php

namespace Modules\Organization\Database\Seeders;

use Illuminate\Database\Seeder;

class OrganizationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeds del módulo Organization...');

        // 1. Crear permisos
        $this->call(OrganizationPermissionsSeeder::class);

        // 2. Asignar permisos a roles
        $this->call(OrganizationRolePermissionsSeeder::class);

        // 3. Crear estructura organizacional de ejemplo
        $this->call(OrganizationalStructureSeeder::class);

        $this->command->info('✅ Seeds del módulo Organization completados exitosamente');
    }
}
