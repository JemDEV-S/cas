# Core Module

Módulo base que proporciona funcionalidades compartidas por todos los módulos del sistema.

## 📦 Componentes

### Entities (Modelos Base)
- `BaseModel`: Modelo abstracto base con funcionalidades comunes
- `BaseSoftDelete`: Modelo base con soft deletes

### Traits
- `HasUuid`: Generación automática de UUID para claves primarias
- `HasStatus`: Gestión de estados en modelos
- `HasMetadata`: Manejo de metadatos JSON
- `Searchable`: Búsqueda full-text
- `Sortable`: Ordenamiento dinámico
- `Filterable`: Filtrado avanzado
- `Exportable`: Exportación a diferentes formatos

### Services
- `BaseService`: Lógica de negocio común
- `FileService`: Manejo de archivos
- `ValidationService`: Validaciones reutilizables
- `EncryptionService`: Encriptación de datos sensibles

### Repositories
- `BaseRepository`: Patrón repositorio base
- `CacheRepository`: Gestión de caché

### Value Objects
- `Email`: Representa un email válido
- `PhoneNumber`: Representa un teléfono peruano válido
- `DNI`: Representa un DNI peruano válido
- `Money`: Representa valores monetarios
- `DateRange`: Representa rangos de fechas

### DTOs
- `PaginationDTO`: Data Transfer Object para paginación
- `FilterDTO`: Data Transfer Object para filtros
- `SortDTO`: Data Transfer Object para ordenamiento

### Helpers
- `StringHelper`: Manipulación de strings
- `DateHelper`: Manejo de fechas
- `ArrayHelper`: Operaciones con arrays
- `NumberHelper`: Formateo de números

### Exceptions
- `CoreException`: Excepción base del módulo
- `ValidationException`: Errores de validación
- `BusinessRuleException`: Violaciones de reglas de negocio
- `UnauthorizedException`: Accesos no autorizados
- `NotFoundException`: Recursos no encontrados

## 🚀 Uso

### Ejemplo: Usar BaseModel con Traits

```php
use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;

class MiModelo extends BaseModel
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = ['nombre', 'descripcion', 'status'];

    protected $searchable = ['nombre', 'descripcion'];
    protected $sortable = ['nombre', 'created_at'];
}
```

### Ejemplo: Usar Value Objects

```php
use Modules\Core\ValueObjects\Email;
use Modules\Core\ValueObjects\DNI;

$email = Email::fromString('usuario@ejemplo.com');
$dni = DNI::fromString('12345678');

echo $email->getDomain(); // ejemplo.com
echo $dni->getFormatted(); // 12.345.678
```

### Ejemplo: Usar Helpers

```php
use Modules\Core\Helpers\StringHelper;
use Modules\Core\Helpers\DateHelper;

$slug = StringHelper::slug('Mi Título');
$fecha = DateHelper::format(now(), 'd/m/Y');
```

### Ejemplo: Usar Services

```php
use Modules\Core\Services\FileService;
use Modules\Core\Services\ValidationService;

$fileService = app(FileService::class);
$resultado = $fileService->store($request->file('archivo'));

$validationService = app(ValidationService::class);
$valido = $validationService->validateDNI('12345678');
```

## 📝 Licencia

Este módulo es parte del sistema CAS-MDSJ
