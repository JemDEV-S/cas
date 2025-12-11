<?php
// clear-cache.php
echo "<pre>";
chdir(__DIR__);

$cachePaths = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/routes-v7.php',
];

echo "Limpiando archivos de cache...\n\n";

foreach ($cachePaths as $path) {
    if (file_exists($path)) {
        unlink($path);
        echo "✓ Eliminado: $path\n";
    } else {
        echo "- No existe: $path\n";
    }
}

echo "\n✓ Cache limpiado exitosamente!\n";
echo "Intenta recargar tu aplicación ahora.\n";
echo "</pre>";
?>