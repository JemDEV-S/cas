# User Module

Módulo de gestión de usuarios del sistema CAS-MDSJ.

## 📦 Componentes

### Entidades
- **User**: Usuario del sistema con autenticación
- **UserProfile**: Perfil extendido del usuario
- **UserPreference**: Preferencias personales
- **UserOrganizationUnit**: Asignaciones organizacionales

### Enums
- **GenderEnum**: Géneros (Masculino, Femenino, Otro, Prefiero no decir)
- **LanguageEnum**: Idiomas (Español, English)
- **ThemeEnum**: Temas visuales (Claro, Oscuro, Sistema)

### Services
- **UserService**: CRUD de usuarios, activación/desactivación, asignación de roles
- **ProfileService**: Gestión de perfiles de usuario
- **PreferenceService**: Gestión de preferencias personales

### Repositories
- **UserRepository**: Operaciones de base de datos para usuarios

### Events
- **UserCreated**: Usuario creado
- **UserUpdated**: Usuario actualizado
- **UserDeleted**: Usuario eliminado
- **UserActivated**: Usuario activado
- **UserDeactivated**: Usuario desactivado

## 🚀 Uso

### Crear un usuario
```php
$userService = app(\Modules\User\Services\UserService::class);

$user = $userService->create([
    'dni' => '12345678',
    'email' => 'usuario@ejemplo.com',
    'password' => 'password123',
    'first_name' => 'Juan',
    'last_name' => 'Pérez',
    'phone' => '999999999',
]);
```

### Actualizar perfil
```php
$profileService = app(\Modules\User\Services\ProfileService::class);

$profile = $profileService->updateProfile($userId, [
    'birth_date' => '1990-01-01',
    'gender' => 'male',
    'address' => 'Av. Principal 123',
    'district' => 'Lima',
    'province' => 'Lima',
    'department' => 'Lima',
]);
```

### Asignar roles a un usuario
```php
$userService->assignRoles($userId, [$roleId1, $roleId2]);
```

### Verificar roles
```php
if ($user->hasRole('admin-rrhh')) {
    // Usuario tiene rol de Admin RRHH
}

if ($user->hasAnyRole(['admin-rrhh', 'super-admin'])) {
    // Usuario tiene al menos uno de estos roles
}
```

### Gestionar preferencias
```php
$preferenceService = app(\Modules\User\Services\PreferenceService::class);

// Actualizar preferencias
$preferenceService->updatePreferences($userId, [
    'language' => 'es',
    'theme' => 'dark',
    'notifications_email' => true,
]);

// Obtener una preferencia específica
$theme = $preferenceService->getPreference($userId, 'custom.theme', 'light');

// Establecer una preferencia personalizada
$preferenceService->setPreference($userId, 'custom.sidebar_collapsed', true);
```

## 🗄️ Migraciones

Para ejecutar las migraciones:

```bash
php artisan migrate
```

Esto creará las siguientes tablas:
- `users`
- `user_profiles`
- `user_preferences`
- `user_organization_units`
- `user_role` (relación muchos a muchos con roles)

## 📝 Relaciones

- User → hasOne → UserProfile
- User → hasOne → UserPreference
- User → hasMany → UserOrganizationUnit
- User → belongsToMany → Role

## 📝 Licencia

Este módulo es parte del sistema CAS-MDSJ
