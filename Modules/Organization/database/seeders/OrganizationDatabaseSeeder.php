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
        $this->command->info('🌱 Iniciando seeds del módulo Organization...');;

        // 3. Crear estructura organizacional de ejemplo
        $this->call(OrganizationalStructureSeeder::class);

        $this->command->info('✅ Seeds del módulo Organization completados exitosamente');
    }
}
