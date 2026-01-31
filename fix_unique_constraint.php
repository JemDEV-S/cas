<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Paso 1: Eliminando clave foránea...\n";
    DB::statement('ALTER TABLE `eligibility_overrides` DROP FOREIGN KEY `eligibility_overrides_application_id_foreign`');
    echo "✓ Clave foránea eliminada\n\n";

    echo "Paso 2: Eliminando índice único...\n";
    DB::statement('ALTER TABLE `eligibility_overrides` DROP INDEX `eligibility_overrides_application_id_unique`');
    echo "✓ Índice único eliminado\n\n";

    echo "Paso 3: Recreando clave foránea...\n";
    DB::statement('ALTER TABLE `eligibility_overrides` ADD CONSTRAINT `eligibility_overrides_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE');
    echo "✓ Clave foránea recreada\n\n";

    echo "Paso 4: Agregando índice normal...\n";
    DB::statement('ALTER TABLE `eligibility_overrides` ADD INDEX `eligibility_overrides_application_id_index` (`application_id`)');
    echo "✓ Índice normal agregado\n\n";

    echo "✅ Migración completada exitosamente!\n";
    echo "Ahora se pueden crear múltiples reclamos para la misma postulación.\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
