<?php

namespace Modules\Configuration\Console\Commands;

use Illuminate\Console\Command;
use Modules\Configuration\Entities\ConfigHistory;
use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Entities\ConfigGroup;

class CasFreshConfigCommand extends Command
{
    protected $signature = 'cas:fresh-config';

    protected $description = '🧹 Limpia y reseedea solo el módulo Configuration';

    public function handle(): int
    {
        $this->info('🧹 Limpiando módulo Configuration...');

        // Orden correcto por claves foráneas
        ConfigHistory::query()->delete();
        SystemConfig::query()->delete();
        ConfigGroup::query()->delete();

        $this->info('✅ Tablas limpias. Reseedeando...');

        // Ejecutar el seeder del módulo
        $this->call('db:seed', [
            '--class' => 'Modules\\Configuration\\Database\\Seeders\\ConfigurationDatabaseSeeder'
        ]);

        $this->info('✅ Configuration refrescado exitosamente.');
        return 0;
    }
}
