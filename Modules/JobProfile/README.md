# JobProfile Module

## Descripción

El módulo **JobProfile** gestiona los perfiles de puestos de trabajo en el sistema CAS. Define las características, requisitos y responsabilidades de cada posición dentro de la organización.

## Características Principales

- Gestión completa de perfiles de puestos
- Definición de requisitos por categorías (educación, experiencia, competencias, etc.)
- Definición de responsabilidades ordenadas
- Workflow de aprobación (draft → pending_review → approved → active)
- Integración con unidades organizacionales
- Rangos salariales por perfil
- Tipos de contrato configurables
- Niveles de puesto
- Condiciones de trabajo específicas

## Entidades

### JobProfile
Representa un perfil de puesto de trabajo.

**Campos principales:**
- `code`: Código único del perfil
- `title`: Título del puesto
- `organizational_unit_id`: Unidad organizacional asociada
- `job_level`: Nivel del puesto
- `contract_type`: Tipo de contrato (CAS, Plazo Fijo, Indefinido, Locación de Servicios)
- `salary_min` / `salary_max`: Rango salarial
- `description`: Descripción general
- `mission`: Misión del puesto
- `working_conditions`: Condiciones de trabajo
- `status`: Estado del perfil (draft, pending_review, approved, rejected, active, inactive)
- Campos de auditoría: `requested_by`, `reviewed_by`, `approved_by`, timestamps

**Relaciones:**
- `organizationalUnit`: Pertenece a una unidad organizacional
- `requirements`: Tiene múltiples requisitos
- `responsibilities`: Tiene múltiples responsabilidades

### JobProfileRequirement
Representa un requisito del perfil (educación, experiencia, competencias, etc.).

**Campos:**
- `category`: Categoría del requisito (educacion, experiencia, competencias, etc.)
- `description`: Descripción del requisito
- `is_mandatory`: Si es obligatorio o deseable
- `order`: Orden de presentación

### JobProfileResponsibility
Representa una responsabilidad del puesto.

**Campos:**
- `description`: Descripción de la responsabilidad
- `order`: Orden de presentación

## Enums

### JobProfileStatusEnum
Estados del workflow del perfil:
- `DRAFT`: Borrador inicial
- `PENDING_REVIEW`: En revisión
- `APPROVED`: Aprobado
- `REJECTED`: Rechazado
- `ACTIVE`: Activo para convocatorias
- `INACTIVE`: Inactivo

### ContractTypeEnum
Tipos de contrato disponibles:
- `CAS`: Contrato Administrativo de Servicios
- `PLAZO_FIJO`: Plazo Fijo
- `INDEFINIDO`: Indefinido
- `LOCACION_SERVICIOS`: Locación de Servicios

## Servicios

### JobProfileService

**Métodos principales:**

#### `create(array $data, array $requirements, array $responsibilities): JobProfile`
Crea un nuevo perfil en estado draft con sus requisitos y responsabilidades.

```php
$profile = $jobProfileService->create([
    'code' => 'PF-001',
    'title' => 'Analista de Recursos Humanos',
    'organizational_unit_id' => '...',
    'job_level' => 'Analista II',
    'contract_type' => ContractTypeEnum::CAS->value,
    'salary_min' => 3000.00,
    'salary_max' => 4000.00,
    'description' => 'Descripción del puesto...',
    'mission' => 'Misión del puesto...',
    'working_conditions' => 'Modalidad híbrida, 40 horas semanales',
], [
    [
        'category' => 'educacion',
        'description' => 'Título profesional en Administración o RRHH',
        'is_mandatory' => true,
    ],
    [
        'category' => 'experiencia',
        'description' => 'Mínimo 2 años en reclutamiento',
        'is_mandatory' => true,
    ],
], [
    [
        'description' => 'Conducir procesos de selección',
    ],
    [
        'description' => 'Elaborar perfiles de puesto',
    ],
]);
```

#### `update(string $id, array $data): JobProfile`
Actualiza un perfil (solo si está en draft).

#### `submitForReview(string $id, string $requestedBy): JobProfile`
Envía un perfil a revisión (solo si está en draft).

```php
$profile = $jobProfileService->submitForReview($profileId, 'user-uuid-123');
```

#### `approve(string $id, string $approvedBy, ?string $comments = null): JobProfile`
Aprueba un perfil (solo si está en pending_review).

```php
$profile = $jobProfileService->approve($profileId, 'approver-uuid-456', 'Aprobado según normativa');
```

#### `reject(string $id, string $reviewedBy, string $reason): JobProfile`
Rechaza un perfil (solo si está en pending_review).

```php
$profile = $jobProfileService->reject($profileId, 'reviewer-uuid-789', 'Falta especificar competencias técnicas');
```

#### `activate(string $id): JobProfile`
Activa un perfil aprobado para ser usado en convocatorias.

```php
$profile = $jobProfileService->activate($profileId);
```

#### `deactivate(string $id): JobProfile`
Desactiva un perfil activo.

```php
$profile = $jobProfileService->deactivate($profileId);
```

## Repositorio

### JobProfileRepository

**Métodos adicionales:**

```php
// Buscar por código
$profile = $repository->findByCode('PF-001');

// Obtener por estado
$drafts = $repository->getByStatus('draft');
$pending = $repository->getByStatus('pending_review');

// Obtener aprobados
$approved = $repository->getApproved();

// Obtener activos
$active = $repository->getActive();

// Obtener por unidad organizacional
$profiles = $repository->getByOrganizationalUnit($unitId);
```

## Eventos

### JobProfileCreated
Se dispara cuando se crea un nuevo perfil.

**Propiedades:**
- `jobProfile`: El perfil creado

### JobProfileApproved
Se dispara cuando se aprueba un perfil.

**Propiedades:**
- `jobProfile`: El perfil aprobado
- `approvedBy`: ID del usuario que aprobó

## Workflow de Estados

```
DRAFT
  └─> submitForReview() → PENDING_REVIEW
                              ├─> approve() → APPROVED
                              │                  └─> activate() → ACTIVE
                              │                                      └─> deactivate() → INACTIVE
                              └─> reject() → REJECTED
```

## Reglas de Negocio

1. Solo los perfiles en **draft** pueden ser modificados
2. Solo los perfiles en **draft** pueden enviarse a revisión
3. Solo los perfiles en **pending_review** pueden ser aprobados o rechazados
4. Solo los perfiles **approved** pueden ser activados
5. Solo los perfiles **active** pueden ser desactivados
6. Los perfiles **approved** y **active** no pueden modificarse

## Validaciones

- El código del perfil debe ser único
- Los rangos salariales deben ser válidos (min <= max)
- Debe existir la unidad organizacional asociada
- El tipo de contrato debe ser válido según el enum
- Los requisitos deben tener categoría y descripción
- Las responsabilidades deben tener descripción

## Integración con otros Módulos

- **Organization**: Relaciona perfiles con unidades organizacionales
- **User**: Registra quién solicitó, revisó y aprobó cada perfil
- **JobPosting** (próximo): Los perfiles activos serán base para crear convocatorias

## Uso en Controladores

```php
use Modules\JobProfile\Services\JobProfileService;
use Modules\JobProfile\Enums\ContractTypeEnum;

class JobProfileController extends Controller
{
    public function __construct(
        private JobProfileService $jobProfileService
    ) {}

    public function store(Request $request)
    {
        $profile = $this->jobProfileService->create(
            $request->only(['code', 'title', 'organizational_unit_id', ...]),
            $request->input('requirements', []),
            $request->input('responsibilities', [])
        );

        return response()->json($profile, 201);
    }

    public function submitForReview(string $id)
    {
        try {
            $profile = $this->jobProfileService->submitForReview($id, auth()->id());
            return response()->json($profile);
        } catch (BusinessRuleException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function approve(string $id, Request $request)
    {
        $profile = $this->jobProfileService->approve(
            $id,
            auth()->id(),
            $request->input('comments')
        );

        return response()->json($profile);
    }
}
```

## Migraciones

Ejecutar migraciones:

```bash
php artisan migrate
```

Las migraciones crean:
1. Tabla `job_profiles` con todos los campos del perfil
2. Tabla `job_profile_requirements` con los requisitos
3. Tabla `job_profile_responsibilities` con las responsabilidades

## Testing

Ejemplo de test básico:

```php
public function test_create_job_profile_with_requirements_and_responsibilities()
{
    $service = app(JobProfileService::class);

    $profile = $service->create([
        'code' => 'TEST-001',
        'title' => 'Test Profile',
        'organizational_unit_id' => $this->unit->id,
        'job_level' => 'Test Level',
        'contract_type' => ContractTypeEnum::CAS->value,
        'salary_min' => 1000,
        'salary_max' => 2000,
    ], [
        ['category' => 'educacion', 'description' => 'Test requirement', 'is_mandatory' => true],
    ], [
        ['description' => 'Test responsibility'],
    ]);

    $this->assertEquals('draft', $profile->status);
    $this->assertCount(1, $profile->requirements);
    $this->assertCount(1, $profile->responsibilities);
}
```

## Próximas Mejoras

- Versionado de perfiles
- Comparación entre versiones
- Templates de perfiles
- Duplicación de perfiles existentes
- Historial de cambios
- Notificaciones automáticas en cambios de estado
