<?php
// composer-run.php
echo "<pre>";
echo "Ejecutando composer dump-autoload...\n\n";

chdir(__DIR__);

// Intentar diferentes rutas de composer
$composerPaths = [
    'composer.phar',
    'composer',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
    'php composer.phar',
    '/usr/local/bin/php composer.phar'
];

$executed = false;

foreach ($composerPaths as $composer) {
    echo "Intentando con: $composer\n";
    $output = shell_exec("$composer dump-autoload 2>&1");
    
    if ($output && strpos($output, 'command not found') === false) {
        echo $output;
        $executed = true;
        break;
    }
}

if (!$executed) {
    echo "No se encontró composer. Intentando solución alternativa...\n\n";
    
    // Si composer.phar existe, usarlo con PHP
    if (file_exists('composer.phar')) {
        $output = shell_exec('php composer.phar dump-autoload 2>&1');
        echo $output;
    } else {
        echo "composer.phar no encontrado. Necesitamos descargarlo o usar solución manual.\n";
    }
}

echo "\n\nProceso completado.";
echo "</pre>";
?>