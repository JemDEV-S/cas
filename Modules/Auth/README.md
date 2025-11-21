# Auth Module

Módulo de autenticación y autorización del sistema CAS-MDSJ.

## 📦 Componentes

### Entidades
- **Role**: Gestión de roles del sistema
- **Permission**: Permisos granulares
- **UserSession**: Sesiones activas de usuarios
- **LoginAttempt**: Registro de intentos de login
- **PasswordReset**: Tokens de recuperación de contraseña

### Roles Predefinidos
- **SUPER_ADMIN**: Control total del sistema
- **ADMIN_RRHH**: Gestión de convocatorias
- **AREA_USER**: Solicita perfiles
- **RRHH_REVIEWER**: Revisa perfiles
- **JURY**: Evalúa postulaciones
- **APPLICANT**: Postula a convocatorias
- **VIEWER**: Solo visualización

### Middleware
- **CheckRole**: Verifica roles de usuario
- **CheckPermission**: Verifica permisos específicos
- **TrackLoginAttempt**: Rastrea intentos de inicio de sesión

### Services
- **AuthService**: Lógica de autenticación y sesiones
- **RoleService**: Gestión de roles
- **PermissionService**: Gestión de permisos

### Policies
- **RolePolicy**: Autorización para roles
- **PermissionPolicy**: Autorización para permisos

### Events
- **UserLoggedIn**: Usuario inició sesión
- **UserLoggedOut**: Usuario cerró sesión
- **LoginFailed**: Intento fallido de login
- **RoleAssigned**: Rol asignado a usuario

## 🚀 Uso

### Proteger rutas con roles
```php
Route::middleware(['auth', 'role:admin-rrhh,super-admin'])->group(function () {
    // Rutas solo para admins
});
```

### Proteger rutas con permisos
```php
Route::middleware(['auth', 'permission:jobposting.create.convocatoria'])->group(function () {
    // Rutas solo con permiso específico
});
```

### Usar el AuthService
```php
$authService = app(\Modules\Auth\Services\AuthService::class);

$result = $authService->login(
    email: 'usuario@ejemplo.com',
    password: 'password123',
    ip: $request->ip(),
    userAgent: $request->userAgent()
);

// $result contiene: ['user' => ..., 'token' => ..., 'expires_at' => ...]
```

### Verificar permisos en el código
```php
if ($user->roles()->first()->hasPermission('auth.create.role')) {
    // Usuario tiene permiso
}
```

## 🗄️ Migraciones y Seeders

Para ejecutar las migraciones y seeders:

```bash
php artisan migrate
php artisan module:seed Auth
```

Esto creará las tablas necesarias y poblará los roles y permisos predefinidos.

## 📝 Licencia

Este módulo es parte del sistema CAS-MDSJ
