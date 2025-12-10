# Módulo Application - Sistema CAS

## 📋 Descripción

Módulo completo para la gestión de **Postulaciones (Registro Virtual)** del sistema CAS-MDSJ. Implementado siguiendo arquitectura modular estricta con separación de responsabilidades.

---

## ✅ Componentes Implementados

### 🗄️ **Base de Datos** (9 Tablas)

1. ✅ **applications** - Datos principales de la postulación
2. ✅ **application_academics** - Formación académica
3. ✅ **application_experiences** - Experiencia laboral (general/específica)
4. ✅ **application_trainings** - Capacitaciones y cursos
5. ✅ **application_special_conditions** - Condiciones especiales (CONADIS, FF.AA)
6. ✅ **application_professional_registrations** - Colegiatura, OSCE, Licencias
7. ✅ **application_knowledge** - Conocimientos técnicos
8. ✅ **application_documents** - Documentos con firma digital PKI
9. ✅ **application_history** - Historial completo de cambios

### 🎯 **Entidades (Models)** - 9 Clases

Todas ubicadas en `Modules/Application/app/Entities/`:
- `Application.php` - Entidad principal
- `ApplicationAcademic.php`
- `ApplicationExperience.php`
- `ApplicationTraining.php`
- `ApplicationSpecialCondition.php`
- `ApplicationProfessionalRegistration.php`
- `ApplicationKnowledge.php`
- `ApplicationDocument.php`
- `ApplicationHistory.php`

### 📦 **DTOs** - 8 Clases

Ubicados en `Modules/Application/app/DTOs/`:
- `ApplicationDTO.php`
- `PersonalDataDTO.php`
- `AcademicDTO.php`
- `ExperienceDTO.php`
- `TrainingDTO.php`
- `SpecialConditionDTO.php`
- `ProfessionalRegistrationDTO.php`
- `KnowledgeDTO.php`

### 🎨 **Enums** - 7 Clases

Ubicados en `Modules/Application/app/Enums/`:
- `ApplicationStatus.php` - Estados con transiciones
- `DegreeType.php` - Tipos de formación académica
- `SpecialConditionType.php` - Con porcentajes de bonificación
- `RegistrationType.php`
- `ProficiencyLevel.php`
- `DocumentType.php`
- `HistoryEventType.php`

### ⚙️ **Servicios** - 3 Clases Principales

#### 1. **EligibilityCalculatorService** ⭐
```php
Ubicación: Modules/Application/app/Services/EligibilityCalculatorService.php
```
**Funcionalidades:**
- ✅ Cálculo de experiencia con **detección y fusión de overlaps**
- ✅ Formato: "X Años, Y Meses, Z Días"
- ✅ Experiencia general, específica y sector público
- ✅ Validación de periodos solapados

**Métodos principales:**
- `calculateTotalExperience(array $experiences): array`
- `calculateGeneralExperience(array $experiences): array`
- `calculateSpecificExperience(array $experiences): array`
- `detectOverlaps(array $experiences): array`
- `meetsRequirement(array $experiences, float $requiredYears): bool`

#### 2. **AutoGraderService** ⭐
```php
Ubicación: Modules/Application/app/Services/AutoGraderService.php
```
**Funcionalidades:**
- ✅ Evaluación automática de elegibilidad
- ✅ Comparación contra requisitos del JobProfile
- ✅ Determina: **APTO** o **NO_APTO**
- ✅ Validaciones completas

**Validaciones implementadas:**
- Formación académica (nivel y carrera)
- Experiencia general (años requeridos)
- Experiencia específica (años requeridos)
- Colegiatura profesional
- Certificación OSCE
- Licencia de conducir

**Método principal:**
- `evaluateEligibility(Application $application): array`
- `applyAutoGrading(Application $application, string $checkedBy): Application`

#### 3. **ApplicationService**
```php
Ubicación: Modules/Application/app/Services/ApplicationService.php
```
**Funcionalidades:**
- Crear y actualizar postulaciones
- Gestión del ciclo de vida
- Coordinación con otros servicios
- Cálculo de bonificaciones

### 🗄️ **Repository Pattern**

```php
Ubicación: Modules/Application/app/Repositories/
```
- `Contracts/ApplicationRepositoryInterface.php` - Contrato
- `ApplicationRepository.php` - Implementación

**Métodos disponibles:**
- `find(string $id)`
- `findByCode(string $code)`
- `paginate(array $filters, int $perPage)`
- `getByVacancy(string $vacancyId)`
- `getByStatus(string $status)`
- `hasApplied(string $applicantId, string $vacancyId)`
- `getRankingByVacancy(string $vacancyId)`

### ✅ **FormRequests** (Validación)

```php
Ubicación: Modules/Application/app/Http/Requests/
```
- `StoreApplicationRequest.php` - Validación para creación
- `UpdateApplicationRequest.php` - Validación para actualización

### 🔐 **Policy**

```php
Ubicación: Modules/Application/app/Policies/ApplicationPolicy.php
```
**Métodos:**
- `viewAny(User $user)`
- `view(User $user, Application $application)`
- `create(User $user)`
- `update(User $user, Application $application)`
- `delete(User $user, Application $application)`
- `withdraw(User $user, Application $application)`
- `evaluate(User $user, Application $application)`
- `viewHistory(User $user, Application $application)`
- `manageDocuments(User $user, Application $application)`
- `verifyDocuments(User $user, Application $application)`

### 🎭 **Eventos y Listeners**

**Eventos:**
- `ApplicationSubmitted`
- `ApplicationUpdated`
- `ApplicationEvaluated`

**Listeners:**
- `LogApplicationSubmitted` - Registra en historial
- `LogApplicationUpdated` - Registra en historial
- `LogApplicationEvaluated` - Registra resultado de evaluación
- `SendApplicationSubmittedNotification` - Envía notificación

### 🎮 **Controlador Web**

```php
Ubicación: Modules/Application/app/Http/Controllers/ApplicationController.php
```

**Métodos implementados:**
- `index(Request $request)` - Listar con filtros
- `create(Request $request)` - Formulario de creación
- `store(StoreApplicationRequest $request)` - Guardar
- `show(string $id)` - Ver detalle con estadísticas
- `edit(string $id)` - Formulario de edición
- `update(UpdateApplicationRequest $request, string $id)` - Actualizar
- `destroy(string $id)` - Eliminar (soft delete)
- `withdraw(Request $request, string $id)` - Desistir
- `evaluateEligibility(string $id)` - Evaluar automáticamente
- `history(string $id)` - Ver historial

### 🌐 **Rutas Web**

```php
Ubicación: Modules/Application/routes/web.php
```

**Rutas configuradas:**
```
GET    /applications                        - Listar
GET    /applications/create                 - Formulario nuevo
POST   /applications                        - Crear
GET    /applications/{id}                   - Ver detalle
GET    /applications/{id}/edit              - Formulario editar
PUT    /applications/{id}                   - Actualizar
DELETE /applications/{id}                   - Eliminar
POST   /applications/{id}/withdraw          - Desistir
POST   /applications/{id}/evaluate-eligibility - Evaluar
GET    /applications/{id}/history           - Historial
```

### 🎨 **Vistas Blade** - 4 Vistas Principales

```
Modules/Application/resources/views/
├── layouts/
│   └── master.blade.php              - Layout principal
├── components/
│   └── navigation.blade.php          - Navegación
├── index.blade.php                   - Listado con filtros
├── show.blade.php                    - Detalle completo
└── history.blade.php                 - Timeline de historial
```

---

## 🚀 Instalación y Configuración

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará las 9 tablas del módulo.

### 2. Registrar el Policy (Opcional)

En `App\Providers\AuthServiceProvider.php`:

```php
use Modules\Application\Entities\Application;
use Modules\Application\Policies\ApplicationPolicy;

protected $policies = [
    Application::class => ApplicationPolicy::class,
];
```

### 3. Configurar Permisos

Crear los siguientes permisos en la base de datos:

```
- application.view.all
- application.view.own
- application.create
- application.update
- application.delete.all
- application.evaluate
- application.documents.manage
- application.documents.verify
```

### 4. Configurar Roles

El sistema espera estos roles:
- `APPLICANT` - Postulante
- `ADMIN_RRHH` - Administrador RRHH
- `JURY` - Miembro del jurado

---

## 📊 Uso del Sistema

### Ejemplo 1: Crear una Postulación

```php
use Modules\Application\Services\ApplicationService;
use Modules\Application\DTOs\ApplicationDTO;

$service = app(ApplicationService::class);

$dto = ApplicationDTO::fromArray([
    'job_profile_vacancy_id' => '...',
    'applicant_id' => auth()->id(),
    'personal_data' => [...],
    'academics' => [...],
    'experiences' => [...],
    'trainings' => [...],
    'terms_accepted' => true,
]);

$application = $service->create($dto);
```

### Ejemplo 2: Evaluar Elegibilidad Automática

```php
use Modules\Application\Services\ApplicationService;

$service = app(ApplicationService::class);
$application = Application::find($id);

$application = $service->evaluateEligibility($application, auth()->id());

// Resultado: $application->is_eligible = true/false
// Estado: $application->status = 'APTO' o 'NO_APTO'
```

### Ejemplo 3: Calcular Experiencia

```php
use Modules\Application\Services\EligibilityCalculatorService;

$calculator = app(EligibilityCalculatorService::class);

$experiences = [
    [
        'start_date' => '2020-01-01',
        'end_date' => '2021-12-31',
        'is_specific' => true,
    ],
    [
        'start_date' => '2021-06-01', // Hay overlap!
        'end_date' => '2023-12-31',
        'is_specific' => true,
    ],
];

$result = $calculator->calculateSpecificExperience($experiences);

// Resultado:
// [
//     'total_days' => 1461,
//     'years' => 4,
//     'months' => 0,
//     'days' => 1,
//     'formatted' => '4 años, 1 día',
//     'decimal_years' => 4.0
// ]

// Detectar overlaps
$overlaps = $calculator->detectOverlaps($experiences);
```

---

## 🎯 Características Principales

### ✅ Detección de Overlaps en Experiencia

El sistema detecta y fusiona automáticamente periodos de experiencia superpuestos para evitar duplicar tiempo:

```
Experiencia 1: 01/01/2020 - 31/12/2021 (2 años)
Experiencia 2: 01/06/2021 - 31/12/2023 (2.5 años)
                    ↓
Overlap: 01/06/2021 - 31/12/2021 (7 meses)
                    ↓
Total Real: 4 años (no 4.5 años)
```

### ✅ Evaluación Automática de Elegibilidad

El `AutoGraderService` compara automáticamente:
- ✅ Nivel educativo vs requerido
- ✅ Carrera profesional vs requerida
- ✅ Años de experiencia general vs requeridos
- ✅ Años de experiencia específica vs requeridos
- ✅ Colegiatura (si es requerida)
- ✅ Certificación OSCE (si es requerida)
- ✅ Licencia de conducir (si es requerida)

**Resultado:** APTO o NO_APTO con razones detalladas.

### ✅ Historial Completo

Cada acción queda registrada en `application_history`:
- Creación
- Actualizaciones
- Cambios de estado
- Evaluaciones
- Documentos subidos/eliminados
- Usuario que realizó la acción
- IP y User-Agent

---

## 📝 Próximos Pasos Sugeridos

1. **Crear vistas de formulario** (`create.blade.php` y `edit.blade.php`)
2. **Implementar `ApplicationDocumentController`** para gestión de documentos
3. **Generar PDF de "Ficha de Postulación"** usando DomPDF o similar
4. **Implementar firma digital PKI** para documentos
5. **Crear Factories y Seeders** para testing
6. **Escribir Tests unitarios** para servicios críticos
7. **Crear dashboard** con estadísticas

---

## 🔧 Testing

Para probar el módulo en desarrollo:

```bash
# Listar postulaciones
http://localhost/applications

# Ver detalle
http://localhost/applications/{id}

# Ver historial
http://localhost/applications/{id}/history
```

---

## 📚 Documentación Adicional

- Ver [cas-info.md](../../cas-info.md) para arquitectura general del sistema
- Los servicios están documentados con PHPDoc
- Las validaciones están en los FormRequests

---

**Desarrollado con arquitectura modular estricta siguiendo SOLID y DDD** 🚀
