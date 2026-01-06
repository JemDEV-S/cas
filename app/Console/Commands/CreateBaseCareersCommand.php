<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Application\Database\Seeders\AcademicCareersSeeder;

class CreateBaseCareersCommand extends Command
{
    protected $signature = 'catalog:create-base-careers
                            {--seed=default : Tipo de seed a usar}';

    protected $description = 'Crea el catálogo base de 45 carreras profesionales curadas';

    public function handle()
    {
        $this->info('🎓 Creando catálogo base de carreras académicas...');
        $this->newLine();

        if ($this->confirm('¿Desea ejecutar el seeder de carreras?', true)) {
            $seeder = new AcademicCareersSeeder();
            $seeder->setCommand($this);
            $seeder->run();

            $this->newLine();
            $this->info('✅ Catálogo de carreras creado exitosamente');
        } else {
            $this->warn('Operación cancelada');
        }

        return 0;
    }
}
