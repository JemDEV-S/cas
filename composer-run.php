<?php
// composer-run.php
echo "<pre>";
echo "Ejecutando composer dump-autoload...\n\n";

// Cambiar al directorio del proyecto
chdir(__DIR__);

// Ejecutar composer dump-autoload
$output = shell_exec('composer2 dump-autoload 2>&1');
echo $output;

echo "\n\nProceso completado.";
echo "</pre>";

// Opcional: Auto-eliminar este archivo después de ejecutarlo
// unlink(__FILE__);
?>