<?php
// Script para debug de sesión
// Ejecutar inmediatamente después de intentar acceder a /configuration

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG DE SESIÓN Y AUTENTICACIÓN ===" . PHP_EOL . PHP_EOL;

// Check auth
echo "1. Estado de autenticación:" . PHP_EOL;
echo "   - Usuario autenticado: " . (auth()->check() ? 'SI' : 'NO') . PHP_EOL;

if (auth()->check()) {
    $user = auth()->user();
    echo "   - Email: " . $user->email . PHP_EOL;
    echo "   - ID: " . $user->id . PHP_EOL;
    echo "   - Roles: " . $user->roles->pluck('name')->implode(', ') . PHP_EOL;
    echo "   - Tiene permiso 'configuration.view.configs': " . ($user->hasPermission('configuration.view.configs') ? 'SI' : 'NO') . PHP_EOL;
} else {
    echo "   ⚠ No hay usuario autenticado en esta sesión CLI" . PHP_EOL;
}

echo PHP_EOL . "2. Configuración de autenticación:" . PHP_EOL;
echo "   - Guard predeterminado: " . config('auth.defaults.guard') . PHP_EOL;
echo "   - Provider: " . config('auth.guards.web.provider') . PHP_EOL;
echo "   - Modelo de usuario: " . config('auth.providers.users.model') . PHP_EOL;

echo PHP_EOL . "3. Verificar últimas sesiones en BD:" . PHP_EOL;
$sessions = DB::table('sessions')->orderBy('last_activity', 'desc')->limit(5)->get(['id', 'user_id', 'last_activity']);
foreach ($sessions as $session) {
    $userId = $session->user_id;
    $userEmail = $userId ? \Modules\User\Entities\User::find($userId)?->email : 'Guest';
    echo "   - Sesión: " . substr($session->id, 0, 10) . "... | Usuario: " . $userEmail . " | Última actividad: " . date('Y-m-d H:i:s', $session->last_activity) . PHP_EOL;
}

echo PHP_EOL . "4. Verificar que la política esté registrada:" . PHP_EOL;
$gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);
try {
    // Esto debería devolver la política registrada
    $policies = $gate->policies();
    $configPolicy = $policies[\Modules\Configuration\Entities\SystemConfig::class] ?? null;
    echo "   - Política de SystemConfig: " . ($configPolicy ?? 'NO REGISTRADA') . PHP_EOL;
} catch (\Exception $e) {
    echo "   - Error al verificar políticas: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== FIN DEL DEBUG ===" . PHP_EOL;
