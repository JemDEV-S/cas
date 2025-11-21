# Configuration Module

## Descripción

El módulo **Configuration** proporciona un sistema centralizado de configuración del sistema CAS. Permite gestionar todas las configuraciones de manera dinámica, con validación, historial de cambios y caché automático.

## Características Principales

- Configuración centralizada con tipos de datos tipados
- Validación automática de valores
- Historial completo de cambios con auditoría
- Sistema de caché integrado para rendimiento
- Organización por grupos temáticos
- Valores por defecto configurables
- Permisos granulares por configuración
- Soporte para configuraciones públicas y del sistema
- Facade para acceso simplificado

## Entidades

### ConfigGroup
Agrupa configuraciones relacionadas por categoría.

**Campos principales:**
- `code`: Código único del grupo (ej: "general", "security")
- `name`: Nombre descriptivo
- `description`: Descripción del grupo
- `icon`: Icono para UI
- `order`: Orden de visualización
- `is_active`: Estado del grupo

### SystemConfig
Configuración individual del sistema.

**Campos principales:**
- `config_group_id`: Grupo al que pertenece
- `key`: Clave única de configuración (ej: "SYSTEM_NAME")
- `value`: Valor actual
- `value_type`: Tipo de dato (string, integer, boolean, json, date, etc.)
- `default_value`: Valor por defecto

**Validación:**
- `validation_rules`: Reglas de validación Laravel
- `options`: Opciones si es select/enum
- `min_value` / `max_value`: Rangos para valores numéricos

**UI:**
- `display_name`: Nombre para mostrar
- `help_text`: Texto de ayuda
- `display_order`: Orden de visualización
- `input_type`: Tipo de input (text, number, boolean, select, etc.)

**Permisos:**
- `is_public`: Visible sin autenticación
- `required_permission`: Permiso necesario para editar
- `is_editable`: Puede modificarse desde UI
- `is_system`: Configuración crítica del sistema

### ConfigHistory
Historial de cambios de configuración.

**Campos:**
- `system_config_id`: Configuración modificada
- `old_value` / `new_value`: Valores antes/después
- `changed_by`: Usuario que realizó el cambio
- `changed_at`: Fecha del cambio
- `change_reason`: Razón del cambio
- `ip_address`: IP desde donde se realizó

## Enums

### ValueTypeEnum
Tipos de datos soportados:
- `STRING`: Cadena de texto
- `INTEGER`: Número entero
- `DECIMAL`: Número decimal
- `BOOLEAN`: Verdadero/Falso
- `JSON`: Objeto JSON
- `DATE`: Fecha
- `DATETIME`: Fecha y hora
- `TEXT`: Texto largo
- `FILE`: Archivo

### InputTypeEnum
Tipos de input para UI:
- `TEXT`: Campo de texto
- `NUMBER`: Campo numérico
- `BOOLEAN`: Checkbox
- `SELECT`: Lista de selección
- `TEXTAREA`: Área de texto
- `DATE`: Selector de fecha
- `FILE`: Carga de archivo
- `COLOR`: Selector de color

## Grupos de Configuración Predefinidos

### 1. General (general)
Configuración general del sistema:
- `SYSTEM_NAME`: Nombre de la institución
- `SYSTEM_LOGO`: Logo del sistema
- `SYSTEM_PRIMARY_COLOR`: Color primario
- `SYSTEM_TIMEZONE`: Zona horaria
- `SYSTEM_LOCALE`: Idioma
- `CONTACT_EMAIL`: Email de contacto
- `CONTACT_PHONE`: Teléfono

### 2. Proceso (process)
Configuración de procesos de convocatoria:
- `DEFAULT_APPLICATION_DEADLINE_DAYS`: Días de plazo para postulaciones (15)
- `DEFAULT_AMENDMENT_DEADLINE_DAYS`: Días para subsanar (3)
- `DEFAULT_APPEAL_DEADLINE_DAYS`: Días para recursos (3)
- `MAX_APPLICATIONS_PER_USER`: Límite de postulaciones (5)
- `AUTO_GENERATE_JOB_CODE`: Generación automática de códigos
- `JOB_CODE_PREFIX`: Prefijo de códigos (CONV)

### 3. Documentos (documents)
Configuración de gestión documental:
- `MAX_FILE_SIZE_MB`: Tamaño máximo de archivo (10 MB)
- `ALLOWED_DOCUMENT_TYPES`: Tipos permitidos (["pdf","docx","jpg","png"])
- `DOCUMENT_STORAGE_DRIVER`: Driver de storage (local, s3, azure)
- `DOCUMENT_RETENTION_YEARS`: Años de retención (7)
- `REQUIRE_DIGITAL_SIGNATURE`: Requerir firma digital

### 4. Notificaciones (notifications)
Configuración de notificaciones:
- `NOTIFICATIONS_ENABLED`: Activar notificaciones
- `EMAIL_FROM_ADDRESS`: Email remitente
- `EMAIL_FROM_NAME`: Nombre remitente

### 5. Seguridad (security)
Configuración de seguridad:
- `SESSION_LIFETIME`: Duración de sesión (120 min)
- `PASSWORD_MIN_LENGTH`: Longitud mínima de contraseña (8)
- `PASSWORD_REQUIRE_UPPERCASE`: Requerir mayúsculas
- `PASSWORD_REQUIRE_NUMBERS`: Requerir números
- `TWO_FACTOR_ENABLED`: 2FA habilitado
- `MAX_LOGIN_ATTEMPTS`: Intentos máximos de login (5)
- `LOCKOUT_DURATION_MINUTES`: Duración de bloqueo (15 min)

### 6. Integraciones (integrations)
Configuración de integraciones externas:
- `RENIEC_API_ENABLED`: Integración RENIEC
- `SUNAT_API_ENABLED`: Integración SUNAT

### 7. Reportes (reports)
Configuración de reportes:
- `DEFAULT_REPORT_FORMAT`: Formato por defecto (pdf, excel, csv)
- `CACHE_REPORTS`: Cachear reportes

### 8. Auditoría (audit)
Configuración de auditoría:
- `AUDIT_ENABLED`: Auditoría habilitada
- `AUDIT_RETENTION_DAYS`: Días de retención (2555 = 7 años)
- `LOG_FAILED_LOGINS`: Registrar logins fallidos

## Servicios

### ConfigService

**Métodos principales:**

#### `get(string $key, $default = null): mixed`
Obtiene el valor de una configuración.

```php
use Modules\Configuration\Facades\Config;

$systemName = Config::get('SYSTEM_NAME');
$maxSize = Config::get('MAX_FILE_SIZE_MB', 10); // con valor por defecto
```

#### `set(string $key, $value, ?string $changedBy = null, ?string $reason = null): SystemConfig`
Establece el valor de una configuración.

```php
Config::set('SYSTEM_NAME', 'Nueva Municipalidad', auth()->id(), 'Cambio de razón social');
```

#### `has(string $key): bool`
Verifica si existe una configuración.

```php
if (Config::has('SOME_KEY')) {
    // ...
}
```

#### `group(string $groupCode): array`
Obtiene todas las configuraciones de un grupo.

```php
$securityConfig = Config::group('security');
// ['SESSION_LIFETIME' => 120, 'PASSWORD_MIN_LENGTH' => 8, ...]
```

#### `all(): array`
Obtiene todas las configuraciones.

```php
$allConfig = Config::all();
```

#### `updateBatch(array $configs, ?string $changedBy = null, ?string $reason = null): array`
Actualiza múltiples configuraciones a la vez.

```php
Config::updateBatch([
    'SYSTEM_NAME' => 'Nuevo Nombre',
    'CONTACT_EMAIL' => 'nuevo@email.com',
], auth()->id());
```

#### `reset(string $key, ?string $changedBy = null, ?string $reason = null): SystemConfig`
Restablece una configuración a su valor por defecto.

```php
Config::reset('MAX_FILE_SIZE_MB', auth()->id());
```

#### `clearCache(?string $key = null): void`
Limpia la caché de configuración.

```php
Config::clearCache(); // Limpia toda la caché
Config::clearCache('SYSTEM_NAME'); // Limpia solo una clave
```

#### `getHistory(string $key, int $limit = 50): Collection`
Obtiene el historial de cambios de una configuración.

```php
$history = Config::getHistory('SYSTEM_NAME', 20);
```

## Repositorio

### ConfigRepository

```php
// Buscar por clave
$config = $repository->findByKey('SYSTEM_NAME');

// Obtener por grupo
$configs = $repository->getByGroup('security');

// Obtener configuraciones públicas
$publicConfigs = $repository->getPublicConfigs();

// Obtener configuraciones editables
$editableConfigs = $repository->getEditableConfigs();

// Obtener todas agrupadas
$grouped = $repository->getAllGrouped();

// Verificar existencia
$exists = $repository->keyExists('SOME_KEY');

// Obtener historial
$history = $repository->getHistory($configId, 50);
```

## Eventos

### ConfigUpdated
Se dispara cuando se actualiza una configuración.

**Propiedades:**
- `config`: La configuración actualizada
- `changedBy`: Usuario que realizó el cambio

### ConfigCacheCleared
Se dispara cuando se limpia la caché de configuración.

### CriticalConfigChanged
Se dispara cuando se modifica una configuración crítica del sistema (`is_system = true`).

**Propiedades:**
- `config`: La configuración crítica modificada
- `changedBy`: Usuario que realizó el cambio

## Uso del Facade

```php
use Modules\Configuration\Facades\Config;

// Obtener valores
$name = Config::get('SYSTEM_NAME');
$maxAttempts = Config::get('MAX_LOGIN_ATTEMPTS', 5);

// Establecer valores
Config::set('CONTACT_EMAIL', 'nuevo@email.com', auth()->id());

// Obtener grupo completo
$generalSettings = Config::group('general');

// Verificar existencia
if (Config::has('TWO_FACTOR_ENABLED')) {
    if (Config::get('TWO_FACTOR_ENABLED')) {
        // Habilitar 2FA
    }
}

// Resetear a valor por defecto
Config::reset('MAX_FILE_SIZE_MB');

// Limpiar caché
Config::clearCache();
```

## Uso en Servicios

```php
use Modules\Configuration\Services\ConfigService;

class SomeService
{
    public function __construct(
        private ConfigService $configService
    ) {}

    public function someMethod()
    {
        $maxSize = $this->configService->get('MAX_FILE_SIZE_MB');

        // Usar el valor...
    }
}
```

## Sistema de Caché

El módulo implementa un sistema de caché automático:

- **TTL por defecto**: 1 hora
- **Caché por clave**: Cada configuración se cachea individualmente
- **Invalidación automática**: Se limpia al actualizar valores
- **Caché de grupo**: También se cachea `all()` para rendimiento

```php
// Primera llamada: consulta BD y cachea
$name = Config::get('SYSTEM_NAME');

// Siguientes llamadas: retorna desde caché
$name = Config::get('SYSTEM_NAME');

// Al actualizar, se invalida la caché
Config::set('SYSTEM_NAME', 'Nuevo Nombre');

// Próxima llamada: consulta BD y cachea nuevo valor
$name = Config::get('SYSTEM_NAME');
```

## Validación de Valores

Las configuraciones pueden tener reglas de validación:

```php
// Ejemplo de configuración con validación
[
    'key' => 'CONTACT_EMAIL',
    'validation_rules' => ['email'],
    // ...
]

// Al intentar establecer un valor inválido:
Config::set('CONTACT_EMAIL', 'not-an-email'); // Lanzará ValidationException
```

Validaciones soportadas:
- Reglas de validación Laravel (en `validation_rules`)
- Rangos numéricos (min_value, max_value)
- Opciones predefinidas (options array)

## Historial de Cambios

Cada modificación se registra automáticamente:

```php
$history = Config::getHistory('SYSTEM_NAME');

foreach ($history as $change) {
    echo "Cambiado por: " . $change->changedBy->name;
    echo "De: " . $change->old_value;
    echo "A: " . $change->new_value;
    echo "Razón: " . $change->change_reason;
    echo "IP: " . $change->ip_address;
}
```

## Seeders

Para poblar las configuraciones iniciales:

```bash
php artisan module:seed Configuration
```

Esto creará:
- 8 grupos de configuración
- Más de 30 configuraciones predefinidas con valores por defecto

## Migraciones

Ejecutar migraciones:

```bash
php artisan migrate
```

Las migraciones crean:
1. Tabla `config_groups`: Grupos de configuración
2. Tabla `system_configs`: Configuraciones individuales
3. Tabla `config_history`: Historial de cambios

## Uso en Controladores

```php
use Modules\Configuration\Services\ConfigService;

class SettingsController extends Controller
{
    public function __construct(
        private ConfigService $configService
    ) {}

    public function index()
    {
        $groups = $this->configService->getAllGrouped();
        return view('settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $this->configService->updateBatch(
            $request->input('configs'),
            auth()->id(),
            'Actualización masiva de configuración'
        );

        return redirect()->back()->with('success', 'Configuración actualizada');
    }

    public function reset(string $key)
    {
        $this->configService->reset($key, auth()->id(), 'Reset manual');
        return redirect()->back()->with('success', 'Configuración restablecida');
    }
}
```

## Mejores Prácticas

1. **Usar el Facade**: Preferir `Config::get()` sobre acceso directo al servicio
2. **Valores por defecto**: Siempre proporcionar un valor por defecto en `get()`
3. **Razones de cambio**: Documentar por qué se cambia una configuración
4. **Configuraciones críticas**: Marcar como `is_system = true` las configuraciones sensibles
5. **Caché**: Limpiar la caché después de cambios masivos
6. **Validación**: Definir reglas de validación para prevenir valores inválidos
7. **Permisos**: Usar `required_permission` para configuraciones sensibles

## Seguridad

- Las configuraciones del sistema (`is_system = true`) disparan eventos especiales
- El historial incluye IP y usuario para auditoría
- Los valores sensibles pueden encriptarse en la capa de aplicación
- Soporte para permisos granulares por configuración

## Próximas Mejoras

- Encriptación automática de valores sensibles
- Import/Export de configuraciones
- Validación de dependencias entre configuraciones
- UI administrativa integrada
- Soporte para configuraciones por entorno
- Versionado de configuraciones
