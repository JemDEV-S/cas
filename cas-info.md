# 🏗️ Arquitectura Modular - Sistema de Convocatorias

## 📋 Índice
1. [Visión General](#visión-general)
2. [Estructura de Módulos](#estructura-de-módulos)
3. [Módulos Core](#módulos-core)
4. [Módulos de Dominio](#módulos-de-dominio)
5. [Módulos de Soporte](#módulos-de-soporte)
6. [Patrones y Convenciones](#patrones-y-convenciones)
7. [Roadmap de Implementación](#roadmap-de-implementación)

---

## 🎯 Visión General

### Stack Tecnológico
- **Framework**: Laravel 11.x
- **Modularización**: nwidart/laravel-modules
- **Base de Datos**: PostgreSQL
- **Caché/Queue**: Redis
- **Permisos**: Spatie Laravel Permission

### Principios Arquitectónicos
- **DDD (Domain-Driven Design)**: Módulos organizados por dominios de negocio
- **SOLID**: Aplicado en servicios y repositorios
- **Event-Driven**: Comunicación desacoplada entre módulos
- **Repository Pattern**: Abstracción de la capa de datos
- **Service Layer**: Lógica de negocio centralizada

---

## 🗂️ Estructura de Módulos

```
Modules/
├── Core/              # Base compartida (traits, helpers, exceptions)
├── Auth/              # Autenticación y autorización (roles, permisos)
├── User/              # Gestión de usuarios y perfiles
├── Organization/      # Estructura organizacional jerárquica
├── JobPosting/        # Convocatorias y cronogramas
├── JobProfile/        # Perfiles de puesto y criterios
├── Application/       # Postulaciones y documentos
├── Evaluation/        # Sistema de evaluación y jurados
├── Document/          # Gestión documental y firma digital PKI
├── Notification/      # Notificaciones multi-canal
├── Reporting/         # Reportes y dashboards
├── Audit/             # Auditoría y trazabilidad
└── Configuration/     # Configuración del sistema
```

### Matriz de Dependencias

| Módulo | Depende de | Usado por |
|--------|-----------|-----------|
| Core | - | Todos |
| Auth | Core, User | Todos |
| User | Core, Auth | Organization, Application |
| Organization | Core, User | JobPosting, JobProfile |
| JobPosting | Core, Organization | Application |
| JobProfile | Core, Organization | Application |
| Application | Core, User, JobPosting, JobProfile | Evaluation |
| Evaluation | Core, Application, User | Reporting |
| Document | Core, User | Application, Evaluation |
| Notification | Core, User | Todos (eventos) |
| Reporting | Core | - |
| Audit | Core | Todos (automático) |
| Configuration | Core | Todos |

---

## 🔷 Módulos Core

### 1. Core Module
**Responsabilidad**: Funcionalidades base compartidas

**Componentes Principales**:
```php
// Traits
HasUuid, HasStatus, HasMetadata, Searchable, Filterable, Exportable

// Base Classes
BaseModel, BaseService, BaseRepository

// Value Objects
Email, PhoneNumber, DNI, Money, DateRange

// Exceptions
CoreException, ValidationException, BusinessRuleException
```

**Estructura**:
```
Core/
├── Entities/BaseModel.php
├── Services/BaseService.php
├── Repositories/BaseRepository.php
├── Traits/
├── ValueObjects/
├── DTOs/
└── Exceptions/
```

---

### 2. Auth Module
**Responsabilidad**: Autenticación, autorización y seguridad

**Entidades**: `Role`, `Permission`, `UserSession`, `LoginAttempt`

**Roles Predefinidos**:
```php
enum UserRole: string {
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_RRHH = 'admin_rrhh';
    case AREA_USER = 'area_user';
    case JURY = 'jury';
    case APPLICANT = 'applicant';
}
```

**Permisos** (formato: `modulo.accion.recurso`):
```
- jobposting.create
- application.view.own
- evaluation.update
- reporting.export
```

**Características**:
- Autenticación con Laravel Sanctum
- 2FA opcional
- Rate limiting por IP
- Bloqueo automático tras intentos fallidos
- Políticas de contraseña configurables

---

## 🔶 Módulos de Dominio

### 3. User Module
**Entidades**: `User`, `UserProfile`, `UserPreference`

```php
// User (tabla principal)
- id: uuid (PK)
- dni: string(8) unique
- email: string unique
- password: hashed
- first_name, last_name: string
- phone, photo_url: string
- is_active: boolean
- last_login_at: timestamp
```

**Relaciones**:
- `hasMany(Application)`: postulaciones del usuario
- `hasMany(Evaluation)`: evaluaciones realizadas (si es jurado)
- `belongsToMany(OrganizationalUnit)`: unidades asignadas

---

### 4. Organization Module
**Entidades**: `OrganizationalUnit` (jerarquía con Closure Table)

```php
// organizational_units
- id: uuid
- code: string unique (ej: "OGM-001")
- name: string
- type: enum (ORGANO, AREA, SUB_UNIDAD)
- parent_id: uuid nullable
- level: integer (auto)
- path: string (ej: "/1/5/12")
- order: integer
- is_active: boolean
```

**Patrón Closure Table**:
```php
// organizational_unit_closure
- ancestor_id: uuid
- descendant_id: uuid  
- depth: integer

// Métodos eficientes
getAncestors(), getDescendants(), getSiblings(), moveUnit()
```

---

### 5. JobPosting Module
**Entidades**: `JobPosting`, `ProcessPhase`, `JobPostingSchedule`

```php
// job_postings
- id, code: uuid, string unique (auto: "CONV-2025-001")
- title, description: string, text
- status: enum (BORRADOR, PUBLICADA, EN_PROCESO, FINALIZADA, CANCELADA)
- year: integer
- published_at, finalized_at: timestamp
- published_by, finalized_by: uuid (FK User)
```

**Fases del Proceso** (12 fases predefinidas):
```
1. APPROVAL - Aprobación
2. PUBLICATION - Publicación
3. REGISTRATION - Registro de postulantes
4. ELIGIBLE_PUBLICATION - Publicación de aptos
5. CV_SUBMISSION - Presentación de CV
6. CV_EVALUATION - Evaluación curricular ⚡
7. CV_RESULTS - Resultados curriculares
8. INTERVIEW - Entrevista personal ⚡
9. INTERVIEW_RESULTS - Resultados entrevista
10. CONTRACT - Suscripción de contrato
11. INDUCTION - Inducción
12. START_WORK - Inicio de labores

⚡ = Requiere evaluación por jurado
```

**State Machine**:
```
BORRADOR → PUBLICADA → EN_PROCESO → FINALIZADA
                ↓          ↓           ↓
            CANCELADA  CANCELADA   CANCELADA
```

**Validaciones de transición**:
- `BORRADOR → PUBLICADA`: cronograma completo, perfiles aprobados
- `PUBLICADA → EN_PROCESO`: fecha inicio alcanzada, postulaciones existentes
- `EN_PROCESO → FINALIZADA`: fases completadas, vacantes asignadas/desiertas

---

### 6. JobProfile Module
**Entidades**: `JobProfileRequest`, `PositionCode`, `EvaluationCriterion`, `JobProfileVacancy`

```php
// job_profile_requests
- id, code: uuid, string (auto: "PROF-2025-001-01")
- job_posting_id, requesting_unit_id: uuid
- position_code_id: uuid (FK)
- status: enum (BORRADOR, EN_REVISION, MODIFICACION_REQUERIDA, APROBADO, RECHAZADO)

// Requisitos
- education_level: enum
- career_field, title_required: string
- general_experience_years: decimal(3,1)
- specific_experience_years: decimal(3,1)
- required_courses, knowledge_areas, required_competencies: jsonb

// Vacantes
- total_vacancies: integer
```

```php
// position_codes (códigos de cargo)
- code: string unique (ej: "CAP-001")
- name: string
- base_salary: decimal(10,2)
- essalud_percentage: decimal(5,2) default 9.0
- essalud_amount: decimal(10,2) (calc: base * %)
- monthly_total: decimal(10,2) (calc: base + essalud)
- contract_months: integer default 3
- quarterly_total: decimal(10,2) (calc: monthly * months)
```

```php
// evaluation_criteria
- position_code_id, process_phase_id: uuid
- name, description: string, text
- min_score, max_score, weight: decimal(5,2)
- order: integer
- is_required: boolean

// Ejemplo: Fase CV_EVALUATION
Criterio              | Min | Max | Peso
----------------------|-----|-----|-----
Formación Académica   |  0  |  20 | 20%
Experiencia General   |  0  |  15 | 15%
Experiencia Específica|  0  |  25 | 25%
Cursos y Capacitación |  0  |  20 | 20%
Conocimientos Técnicos|  0  |  20 | 20%
TOTAL                          100  100%
```

**Generación automática de vacantes**:
```php
// Al aprobar perfil con total_vacancies = 3
CONV-2025-001-01-V01 (DISPONIBLE)
CONV-2025-001-01-V02 (DISPONIBLE)
CONV-2025-001-01-V03 (DISPONIBLE)
```

---

### 7. Application Module
**Entidades**: `Application`, `ApplicationDocument`, `SpecialCondition`

```php
// applications
- id, code: uuid, string unique (auto: "APP-2025-001-001")
- job_profile_vacancy_id, applicant_id: uuid
- status: enum (PRESENTADA, EN_REVISION, APTO, NO_APTO, EN_EVALUACION, 
               SUBSANACION, APROBADA, RECHAZADA, DESISTIDA)
- application_date: timestamp
- terms_accepted: boolean

// Elegibilidad
- is_eligible: boolean nullable
- eligibility_checked_by, eligibility_checked_at
- ineligibility_reason: text

// Subsanación
- requires_amendment: boolean
- amendment_deadline: date

// Puntajes
- curriculum_score, interview_score: decimal(5,2)
- special_condition_bonus: decimal(5,2)
- final_score: decimal(5,2) (calc: sum + bonus, max 100)
- final_ranking: integer
```

**Condiciones Especiales** (bonificaciones):
```php
enum ConditionType: string {
    case DISABILITY = 'discapacidad';          // 15%
    case MILITARY = 'licenciado_ffaa';         // 10%
    case ATHLETE_NATIONAL = 'deportista_nac';  // 10%
    case ATHLETE_INTL = 'deportista_intl';     // 15%
    case TERRORISM = 'victima_terrorismo';     // 10%
}

// Cálculo
final_score = min(base_score * (1 + bonus_percentage), 100)
// Ejemplo: 85 + (85 * 0.15) = 97.75
```

**Tipos de Documento Requeridos**:
```
1. DOC_APPLICATION_FORM - Ficha firmada [REQUERIDO+FIRMA]
2. DOC_CV - CV documentado [REQUERIDO]
3. DOC_DNI - Copia DNI [REQUERIDO]
4. DOC_DEGREE - Título profesional [REQUERIDO]
5. DOC_CERTIFICATE - Certificados
6. DOC_EXPERIENCE - Constancias
7. DOC_SPECIAL_CONDITION - Docs condición especial
```

---

### 8. Evaluation Module
**Entidades**: `Evaluation`, `EvaluationDetail`, `EvaluatorAssignment`, `Appeal`

```php
// evaluations
- id: uuid
- application_id, process_phase_id, evaluator_id: uuid
- status: enum (PENDIENTE, EN_PROGRESO, COMPLETADA, MODIFICADA)
- raw_score: decimal(5,2) (suma criterios)
- special_condition_bonus, final_score: decimal(5,2)
- assigned_at, completed_at, deadline: timestamp

// evaluation_details (por criterio)
- evaluation_id, evaluation_criterion_id: uuid
- score: decimal(5,2)
- comments, evidence_notes: text
```

**Asignación de Evaluadores**:
```php
// Manual o Automática (balanceo de carga)
- Límite configurable (ej: 20 evaluaciones/jurado)
- Distribución equitativa
- Exclusión de conflictos de interés

// Evaluación Colaborativa
// Promedio ponderado cuando múltiples evaluadores
final = (score1 * weight1) + (score2 * weight2)
```

**Recursos/Reclamaciones**:
```php
// appeals
- application_id, evaluation_id: uuid
- grounds: text (fundamentos)
- status: enum (PRESENTADO, EN_REVISION, FUNDADO, INFUNDADO)
- score_before, score_after: decimal(5,2)

// Si FUNDADO → recalcular ranking + notificar afectados
```

**Gestión de Jurados**:
```php
// jury_assignments
- job_posting_id, jury_id: uuid
- role: enum (TITULAR, SUPLENTE)
- designation_document, designation_number: string

// conflict_of_interests
- jury_id, applicant_id: uuid
- conflict_type: enum (FAMILIAR, LABORAL, ECONOMICO, AMISTAD)
- severity: enum (BAJO, MEDIO, ALTO)
- action_taken: enum (NONE, RECUSAL, REASSIGNMENT)
```

---

## 🔸 Módulos de Soporte

### 9. Document Module
**Responsabilidad**: Gestión documental y firma digital PKI

**Entidades**: `DocumentTemplate`, `GeneratedDocument`, `DigitalSignature`, `SignatureCertificate`

**Firma Digital - Arquitectura PKI**:
```
1. Certificate Authority (CA)
   - Certificados X.509
   - RSA-2048/4096 bits

2. Proceso de Firma:
   a) Generar documento (PDF)
   b) Calcular hash SHA-256
   c) Firmar con llave privada RSA
   d) Timestamp Authority (TSA)
   e) Incrustar firma en PDF

3. Verificación:
   a) Extraer firma
   b) Verificar certificado (válido, no revocado)
   c) Verificar hash del documento
   d) Verificar firma con public key
   e) Verificar timestamp
   → Resultado: VALIDA/INVALIDA
```

```php
// generated_documents
- documentable_type, documentable_id: morph
- document_number: string unique
- file_path, hash: string (SHA-256)
- is_signed, fully_signed: boolean
- requires_signatures, signatures_count: integer

// digital_signatures
- generated_document_id, signer_id: uuid
- signature_value: text (RSA encrypted)
- signature_algorithm: string (RSA-SHA256)
- document_hash: string
- signed_at, timestamp_token: timestamp, text
- is_valid: boolean
```

**Plantillas con Variables**:
```html
<!-- Formato: {{variable}} -->
{{convocatoria.codigo}}
{{postulante.nombres_completos}}
{{evaluacion.puntaje_total}}
```

---

### 10. Notification Module
**Responsabilidad**: Notificaciones multi-canal

**Canales**: System, Email, SMS, Push, WhatsApp

```php
// notifications
- notifiable_type, notifiable_id: morph
- type: string (clase de notificación)
- channel: enum
- priority: enum (LOW, NORMAL, HIGH, URGENT)
- title, message, action_url: string, text, string
- is_read, read_at: boolean, timestamp
- scheduled_for, sent_at: timestamp

// notification_templates
- code: string unique (ej: "NOTIF_APPLICATION_SUBMITTED")
- event_type: string (ApplicationSubmitted)
- system_enabled, email_enabled, sms_enabled: boolean
- email_subject, email_template: string, text
- variables: jsonb
```

**Eventos que disparan notificaciones**:
```
Convocatorias: publicada, actualizada, fase próxima
Postulaciones: enviada, en revisión, apto/no apto
Evaluaciones: asignada, completada, resultados
Sistema: mensajes, cambios, alertas
```

---

### 11. Reporting Module
**Responsabilidad**: Reportes y dashboards

```php
// report_definitions
- code: string unique (ej: "RPT_CONVOCATORIA_GENERAL")
- query_type: enum (SQL, ELOQUENT, CUSTOM)
- default_format: enum (PDF, EXCEL, CSV, HTML)
- cache_ttl: integer (segundos)

// dashboards + widgets
- widget_type: enum (METRIC, CHART, TABLE, MAP, CALENDAR)
- chart_type: enum (LINE, BAR, PIE, DONUT, AREA)
```

**Reportes Predefinidos**:
1. Reporte General de Convocatoria
2. Postulaciones (demográficos, documentos, puntajes)
3. Evaluaciones (por fase, criterios, tiempos)
4. Desempeño de Jurados
5. Análisis Comparativo Anual

**KPIs Principales**:
- Convocatorias activas
- Postulaciones por estado
- Evaluaciones pendientes
- Vacantes cubiertas vs disponibles
- Tiempo promedio de proceso
- Tasa de conversión

---

### 12. Audit Module
**Responsabilidad**: Auditoría completa y trazabilidad

```php
// audit_logs (trait HasAudit)
- auditable_type, auditable_id: morph
- user_id: uuid
- event: enum (CREATED, UPDATED, DELETED, VIEWED, EXPORTED)
- old_values, new_values, changes: jsonb
- ip_address: inet
- performed_at: timestamp

// security_events
- event_type: enum (LOGIN_ATTEMPT, LOGIN_SUCCESS, BRUTE_FORCE, 
                    UNAUTHORIZED_ACCESS, SUSPICIOUS_ACTIVITY)
- severity: enum (INFO, WARNING, CRITICAL)
- requires_action: boolean

// system_accesses
- session_id, access_type: string, enum (WEB, API, MOBILE)
- login_at, logout_at: timestamp
- session_duration_seconds: integer
```

**Detección de Actividades Sospechosas**:
```
- Múltiples intentos fallidos (> 5 en 15 min)
- Login desde IP/país inusual
- Cambios masivos (> 10 registros en 1 min)
- Accesos en horarios inusuales (2am-6am)
- Descargas masivas de datos
```

**Retención de Logs**:
```
AuditLog: 7 años (normativa)
ActivityLog: 2 años
SecurityEvent: 5 años
SystemAccess: 1 año
```

---

### 13. Configuration Module
**Responsabilidad**: Configuración centralizada

```php
// system_configs
- key: string unique
- value, default_value: text
- value_type: enum (STRING, INTEGER, BOOLEAN, JSON, DATE, FILE)
- validation_rules, options: jsonb
- is_editable, is_system: boolean
```

**Grupos de Configuración**:
```
1. General: nombre, logo, colores, contacto
2. Proceso: plazos, límites, prefijos de códigos
3. Documentos: tamaño máximo, tipos permitidos, retención
4. Notificaciones: email, SMS, push
5. Seguridad: sesiones, contraseñas, 2FA, IPs
6. Integrations: RENIEC, SUNAT, SMTP
7. Reports: formatos, caché
8. Audit: retención, alertas
```

**Uso en código**:
```php
use Modules\Configuration\Facades\Config;

$systemName = Config::get('SYSTEM_NAME');
Config::set('MAX_FILE_SIZE_MB', 20);
```

---

## 📐 Patrones y Convenciones

### Estructura de un Módulo (nwidart/laravel-modules)

```
Modules/
└── ModuleName/
    ├── Config/config.php
    ├── Database/
    │   ├── Migrations/
    │   ├── Seeders/
    │   └── Factories/
    ├── Entities/ (Models)
    │   └── ModelName.php
    ├── Http/
    │   ├── Controllers/
    │   ├── Middleware/
    │   ├── Requests/
    │   └── Resources/
    ├── Providers/
    │   ├── ModuleNameServiceProvider.php
    │   └── RouteServiceProvider.php
    ├── Repositories/
    │   ├── Contracts/RepositoryInterface.php
    │   └── RepositoryEloquent.php
    ├── Routes/
    │   ├── api.php
    │   └── web.php
    ├── Services/
    │   └── ServiceName.php
    ├── Events/, Listeners/, Policies/
    ├── Traits/, ValueObjects/, DTOs/, Enums/
    ├── Exceptions/
    ├── Tests/Unit/, Tests/Feature/
    └── module.json
```

### Naming Conventions

```php
// Models (Entities)
User, Application, JobPosting (singular, PascalCase)

// Services
ApplicationService, EvaluationService

// Repositories
ApplicationRepository, UserRepository

// Controllers
ApplicationController, EvaluationController

// Requests
StoreApplicationRequest, UpdateProfileRequest

// Resources (API)
UserResource, ApplicationResource, ApplicationCollection

// Jobs
SendNotification, GenerateReport (verbo infinitivo)

// Events
ApplicationSubmitted, EvaluationCompleted (pasado)

// Listeners
SendApplicationConfirmation, NotifyJuryAssigned
```

### Repository Pattern

```php
// Interface
namespace Modules\Application\Repositories\Contracts;

interface ApplicationRepositoryInterface
{
    public function findByCode(string $code): ?Application;
    public function getByVacancy(string $vacancyId): Collection;
    public function getEligible(): Collection;
}

// Implementation
namespace Modules\Application\Repositories;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    public function __construct(protected Application $model) {}
    
    public function findByCode(string $code): ?Application
    {
        return $this->model->where('code', $code)->first();
    }
}

// Service Provider
$this->app->bind(
    ApplicationRepositoryInterface::class,
    ApplicationRepository::class
);
```

### Service Layer

```php
namespace Modules\Application\Services;

class ApplicationService
{
    public function __construct(
        protected ApplicationRepositoryInterface $repository,
        protected EligibilityService $eligibilityService,
        protected NotificationService $notificationService
    ) {}
    
    public function submit(array $data): Application
    {
        return DB::transaction(function() use ($data) {
            $application = $this->repository->create($data);
            
            event(new ApplicationSubmitted($application));
            
            return $application;
        });
    }
}
```

### Enums (PHP 8.1+)

```php
namespace Modules\JobPosting\Enums;

enum JobPostingStatus: string
{
    case DRAFT = 'BORRADOR';
    case PUBLISHED = 'PUBLICADA';
    case IN_PROCESS = 'EN_PROCESO';
    case FINALIZED = 'FINALIZADA';
    case CANCELLED = 'CANCELADA';
    
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::PUBLISHED => 'Publicada',
            // ...
        };
    }
    
    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::DRAFT => in_array($status, [self::PUBLISHED, self::CANCELLED]),
            self::PUBLISHED => in_array($status, [self::IN_PROCESS, self::CANCELLED]),
            // ...
        };
    }
}
```

### Event-Driven Communication

```php
// Event
namespace Modules\Application\Events;

class ApplicationSubmitted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Application $application) {}
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->application->applicant_id),
        ];
    }
}

// Listener
namespace Modules\Application\Listeners;

class SendApplicationConfirmation
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}
    
    public function handle(ApplicationSubmitted $event): void
    {
        $this->notificationService->send(
            $event->application->applicant,
            'application_submitted',
            ['application' => $event->application]
        );
    }
}

// EventServiceProvider
protected $listen = [
    ApplicationSubmitted::class => [
        SendApplicationConfirmation::class,
        UpdateVacancyStatus::class,
        LogApplicationActivity::class,
    ],
];
```

### API Resources

```php
namespace Modules\Application\Http\Resources;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status->value,
            'application_date' => $this->application_date->toISOString(),
            'vacancy' => VacancyResource::make($this->whenLoaded('vacancy')),
            'scores' => $this->when($this->isEvaluated(), [
                'curriculum' => $this->curriculum_score,
                'interview' => $this->interview_score,
                'final' => $this->final_score,
            ]),
        ];
    }
}
```

### Form Requests

```php
namespace Modules\Application\Http\Requests;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Application::class);
    }
    
    public function rules(): array
    {
        return [
            'vacancy_id' => ['required', 'uuid', 'exists:job_profile_vacancies,id'],
            'terms_accepted' => ['required', 'accepted'],
            'documents' => ['required', 'array', 'min:3'],
            'documents.*.file' => ['required', 'file', 'max:10240'],
        ];
    }
    
    protected function prepareForValidation(): void
    {
        $this->merge([
            'applicant_id' => $this->user()->id,
            'ip_address' => $this->ip(),
        ]);
    }
}
```

### Policies

```php
namespace Modules\Application\Policies;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            || $user->hasPermissionTo('application.view.all');
    }
    
    public function create(User $user): bool
    {
        return $user->hasRole('APPLICANT')
            && $user->active_applications_count < 
               config('jobposting.max_applications_per_user');
    }
    
    public function withdraw(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            && $application->status->in([
                ApplicationStatus::SUBMITTED,
                ApplicationStatus::IN_REVIEW
            ]);
    }
}
```

---

## 🚀 Roadmap de Implementación

### Fase 1: Fundación (3 semanas)
**Setup + Core + Auth + User + Organization + Configuration**

```bash
# Semana 1: Setup y Core
- Laravel 11 + nwidart/laravel-modules
- PostgreSQL + Redis
- Módulo Core (BaseModel, Traits, Helpers)

# Semana 2: Auth y User
- Autenticación (Sanctum)
- Roles y Permisos (Spatie)
- CRUD Usuarios

# Semana 3: Organization y Config
- Estructura jerárquica (Closure Table)
- Sistema de configuración
```

### Fase 2: Core Business (5 semanas)
**JobPosting + JobProfile + Application**

```bash
# Semana 4: JobPosting
- CRUD convocatorias
- Fases del proceso
- State Machine

# Semana 5: JobProfile
- Solicitud de perfiles
- Flujo de revisión
- Criterios de evaluación

# Semana 6-7: Application
- Postulaciones
- Documentos
- Elegibilidad
- Condiciones especiales

# Semana 8: Jury
- Asignación de jurados
- Conflictos de interés
```

### Fase 3: Evaluación (3 semanas)
**Evaluation + Appeals**

```bash
# Semana 9-10: Evaluation
- Asignación automática
- Proceso de evaluación
- Calificación por criterios
- Rankings

# Semana 11: Appeals
- Recursos/Reclamaciones
- Revisión y resolución
```

### Fase 4: Documentos y Firma (3 semanas)
**Document + Digital Signature PKI**

```bash
# Semana 12: Plantillas y Generación
- DocumentTemplate
- Generación de PDF
- Variables dinámicas

# Semana 13: Firma Digital
- PKI Infrastructure
- Certificados X.509
- Proceso de firma RSA-SHA256

# Semana 14: Integración y Testing
- Firma visual en PDF
- Verificación de firmas
- Testing exhaustivo
```

### Fase 5: Soporte (3 semanas)
**Notification + Reporting + Audit**

```bash
# Semana 15: Notification
- Sistema multi-canal
- Plantillas
- Preferencias

# Semana 16: Reporting
- Reportes predefinidos
- Dashboards
- Exportación

# Semana 17: Audit
- Activity Log
- Security Events
- Trazabilidad
```

### Fase 6: Testing y Optimización (3 semanas)

```bash
# Semana 18: Testing
- Unit Tests (>80% coverage)
- Feature Tests
- Integration Tests

# Semana 19: Performance
- Query optimization
- Índices BD
- Caché estratégico

# Semana 20: Security
- Security audit
- Penetration testing
- OWASP compliance
```

### Fase 7: Frontend (4 semanas)

```bash
# Semana 21-22: Admin Panel
- Dashboard administrativo
- Gestión convocatorias

# Semana 23: Applicant Portal
- Portal postulante
- Búsqueda y postulación

# Semana 24: Jury Portal
- Portal jurado
- Proceso de evaluación
```

### Fase 8: Deployment (2 semanas)

```bash
# Semana 25: Staging + UAT
# Semana 26: Production + Training
```

**Total: 26 semanas (6.5 meses)**

---

## 🔧 Stack Técnico Completo

### Backend
```json
{
  "laravel/framework": "^11.0",
  "nwidart/laravel-modules": "^11.0",
  "spatie/laravel-permission": "^6.0",
  "spatie/laravel-activitylog": "^4.0",
  "spatie/laravel-query-builder": "^5.0",
  "phpseclib/phpseclib": "^3.0",
  "tecnickcom/tcpdf": "^6.0",
  "maatwebsite/excel": "^3.1",
  "barryvdh/laravel-dompdf": "^2.0",
  "predis/predis": "^2.0",
  "laravel/horizon": "^5.0",
  "laravel/telescope": "^5.0"
}
```

### Testing
```json
{
  "pestphp/pest": "^2.0",
  "pestphp/pest-plugin-laravel": "^2.0",
  "mockery/mockery": "^1.0"
}
```

---

## 🔐 Seguridad - Checklist

```
✓ Password hashing (bcrypt)
✓ 2FA opcional
✓ Rate limiting (login, API)
✓ CSRF protection
✓ SQL injection prevention (prepared statements)
✓ XSS protection (escaping outputs)
✓ Encriptación en reposo y tránsito (TLS)
✓ Firma digital PKI (RSA-SHA256)
✓ Auditoría completa
✓ Session timeout configurable
✓ IP whitelist (opcional)
```

---

## 📊 Base de Datos - Índices Críticos

```sql
-- Applications
CREATE INDEX idx_applications_vacancy ON applications(job_profile_vacancy_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_code ON applications(code);

-- Evaluations
CREATE INDEX idx_evaluations_application ON evaluations(application_id);
CREATE INDEX idx_evaluations_evaluator ON evaluations(evaluator_id);

-- Audit
CREATE INDEX idx_audit_auditable ON audit_logs(auditable_type, auditable_id);
CREATE INDEX idx_audit_date ON audit_logs(performed_at);

-- Full-Text Search (PostgreSQL)
CREATE INDEX idx_applications_search ON applications 
USING GIN(to_tsvector('spanish', code || ' ' || notes));
```

---

## 🧪 Estrategia de Testing

### Pirámide de Testing
```
     /\
    /E2E\      10% - End to End
   /------\
  /Feature\    20% - Feature Tests
 /----------\
/ Unit Tests \ 70% - Unit Tests
```

### Objetivos
- **Coverage**: > 80%
- **Unit Tests**: Value Objects, Services, Repositories
- **Feature Tests**: API endpoints, workflows
- **Integration Tests**: Módulos entre sí

### Ejemplo
```php
test('applicant can submit application', function () {
    $user = User::factory()->applicant()->create();
    $vacancy = JobProfileVacancy::factory()->available()->create();
    
    actingAs($user)
        ->post('/api/applications', [
            'vacancy_id' => $vacancy->id,
            'terms_accepted' => true,
            'documents' => [/* ... */],
        ])
        ->assertStatus(201);
        
    assertDatabaseHas('applications', [
        'applicant_id' => $user->id,
        'status' => ApplicationStatus::SUBMITTED->value,
    ]);
});
```

---

## 📈 Monitoreo

### Métricas Clave
```
Performance:
- Response time (p50, p95, p99)
- Database query time
- Queue processing time

Negocio:
- Convocatorias activas
- Postulaciones por período
- Tasa de conversión
- Tiempo promedio proceso

Seguridad:
- Failed login attempts
- Suspicious activities
- Certificate expirations
```

### Herramientas
- **Development**: Telescope, Debugbar
- **Production**: Horizon (queues), Sentry (errors), New Relic/DataDog (APM)

---

## 🎯 Mejores Prácticas

1. **Usar Interfaces y Contratos** → Facilita testing y desacoplamiento
2. **Repository Pattern** → Abstrae la capa de datos
3. **Service Layer** → Lógica de negocio centralizada
4. **Event-Driven** → Comunicación desacoplada entre módulos
5. **Type Hinting estricto** → `declare(strict_types=1)`
6. **Transacciones BD** → Para operaciones críticas
7. **Validación exhaustiva** → Nunca confiar en input del usuario
8. **Principio de mínimo privilegio** → Permisos granulares
9. **Auditoría de acciones críticas** → Trazabilidad completa
10. **Testing continuo** → CI/CD con pruebas automatizadas

---

## 📞 Documentación Requerida

```
✓ README.md (setup, instalación)
✓ ARCHITECTURE.md (este documento)
✓ API.md (Swagger/OpenAPI)
✓ DATABASE.md (ERD, esquema)
✓ DEPLOYMENT.md (guía de despliegue)
✓ Manuales de usuario (Admin, Postulante, Jurado)
```

---

**Versión**: 2.0 (Condensado)  
**Última actualización**: 2025  
**Framework**: Laravel 11.x + nwidart/laravel-modules