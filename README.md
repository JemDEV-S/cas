# 🏗️ ARQUITECTURA MODULAR DEL SISTEMA DE CONVOCATORIAS

## 📋 ÍNDICE

1. [Visión General](#vision-general)
2. [Estructura de Módulos](#estructura-modulos)
3. [Módulos Core](#modulos-core)
4. [Módulos de Dominio](#modulos-dominio)
5. [Módulos de Soporte](#modulos-soporte)
6. [Módulos Transversales](#modulos-transversales)
7. [Relaciones entre Módulos](#relaciones-modulos)
8. [Patrones y Convenciones](#patrones-convenciones)

---

## 🎯 VISIÓN GENERAL

### Principios Arquitectónicos

- **Modularidad**: Cada módulo es independiente y reutilizable
- **Escalabilidad**: Fácil agregar nuevos módulos sin afectar existentes
- **Mantenibilidad**: Código organizado por dominio de negocio
- **Testabilidad**: Cada módulo puede testearse independientemente
- **Separación de Responsabilidades**: Un módulo, una responsabilidad principal

### Tecnologías Base

- **Framework**: Laravel 11.x
- **Modularización**: nwidart/laravel-modules
- **Firma Digital**: Implementación personalizada con PKI
- **Base de Datos**: PostgreSQL
- **Caché**: Redis
- **Cola de Trabajos**: Redis Queue

---

## 🗂️ ESTRUCTURA DE MÓDULOS

### Organización General

```
Modules/
├── Core/                    # Funcionalidades base compartidas
├── Auth/                    # Autenticación y autorización
├── User/                    # Gestión de usuarios
├── Organization/            # Estructura organizacional
├── JobPosting/             # Convocatorias
├── JobProfile/             # Perfiles de puesto
├── Application/            # Postulaciones
├── Evaluation/             # Sistema de evaluación
├── Jury/                   # Gestión de jurados
├── Document/               # Gestión documental y firma digital
├── Notification/           # Notificaciones
├── Reporting/              # Reportes y analíticas
├── Audit/                  # Auditoría y trazabilidad
└── Configuration/          # Configuración del sistema
```

### Matriz de Dependencias

| Módulo | Depende de | Es usado por |
|--------|-----------|--------------|
| Core | - | Todos |
| Auth | Core, User | Todos |
| User | Core, Auth | Todos |
| Organization | Core, User | JobPosting, JobProfile |
| JobPosting | Core, Organization, JobProfile | Application, Evaluation |
| JobProfile | Core, Organization | JobPosting, Application |
| Application | Core, User, JobPosting, JobProfile | Evaluation |
| Evaluation | Core, Application, Jury | Reporting |
| Jury | Core, User, Organization | Evaluation |
| Document | Core, User | Application, Evaluation |
| Notification | Core, User | Todos |
| Reporting | Core | - |
| Audit | Core | Todos |
| Configuration | Core | Todos |

---

## 🔷 MÓDULOS CORE

### 1. Core Module

**Responsabilidad**: Proporcionar funcionalidades base compartidas por todos los módulos

#### Entidades Principales
- `BaseModel` (Modelo abstracto base)
- `BaseSoftDelete` (Modelo con soft deletes)
- `BaseEnum` (Enumeraciones base)

#### Componentes

**Traits Compartidos**
- `HasUuid`: Generación automática de UUID
- `HasStatus`: Gestión de estados
- `HasMetadata`: Campos de metadatos JSON
- `Searchable`: Búsqueda full-text
- `Sortable`: Ordenamiento dinámico
- `Filterable`: Filtrado avanzado
- `Exportable`: Exportación a diferentes formatos

**Services Base**
- `BaseService`: Lógica de negocio común
- `FileService`: Manejo de archivos
- `ValidationService`: Validaciones reutilizables
- `EncryptionService`: Encriptación de datos sensibles

**Repositories**
- `BaseRepository`: Patrón repositorio base
- `CacheRepository`: Gestión de caché

**DTOs (Data Transfer Objects)**
- `PaginationDTO`
- `FilterDTO`
- `SortDTO`

**Value Objects**
- `Email`
- `PhoneNumber`
- `DNI`
- `Money`
- `DateRange`

**Helpers**
- `StringHelper`: Manipulación de strings
- `DateHelper`: Manejo de fechas
- `ArrayHelper`: Operaciones con arrays
- `NumberHelper`: Formateo de números

**Exceptions**
- `CoreException`
- `ValidationException`
- `BusinessRuleException`
- `UnauthorizedException`

#### API Expuesta
```php
// Traits
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;

// Services
use Modules\Core\Services\FileService;
use Modules\Core\Services\ValidationService;

// Repositories
use Modules\Core\Repositories\BaseRepository;

// Value Objects
use Modules\Core\ValueObjects\Email;
use Modules\Core\ValueObjects\DNI;
```

---

### 2. Auth Module

**Responsabilidad**: Gestionar autenticación, autorización y seguridad

#### Entidades Principales
- `Role` (roles del sistema)
- `Permission` (permisos granulares)
- `RolePermission` (relación roles-permisos)
- `UserSession` (sesiones activas)
- `LoginAttempt` (intentos de login)
- `PasswordReset` (tokens de recuperación)

#### Componentes

**Middleware**
- `Authenticate`: Verificación de autenticación
- `CheckRole`: Verificación de roles
- `CheckPermission`: Verificación de permisos
- `TwoFactorAuth`: Autenticación de dos factores
- `IpWhitelist`: Lista blanca de IPs

**Guards Personalizados**
- `JuryGuard`: Guard específico para jurados
- `ApplicantGuard`: Guard para postulantes

**Policies**
- `RolePolicy`: Políticas para roles
- `PermissionPolicy`: Políticas para permisos

**Services**
- `AuthService`: Lógica de autenticación
- `RoleService`: Gestión de roles
- `PermissionService`: Gestión de permisos
- `SessionService`: Gestión de sesiones
- `TwoFactorService`: 2FA

**Events**
- `UserLoggedIn`
- `UserLoggedOut`
- `LoginFailed`
- `PasswordChanged`
- `RoleAssigned`
- `PermissionGranted`

#### Roles Predefinidos
```
- SUPER_ADMIN: Control total
- ADMIN_RRHH: Gestión de convocatorias
- AREA_USER: Solicita perfiles
- RRHH_REVIEWER: Revisa perfiles
- JURY: Evalúa postulaciones
- APPLICANT: Postula a convocatorias
- VIEWER: Solo visualización
```

#### Permisos por Módulo
```
Formato: {modulo}.{accion}.{recurso}

Ejemplos:
- jobposting.create.convocatoria
- application.view.postulacion
- evaluation.update.calificacion
- reporting.export.reporte
```

#### API Expuesta
```php
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\RoleService;
use Modules\Auth\Middleware\CheckRole;
use Modules\Auth\Middleware\CheckPermission;
```

---

## 🔶 MÓDULOS DE DOMINIO

### 3. User Module

**Responsabilidad**: Gestionar usuarios del sistema

#### Entidades Principales
- `User` (usuarios del sistema)
- `UserProfile` (perfil extendido)
- `UserPreference` (preferencias)
- `UserOrganizationUnit` (asignaciones organizacionales)

#### Campos de User
```
- id: UUID
- dni: string (unique, 8 dígitos)
- email: string (unique)
- password: hashed
- first_name: string
- last_name: string
- phone: string
- photo_url: string
- email_verified_at: timestamp
- is_active: boolean
- last_login_at: timestamp
- deleted_at: timestamp (soft delete)
```

#### Campos de UserProfile
```
- user_id: UUID (FK)
- birth_date: date
- gender: enum
- address: text
- district: string
- province: string
- department: string
- biography: text
- linkedin_url: string
- metadata: jsonb
```

#### Campos de UserPreference
```
- user_id: UUID (FK)
- language: enum (es, en)
- timezone: string
- notifications_email: boolean
- notifications_system: boolean
- theme: enum (light, dark)
- date_format: string
- preferences: jsonb
```

#### Campos de UserOrganizationUnit
```
- id: UUID
- user_id: UUID (FK)
- organization_unit_id: UUID (FK)
- start_date: date
- end_date: date (nullable)
- is_primary: boolean
- is_active: boolean
```

#### Services
- `UserService`: CRUD de usuarios
- `ProfileService`: Gestión de perfiles
- `PreferenceService`: Gestión de preferencias
- `AssignmentService`: Asignaciones organizacionales

#### Events
- `UserCreated`
- `UserUpdated`
- `UserDeleted`
- `UserActivated`
- `UserDeactivated`
- `UserOrganizationChanged`

#### API Expuesta
```php
use Modules\User\Entities\User;
use Modules\User\Services\UserService;
use Modules\User\Repositories\UserRepository;
```

---

### 4. Organization Module

**Responsabilidad**: Gestionar la estructura organizacional jerárquica

#### Entidades Principales
- `OrganizationalUnit` (unidades organizacionales)
- `OrganizationalUnitType` (tipos de unidades)
- `OrganizationalUnitHistory` (historial de cambios)

#### Campos de OrganizationalUnit
```
- id: UUID
- code: string (unique, ej: "OGM-001")
- name: string
- description: text
- type: enum (ORGANO, AREA, SUB_UNIDAD)
- parent_id: UUID (nullable, FK a sí misma)
- level: integer (calculado automáticamente)
- path: string (ruta completa, ej: "/1/5/12")
- order: integer (orden de visualización)
- is_active: boolean
- metadata: jsonb
```

#### Patrón: Closure Table
Para queries eficientes de jerarquía, implementar tabla adicional:

```
organizational_unit_closure
- ancestor_id: UUID
- descendant_id: UUID
- depth: integer
```

#### Services
- `OrganizationService`: Gestión de unidades
- `HierarchyService`: Operaciones de jerarquía
- `TreeService`: Generación de árboles

#### Operaciones de Jerarquía
```
- getAncestors(unit_id): Obtener todos los padres
- getDescendants(unit_id): Obtener todos los hijos
- getSiblings(unit_id): Obtener unidades del mismo nivel
- getPath(unit_id): Obtener ruta completa
- moveUnit(unit_id, new_parent_id): Mover unidad
- getTree(): Obtener árbol completo
- getLevel(unit_id): Obtener nivel
```

#### API Expuesta
```php
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Services\OrganizationService;
use Modules\Organization\Services\HierarchyService;
```

---

### 5. JobPosting Module

**Responsabilidad**: Gestionar convocatorias y cronogramas

#### Entidades Principales
- `JobPosting` (convocatorias)
- `JobPostingStatus` (estados de convocatoria)
- `ProcessPhase` (fases del proceso)
- `JobPostingSchedule` (cronograma)
- `JobPostingPhaseStatus` (estado de fases)
- `JobPostingHistory` (historial de cambios)

#### Campos de JobPosting
```
- id: UUID
- code: string (unique, auto-generado, ej: "CONV-2025-001")
- title: string
- year: integer
- description: text
- status: enum (BORRADOR, PUBLICADA, EN_PROCESO, FINALIZADA, CANCELADA)
- start_date: date (tentativa)
- end_date: date (tentativa)
- published_at: timestamp
- published_by: UUID (FK User)
- finalized_at: timestamp
- finalized_by: UUID (FK User)
- metadata: jsonb
```

#### Campos de ProcessPhase
```
- id: UUID
- code: string (unique, ej: "PHASE_01")
- name: string
- description: text
- phase_number: integer (orden)
- requires_evaluation: boolean
- is_active: boolean
- is_system: boolean (fases predefinidas no editables)
```

#### Fases Predefinidas del Sistema
```
1. APPROVAL - Aprobación de la Convocatoria
2. PUBLICATION - Publicación de la Convocatoria
3. REGISTRATION - Registro virtual de Postulantes
4. ELIGIBLE_PUBLICATION - Publicación de postulantes APTOS
5. CV_SUBMISSION - Presentación de CV documentado
6. CV_EVALUATION - Evaluación Curricular ⚡
7. CV_RESULTS - Publicación de resultados curriculares
8. INTERVIEW - Entrevista Personal ⚡
9. INTERVIEW_RESULTS - Publicación de resultados de entrevista
10. CONTRACT - Suscripción de contrato
11. INDUCTION - Charla de Inducción
12. START_WORK - Inicio de labores

⚡ = Requiere evaluación
```

#### Campos de JobPostingSchedule
```
- id: UUID
- job_posting_id: UUID (FK)
- process_phase_id: UUID (FK)
- scheduled_start_date: date
- scheduled_end_date: date
- actual_start_date: date (nullable)
- actual_end_date: date (nullable)
- location: string (ej: "Portal Web", "Municipalidad")
- responsible_unit_id: UUID (FK OrganizationalUnit)
- notes: text
- status: enum (PENDING, IN_PROGRESS, COMPLETED, DELAYED)
- order: integer
```

#### State Machine - Estados de Convocatoria

```
BORRADOR
  ↓ (publicar)
PUBLICADA
  ↓ (iniciar proceso)
EN_PROCESO
  ↓ (finalizar)
FINALIZADA

Desde cualquier estado → CANCELADA
```

#### Validaciones de Estado
```
BORRADOR → PUBLICADA:
  - Debe tener cronograma completo
  - Debe tener al menos un perfil aprobado
  - Fechas no deben estar en el pasado

PUBLICADA → EN_PROCESO:
  - Fecha de inicio debe haber llegado
  - Debe tener postulaciones

EN_PROCESO → FINALIZADA:
  - Todas las fases completadas
  - Vacantes asignadas o declaradas desiertas

* → CANCELADA:
  - Justificación obligatoria
  - No se puede revertir
```

#### Services
- `JobPostingService`: CRUD de convocatorias
- `ScheduleService`: Gestión de cronogramas
- `PhaseService`: Gestión de fases
- `WorkflowService`: Transiciones de estado
- `PublicationService`: Publicación y comunicación

#### Events
- `JobPostingCreated`
- `JobPostingPublished`
- `JobPostingStarted`
- `JobPostingFinalized`
- `JobPostingCancelled`
- `PhaseStarted`
- `PhaseCompleted`
- `ScheduleDelayed`

#### API Expuesta
```php
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Services\JobPostingService;
use Modules\JobPosting\Services\ScheduleService;
use Modules\JobPosting\Services\WorkflowService;
```

---

### 6. JobProfile Module

**Responsabilidad**: Gestionar perfiles de puesto, códigos y criterios de evaluación

#### Entidades Principales
- `JobProfileRequest` (solicitudes de perfil)
- `JobProfileStatus` (estados de solicitud)
- `JobProfileVacancy` (vacantes individuales)
- `PositionCode` (códigos de posición/cargo)
- `EvaluationCriterion` (criterios de evaluación)
- `JobProfileHistory` (historial de cambios)

#### Campos de JobProfileRequest
```
- id: UUID
- code: string (auto-generado, ej: "PROF-2025-001-01")
- job_posting_id: UUID (FK)
- requesting_unit_id: UUID (FK OrganizationalUnit)
- requested_by: UUID (FK User)
- position_code_id: UUID (FK)
- profile_name: string
- status: enum (BORRADOR, EN_REVISION, MODIFICACION_REQUERIDA, APROBADO, RECHAZADO)
- 
// Requisitos académicos
- education_level: enum (SECUNDARIA, TECNICO, UNIVERSITARIO, POSTGRADO)
- career_field: string
- title_required: string
- colegiatura_required: boolean

// Experiencia
- general_experience_years: decimal(3,1)
- specific_experience_years: decimal(3,1)
- specific_experience_description: text

// Capacitación
- required_courses: jsonb (array de cursos)

// Conocimientos
- knowledge_areas: jsonb (array de áreas)

// Competencias
- required_competencies: jsonb (array de competencias)

// Funciones del puesto
- main_functions: jsonb (array de funciones)

// Condiciones laborales
- work_regime: enum (CAS, 728, 276, LOCACION)
- justification: text (justificación del requerimiento)

// Vacantes
- total_vacancies: integer

// Revisión
- reviewed_by: UUID (FK User, nullable)
- reviewed_at: timestamp (nullable)
- review_comments: text (nullable)

- approved_by: UUID (FK User, nullable)
- approved_at: timestamp (nullable)
- rejection_reason: text (nullable)

- metadata: jsonb
```

#### State Machine - Estados de Perfil

```
BORRADOR
  ↓ (enviar a revisión)
EN_REVISION
  ↓ (solicitar cambios)    ↓ (aprobar)
MODIFICACION_REQUERIDA → APROBADO
  ↓ (rechazar)
RECHAZADO

BORRADOR ← (reenviar) ← MODIFICACION_REQUERIDA
```

#### Campos de PositionCode
```
- id: UUID
- code: string (unique, ej: "CAP-001", "ESP-002")
- name: string (nombre del cargo)
- description: text
- base_salary: decimal(10,2)
- essalud_percentage: decimal(5,2) (default: 9.0)
- essalud_amount: decimal(10,2) (calculado)
- monthly_total: decimal(10,2) (calculado)
- contract_months: integer (default: 3)
- quarterly_total: decimal(10,2) (calculado)
- is_active: boolean
```

#### Cálculos Automáticos
```
essalud_amount = base_salary * (essalud_percentage / 100)
monthly_total = base_salary + essalud_amount
quarterly_total = monthly_total * contract_months
```

#### Campos de EvaluationCriterion
```
- id: UUID
- position_code_id: UUID (FK)
- process_phase_id: UUID (FK)
- name: string (ej: "Formación Académica")
- description: text
- min_score: decimal(5,2)
- max_score: decimal(5,2)
- weight: decimal(5,2) (porcentaje, suma debe ser 100)
- order: integer
- is_required: boolean
- metadata: jsonb
```

#### Ejemplo de Criterios por Fase

**Fase: Evaluación Curricular**
```
1. Formación Académica (0-20 puntos, 20%)
2. Experiencia General (0-15 puntos, 15%)
3. Experiencia Específica (0-25 puntos, 25%)
4. Cursos y Capacitaciones (0-20 puntos, 20%)
5. Conocimientos Técnicos (0-20 puntos, 20%)
Total: 100 puntos (100%)
```

**Fase: Entrevista Personal**
```
1. Conocimientos del Puesto (0-30 puntos, 30%)
2. Habilidades Comunicativas (0-20 puntos, 20%)
3. Resolución de Problemas (0-25 puntos, 25%)
4. Actitud y Motivación (0-15 puntos, 15%)
5. Adaptabilidad (0-10 puntos, 10%)
Total: 100 puntos (100%)
```

#### Campos de JobProfileVacancy
```
- id: UUID
- job_profile_request_id: UUID (FK)
- vacancy_number: integer (correlativo)
- code: string (generado, ej: "CONV-2025-001-01-V01")
- status: enum (DISPONIBLE, EN_PROCESO, CUBIERTA, DESIERTA)
- assigned_application_id: UUID (FK Application, nullable)
- declared_vacant_at: timestamp (nullable)
- declared_vacant_reason: text (nullable)
- metadata: jsonb
```

#### Generación Automática de Vacantes
Al aprobar un perfil con `total_vacancies = 3`, se generan:
```
CONV-2025-001-01-V01 (DISPONIBLE)
CONV-2025-001-01-V02 (DISPONIBLE)
CONV-2025-001-01-V03 (DISPONIBLE)
```

#### Services
- `JobProfileService`: Gestión de perfiles
- `PositionCodeService`: Gestión de códigos
- `CriterionService`: Gestión de criterios
- `VacancyService`: Gestión de vacantes
- `ReviewService`: Proceso de revisión

#### Events
- `ProfileRequested`
- `ProfileInReview`
- `ProfileModificationRequested`
- `ProfileApproved`
- `ProfileRejected`
- `VacanciesGenerated`
- `CriteriaUpdated`

#### API Expuesta
```php
use Modules\JobProfile\Entities\JobProfileRequest;
use Modules\JobProfile\Entities\PositionCode;
use Modules\JobProfile\Services\JobProfileService;
use Modules\JobProfile\Services\VacancyService;
```

---

### 7. Application Module

**Responsabilidad**: Gestionar postulaciones de candidatos

#### Entidades Principales
- `Application` (postulaciones)
- `ApplicationStatus` (estados)
- `ApplicationDocument` (documentos)
- `DocumentType` (tipos de documento)
- `SpecialCondition` (condiciones especiales)
- `ConditionType` (tipos de condición)
- `ApplicationStatusHistory` (historial)

#### Campos de Application
```
- id: UUID
- code: string (unique, auto-generado, ej: "APP-2025-001-001")
- job_profile_vacancy_id: UUID (FK)
- applicant_id: UUID (FK User)
- status: enum (PRESENTADA, EN_REVISION, APTO, NO_APTO, EN_EVALUACION, SUBSANACION, APROBADA, RECHAZADA, DESISTIDA)
- 
- application_date: timestamp
- terms_accepted: boolean
- terms_accepted_at: timestamp
- terms_ip_address: inet

// Elegibilidad
- is_eligible: boolean (nullable)
- eligibility_checked_by: UUID (FK User, nullable)
- eligibility_checked_at: timestamp (nullable)
- eligibility_notes: text (nullable)
- ineligibility_reason: text (nullable)

// Subsanación
- requires_amendment: boolean
- amendment_deadline: date (nullable)
- amendment_requested_at: timestamp (nullable)
- amendment_completed_at: timestamp (nullable)

// Resultados
- curriculum_score: decimal(5,2) (nullable)
- interview_score: decimal(5,2) (nullable)
- special_condition_bonus: decimal(5,2) (nullable)
- final_score: decimal(5,2) (nullable)
- final_ranking: integer (nullable)

- metadata: jsonb
```

#### State Machine - Estados de Postulación

```
PRESENTADA
  ↓ (revisar documentos)
EN_REVISION
  ↓ (verificar requisitos)     ↓ (rechazar)
APTO              NO_APTO
  ↓                              ↑ (solicitar subsanación)
  ↓                         SUBSANACION
  ↓ (asignar a evaluación)
EN_EVALUACION
  ↓ (completar evaluación)
APROBADA / RECHAZADA

Desde PRESENTADA o EN_REVISION → DESISTIDA (por el postulante)
```

#### Campos de ApplicationDocument
```
- id: UUID
- application_id: UUID (FK)
- document_type_id: UUID (FK)
- file_name: string
- file_path: string
- file_size: bigint (bytes)
- mime_type: string
- uploaded_at: timestamp
- uploaded_by: UUID (FK User)

// Verificación
- is_verified: boolean
- verified_by: UUID (FK User, nullable)
- verified_at: timestamp (nullable)
- verification_notes: text (nullable)

// Firma Digital
- is_signed: boolean
- digital_signature: text (nullable)
- signature_algorithm: string (nullable)
- signed_at: timestamp (nullable)
- certificate_info: jsonb (nullable)

- metadata: jsonb
```

#### Campos de DocumentType
```
- id: UUID
- code: string (unique, ej: "DOC_DNI", "DOC_CV")
- name: string
- description: text
- is_required: boolean
- max_file_size: integer (MB)
- allowed_mime_types: jsonb (array)
- requires_signature: boolean
- display_order: integer
- is_active: boolean
```

#### Tipos de Documento Predefinidos
```
1. DOC_APPLICATION_FORM - Ficha de Postulación (firmada) [REQUERIDO, FIRMA]
2. DOC_CV - CV Documentado [REQUERIDO]
3. DOC_DNI - Copia de DNI [REQUERIDO]
4. DOC_DEGREE - Título Profesional [REQUERIDO]
5. DOC_CERTIFICATE - Certificados Académicos
6. DOC_EXPERIENCE - Constancias de Experiencia
7. DOC_COURSES - Certificados de Cursos
8. DOC_COLEGIATURA - Constancia de Colegiatura
9. DOC_SPECIAL_CONDITION - Documento de Condición Especial
10. DOC_OTHER - Otros Documentos
```

#### Campos de SpecialCondition
```
- id: UUID
- application_id: UUID (FK)
- condition_type_id: UUID (FK)

// Deportista Calificado
- athlete_level: enum (LOCAL, REGIONAL, NACIONAL, INTERNACIONAL) (nullable)

// Documentación
- supporting_document_id: UUID (FK ApplicationDocument)

// Verificación
- is_verified: boolean
- verified_by: UUID (FK User, nullable)
- verified_at: timestamp (nullable)
- verification_notes: text (nullable)

// Bonificación
- bonus_percentage: decimal(5,2) (calculado según normativa)
- bonus_applied: boolean

- metadata: jsonb
```

#### Campos de ConditionType
```
- id: UUID
- code: string (unique, ej: "COND_DISABILITY")
- name: string
- description: text
- bonus_percentage: decimal(5,2)
- requires_level: boolean (para deportistas)
- legal_reference: text
- is_active: boolean
```

#### Condiciones Especiales Predefinidas
```
1. COND_DISABILITY - Persona con Discapacidad (15%)
2. COND_MILITARY - Licenciado de FFAA (10%)
3. COND_ATHLETE_LOCAL - Deportista Nivel Local (5%)
4. COND_ATHLETE_REGIONAL - Deportista Nivel Regional (7%)
5. COND_ATHLETE_NATIONAL - Deportista Nivel Nacional (10%)
6. COND_ATHLETE_INTERNATIONAL - Deportista Nivel Internacional (15%)
7. COND_TERRORISM - Víctima de Terrorismo (10%)
```

#### Cálculo de Bonificaciones
```php
// Ejemplo:
puntaje_base = 85.00
bonificacion_discapacidad = 85.00 * 0.15 = 12.75
puntaje_final = 85.00 + 12.75 = 97.75 (máximo 100)
```

#### Services
- `ApplicationService`: Gestión de postulaciones
- `DocumentService`: Gestión de documentos
- `EligibilityService`: Verificación de elegibilidad
- `SpecialConditionService`: Gestión de condiciones especiales
- `AmendmentService`: Proceso de subsanación
- `RankingService`: Cálculo de rankings

#### Events
- `ApplicationSubmitted`
- `ApplicationInReview`
- `ApplicationEligible`
- `ApplicationIneligible`
- `AmendmentRequested`
- `AmendmentCompleted`
- `DocumentUploaded`
- `DocumentVerified`
- `SpecialConditionVerified`
- `ApplicationWithdrawn`

#### API Expuesta
```php
use Modules\Application\Entities\Application;
use Modules\Application\Services\ApplicationService;
use Modules\Application\Services\EligibilityService;
use Modules\Application\Services\RankingService;
```

---

### 8. Evaluation Module

**Responsabilidad**: Gestionar el proceso de evaluación de postulaciones

#### Entidades Principales
- `Evaluation` (evaluaciones)
- `EvaluationDetail` (detalles por criterio)
- `EvaluatorAssignment` (asignaciones)
- `EvaluationHistory` (historial de cambios)
- `Appeal` (recursos/reclamaciones)

#### Campos de Evaluation
```
- id: UUID
- application_id: UUID (FK)
- process_phase_id: UUID (FK)
- evaluator_id: UUID (FK User)
- status: enum (PENDIENTE, EN_PROGRESO, COMPLETADA, MODIFICADA)

// Puntajes
- raw_score: decimal(5,2) (suma de criterios)
- special_condition_bonus: decimal(5,2)
- final_score: decimal(5,2) (raw + bonus, max 100)

// Fechas
- assigned_at: timestamp
- started_at: timestamp (nullable)
- completed_at: timestamp (nullable)
- deadline: date

// Control
- is_draft: boolean (guardado como borrador)
- is_editable: boolean (puede modificarse)
- can_view_by_applicant: boolean

- general_comments: text (nullable)
- metadata: jsonb
```

#### Campos de EvaluationDetail
```
- id: UUID
- evaluation_id: UUID (FK)
- evaluation_criterion_id: UUID (FK)
-
- score: decimal(5,2)
- comments: text
- evidence_notes: text (fundamentos de la calificación)
- 
- order: integer
- metadata: jsonb
```

#### Validaciones de Evaluación
```
1. Puntaje debe estar entre min_score y max_score del criterio
2. Todos los criterios deben ser calificados
3. Si el criterio es obligatorio (is_required), comentarios son obligatorios
4. Raw score debe coincidir con suma de criterios
5. Final score = min(raw_score + bonus, 100)
```

#### Campos de EvaluatorAssignment
```
- id: UUID
- process_phase_id: UUID (FK)
- evaluator_id: UUID (FK User)
- application_id: UUID (FK)
- assigned_by: UUID (FK User)
- assigned_at: timestamp
- 
- is_primary: boolean (evaluador principal)
- weight: decimal(5,2) (peso en evaluación colaborativa, default 100)
- 
- accepted_at: timestamp (nullable)
- rejected_at: timestamp (nullable)
- rejection_reason: text (nullable)
- 
- metadata: jsonb
```

#### Asignación de Evaluadores

**Manual**
```
1. Seleccionar fase de evaluación
2. Seleccionar jurados disponibles
3. Asignar manualmente a postulaciones
4. Control de carga equitativa
```

**Automática**
```
Algoritmo:
1. Obtener jurados activos de la convocatoria
2. Obtener postulaciones aptas
3. Distribuir equitativamente
4. Considerar carga actual de cada jurado
5. Considerar área organizacional
6. Excluir conflictos de interés
```

**Control de Carga**
```
- Ver cuántas evaluaciones tiene cada jurado
- Límite máximo configurable (ej: 20 evaluaciones)
- Sugerir distribución óptima
- Balancear carga automáticamente
```

#### Evaluación Colaborativa

Cuando múltiples evaluadores califican la misma postulación:

```php
// Ejemplo con 2 evaluadores:
evaluador_1: 85 puntos (peso 50%)
evaluador_2: 90 puntos (peso 50%)

puntaje_promedio = (85 * 0.5) + (90 * 0.5) = 87.5 puntos
```

#### Campos de Appeal
```
- id: UUID
- application_id: UUID (FK)
- evaluation_id: UUID (FK)
- evaluation_criterion_id: UUID (FK, nullable) (criterio específico reclamado)

// Solicitud
- submitted_by: UUID (FK User, el postulante)
- submitted_at: timestamp
- grounds: text (fundamentos del recurso)
- supporting_documents: jsonb (array de document_ids)

// Plazo
- deadline: date
- submitted_within_deadline: boolean

// Resolución
- status: enum (PRESENTADO, EN_REVISION, FUNDADO, INFUNDADO)
- reviewed_by: UUID (FK User, nullable) (comisión)
- reviewed_at: timestamp (nullable)
- resolution: text (nullable)
- 
// Si es fundado
- score_before: decimal(5,2) (nullable)
- score_after: decimal(5,2) (nullable)
- 
- notified_at: timestamp (nullable)
- metadata: jsonb
```

#### Proceso de Recursos

```
PRESENTADO
  ↓ (comisión revisa)
EN_REVISION
  ↓ (emite resolución)
FUNDADO / INFUNDADO

Si FUNDADO:
  → Modificar puntaje
  → Recalcular ranking
  → Notificar a todos los afectados
```

#### Services
- `EvaluationService`: Gestión de evaluaciones
- `AssignmentService`: Asignación de evaluadores
- `ScoringService`: Cálculo de puntajes
- `CollaborativeEvaluationService`: Evaluaciones colaborativas
- `AppealService`: Gestión de recursos
- `ResultService`: Generación de resultados

#### Events
- `EvaluationAssigned`
- `EvaluationStarted`
- `EvaluationSavedAsDraft`
- `EvaluationCompleted`
- `EvaluationModified`
- `AppealSubmitted`
- `AppealResolved`
- `ResultsPublished`

#### API Expuesta
```php
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Services\EvaluationService;
use Modules\Evaluation\Services\AssignmentService;
use Modules\Evaluation\Services\ResultService;
```

---

### 9. Jury Module

**Responsabilidad**: Gestionar jurados y su participación en convocatorias

#### Entidades Principales
- `JuryAssignment` (asignación de jurados)
- `JuryTraining` (capacitaciones)
- `JuryPerformance` (métricas de desempeño)
- `ConflictOfInterest` (conflictos de interés)

#### Campos de JuryAssignment
```
- id: UUID
- job_posting_id: UUID (FK)
- jury_id: UUID (FK User)
- assigned_by: UUID (FK User)
- assigned_at: timestamp

// Tipo de participación
- role: enum (TITULAR, SUPLENTE)
- specialization_area: string (nullable)

// Documento de designación
- designation_document: string (nullable)
- designation_number: string (nullable)
- designation_date: date (nullable)

// Estado
- is_active: boolean
- activated_at: timestamp (nullable)
- deactivated_at: timestamp (nullable)
- deactivation_reason: text (nullable)

// Excusas
- excused_from: date (nullable)
- excused_until: date (nullable)
- excuse_reason: text (nullable)

- metadata: jsonb
```

#### Validaciones para Jurado
```
1. Usuario debe tener rol JURY
2. No debe tener conflicto de interés con postulantes
3. Debe estar disponible en fechas de evaluación
4. Debe haber completado capacitación (si es requerida)
5. Experiencia mínima en el área (configurable)
```

#### Campos de JuryTraining
```
- id: UUID
- jury_id: UUID (FK User)
- job_posting_id: UUID (FK, nullable) (capacitación específica)

- training_type: enum (GENERAL, SPECIFIC, REFRESHER)
- training_date: date
- duration_hours: integer
- topics: jsonb (array de temas)
- instructor: string

- completed: boolean
- score: decimal(5,2) (nullable, si hay evaluación)
- certificate_number: string (nullable)
- certificate_issued_at: date (nullable)

- metadata: jsonb
```

#### Tipos de Capacitación
```
1. GENERAL: Capacitación general para nuevos jurados
   - Manual del evaluador
   - Proceso de selección
   - Criterios de evaluación
   - Ética y confidencialidad

2. SPECIFIC: Capacitación específica para una convocatoria
   - Perfiles de la convocatoria
   - Criterios específicos
   - Sistema de calificación

3. REFRESHER: Actualización para jurados existentes
   - Cambios normativos
   - Mejores prácticas
   - Casos prácticos
```

#### Campos de JuryPerformance
```
- id: UUID
- jury_id: UUID (FK User)
- job_posting_id: UUID (FK, nullable) (métricas por convocatoria)
- evaluation_period_start: date
- evaluation_period_end: date

// Métricas de productividad
- evaluations_assigned: integer
- evaluations_completed: integer
- evaluations_pending: integer
- completion_rate: decimal(5,2) (porcentaje)

// Métricas de calidad
- average_evaluation_time_hours: decimal(8,2)
- on_time_submissions: integer
- late_submissions: integer
- punctuality_rate: decimal(5,2) (porcentaje)

// Métricas de consistencia
- average_score_given: decimal(5,2)
- score_variance: decimal(5,2)
- modifications_requested: integer (por otros jurados o admin)

// Comparación con otros jurados
- relative_strictness: enum (LENIENTE, PROMEDIO, ESTRICTO)

- calculated_at: timestamp
- metadata: jsonb
```

#### Cálculo de Métricas
```php
completion_rate = (evaluations_completed / evaluations_assigned) * 100
punctuality_rate = (on_time_submissions / evaluations_completed) * 100

// Consistencia (comparar con otros jurados en mismas postulaciones)
relative_strictness:
  - si average_score < (promedio_general - desviación): ESTRICTO
  - si average_score > (promedio_general + desviación): LENIENTE
  - caso contrario: PROMEDIO
```

#### Campos de ConflictOfInterest
```
- id: UUID
- jury_id: UUID (FK User)
- applicant_id: UUID (FK User, nullable)
- job_posting_id: UUID (FK, nullable)

- conflict_type: enum (FAMILIAR, LABORAL, ECONOMICO, AMISTAD, OTRO)
- description: text
- severity: enum (BAJO, MEDIO, ALTO)

- declared_by: UUID (FK User) (quien lo declara)
- declared_at: timestamp

- reviewed_by: UUID (FK User, nullable) (admin que revisa)
- reviewed_at: timestamp (nullable)
- review_decision: enum (APROBADO, RECHAZADO) (nullable)

- action_taken: enum (NONE, RECUSAL, REASSIGNMENT) (nullable)
- action_notes: text (nullable)

- metadata: jsonb
```

#### Proceso de Conflicto de Interés
```
1. Jurado puede autodeclarar conflicto
2. Admin puede identificar conflicto
3. Validar severidad y tipo
4. Decidir acción:
   - NONE: Conflicto menor, continúa
   - RECUSAL: Jurado se excusa de esa postulación
   - REASSIGNMENT: Reasignar evaluación a otro jurado
```

#### Services
- `JuryService`: Gestión de jurados
- `AssignmentService`: Asignaciones a convocatorias
- `TrainingService`: Capacitaciones
- `PerformanceService`: Métricas de desempeño
- `ConflictService`: Gestión de conflictos

#### Events
- `JuryAssigned`
- `JuryActivated`
- `JuryDeactivated`
- `JuryExcused`
- `TrainingCompleted`
- `ConflictDeclared`
- `ConflictResolved`

#### API Expuesta
```php
use Modules\Jury\Entities\JuryAssignment;
use Modules\Jury\Services\JuryService;
use Modules\Jury\Services\PerformanceService;
```

---

## 🔸 MÓDULOS DE SOPORTE

### 10. Document Module

**Responsabilidad**: Gestión documental centralizada y firma digital

#### Entidades Principales
- `DocumentTemplate` (plantillas)
- `GeneratedDocument` (documentos generados)
- `DigitalSignature` (firmas digitales)
- `SignatureCertificate` (certificados)
- `DocumentAudit` (auditoría de documentos)

#### Campos de DocumentTemplate
```
- id: UUID
- code: string (unique, ej: "TPL_CONVOCATORIA", "TPL_ACTA")
- name: string
- description: text
- category: enum (CONVOCATORIA, PERFIL, EVALUACION, CONTRATO, ACTA, CERTIFICADO, CONSTANCIA)

// Contenido
- template_content: text (HTML con variables)
- variables: jsonb (array de variables disponibles)
- styles_css: text (nullable)

// Configuración
- page_size: enum (A4, LETTER)
- orientation: enum (PORTRAIT, LANDSCAPE)
- margins_json: jsonb (top, right, bottom, left)

// Firma
- requires_signature: boolean
- signature_positions: jsonb (coordenadas x,y para firmas)
- signers_required: integer (cuántas firmas necesita)

- version: integer (control de versiones)
- is_active: boolean
- metadata: jsonb
```

#### Variables de Plantilla

**Formato**: `{{variable_name}}`

```
Convocatoria:
- {{convocatoria.codigo}}
- {{convocatoria.titulo}}
- {{convocatoria.año}}
- {{convocatoria.fecha_publicacion}}

Perfil:
- {{perfil.nombre}}
- {{perfil.codigo_posicion}}
- {{perfil.unidad_solicitante}}
- {{perfil.requisitos_academicos}}

Postulante:
- {{postulante.nombres_completos}}
- {{postulante.dni}}
- {{postulante.email}}

Evaluación:
- {{evaluacion.puntaje_total}}
- {{evaluacion.ranking}}
- {{evaluacion.fecha}}
```

#### Campos de GeneratedDocument
```
- id: UUID
- document_template_id: UUID (FK)
-
// Referencia al objeto que generó el documento
- documentable_type: string (morph, ej: "Modules\JobPosting\Entities\JobPosting")
- documentable_id: UUID (morph)

- document_number: string (correlativo único)
- generated_at: timestamp
- generated_by: UUID (FK User)

// Archivo
- file_path: string
- file_name: string
- file_size: bigint (bytes)
- mime_type: string (generalmente application/pdf)
- hash: string (SHA-256 del contenido original)

// Firma Digital
- is_signed: boolean
- requires_signatures: integer
- signatures_count: integer (actual)
- fully_signed: boolean
- signed_at: timestamp (nullable, cuando se completa todas las firmas)

// Control de versiones
- version: integer
- is_latest: boolean
- previous_version_id: UUID (nullable, FK a sí mismo)

// Acceso
- is_public: boolean
- access_token: string (nullable, para acceso temporal sin autenticación)
- access_expires_at: timestamp (nullable)

- metadata: jsonb
```

#### Firma Digital - Arquitectura

**Componentes de PKI (Public Key Infrastructure)**

```
1. Certificate Authority (CA)
   - Autoridad certificadora interna o externa
   - Gestión de certificados digitales

2. Digital Certificate
   - X.509 format
   - Contiene: public key, identidad, validez

3. Signature Algorithm
   - RSA-SHA256 (recomendado)
   - ECDSA-SHA256 (alternativa)

4. Timestamp Authority
   - Sello de tiempo confiable
   - Previene repudio
```

#### Campos de DigitalSignature
```
- id: UUID
- generated_document_id: UUID (FK)
- signer_id: UUID (FK User)
- signature_certificate_id: UUID (FK)

// Firma
- signature_value: text (firma digital encriptada)
- signature_algorithm: string (ej: "RSA-SHA256")
- hash_algorithm: string (ej: "SHA-256")
- document_hash: string (hash del documento al momento de firmar)

// Posición en documento (si es PDF)
- page_number: integer (nullable)
- position_x: decimal (nullable)
- position_y: decimal (nullable)
- signature_image_path: string (nullable, imagen de la firma visual)

// Timestamp
- signed_at: timestamp
- timestamp_token: text (nullable, de TSA)
- timestamp_authority: string (nullable)

// Validación
- is_valid: boolean
- validated_at: timestamp (nullable)
- validation_status: enum (VALIDA, INVALIDA, REVOCADA, EXPIRADA)
- validation_notes: text (nullable)

- metadata: jsonb
```

#### Campos de SignatureCertificate
```
- id: UUID
- user_id: UUID (FK)

// Certificado Digital
- certificate_pem: text (certificado en formato PEM)
- certificate_serial: string (número de serie)
- certificate_issuer: string (CA emisor)
- certificate_subject: string (datos del titular)

// Llaves
- public_key_pem: text
- private_key_encrypted: text (encriptado con contraseña del usuario)
- key_algorithm: string (ej: "RSA-2048")

// Validez
- issued_at: date
- expires_at: date
- is_active: boolean
- revoked_at: timestamp (nullable)
- revocation_reason: text (nullable)

// Uso
- purpose: enum (SIGNATURE, ENCRYPTION, BOTH)
- can_sign_documents: boolean
- can_sign_certificates: boolean

- metadata: jsonb
```

#### Proceso de Firma Digital

**1. Generación de Documento**
```
a. Seleccionar plantilla
b. Llenar variables con datos
c. Generar PDF
d. Calcular hash SHA-256
e. Guardar en storage
f. Registrar en BD
```

**2. Solicitud de Firma**
```
a. Verificar certificado del firmante (válido y no expirado)
b. Solicitar contraseña del certificado
c. Desencriptar llave privada
```

**3. Proceso de Firma**
```
a. Obtener hash del documento
b. Firmar hash con llave privada
c. Generar timestamp de TSA
d. Incrustar firma en PDF (si aplica)
e. Guardar firma en BD
f. Actualizar estado del documento
```

**4. Verificación de Firma**
```
a. Extraer firma del documento
b. Verificar certificado (válido, no revocado)
c. Verificar hash del documento
d. Verificar firma con public key
e. Verificar timestamp
f. Emitir resultado: VALIDA/INVALIDA
```

#### Integración con PDF (usando php-pdftk o similar)
```
- Agregar firma visible en posición específica
- Agregar metadatos de firma
- Proteger documento contra modificaciones
- Mantener firmas existentes si hay múltiples firmantes
```

#### Campos de DocumentAudit
```
- id: UUID
- generated_document_id: UUID (FK)
- action: enum (CREATED, VIEWED, DOWNLOADED, SIGNED, VERIFIED, SHARED, DELETED)
- performed_by: UUID (FK User, nullable)
- performed_at: timestamp
- ip_address: inet
- user_agent: text
- details: jsonb
- metadata: jsonb
```

#### Services
- `DocumentService`: Gestión de documentos
- `TemplateService`: Gestión de plantillas
- `GenerationService`: Generación de documentos
- `SignatureService`: Firma digital
- `CertificateService`: Gestión de certificados
- `VerificationService`: Verificación de firmas
- `StorageService`: Almacenamiento de archivos

#### Events
- `DocumentGenerated`
- `DocumentSigned`
- `SignatureVerified`
- `CertificateIssued`
- `CertificateRevoked`
- `DocumentAccessed`

#### API Expuesta
```php
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Services\VerificationService;
```

---

### 11. Notification Module

**Responsabilidad**: Sistema de notificaciones multi-canal

#### Entidades Principales
- `Notification` (notificaciones)
- `NotificationTemplate` (plantillas)
- `NotificationChannel` (canales de envío)
- `NotificationPreference` (preferencias de usuario)
- `NotificationQueue` (cola de envío)
- `NotificationLog` (log de envío)

#### Campos de Notification
```
- id: UUID
- notifiable_type: string (morph, tipo de usuario)
- notifiable_id: UUID (morph, id del usuario)
- 
- type: string (clase de notificación, ej: "PostulationSubmittedNotification")
- channel: enum (SYSTEM, EMAIL, SMS, PUSH, WHATSAPP)
- priority: enum (LOW, NORMAL, HIGH, URGENT)

// Contenido
- title: string
- message: text
- action_text: string (nullable, texto del botón/link)
- action_url: string (nullable)
- icon: string (nullable, emoji o nombre de icono)
- color: string (nullable, hex color)

// Referencia a objeto relacionado
- related_type: string (nullable, morph)
- related_id: UUID (nullable, morph)

// Estado
- is_read: boolean
- read_at: timestamp (nullable)
- is_archived: boolean
- archived_at: timestamp (nullable)

// Envío
- scheduled_for: timestamp (nullable, envío programado)
- sent_at: timestamp (nullable)
- delivered_at: timestamp (nullable)
- failed_at: timestamp (nullable)
- failure_reason: text (nullable)

- data: jsonb (datos adicionales)
- metadata: jsonb
```

#### Campos de NotificationTemplate
```
- id: UUID
- code: string (unique, ej: "NOTIF_APPLICATION_SUBMITTED")
- name: string
- description: text

- event_type: string (evento que dispara, ej: "ApplicationSubmitted")
- category: enum (CONVOCATORIA, POSTULACION, EVALUACION, SISTEMA)

// Contenido por canal
- system_enabled: boolean
- system_template: text (nullable)

- email_enabled: boolean
- email_subject: string (nullable)
- email_template: text (nullable, HTML)

- sms_enabled: boolean
- sms_template: string (nullable, max 160 chars)

- push_enabled: boolean
- push_template: text (nullable)

// Variables disponibles
- variables: jsonb (array)

// Configuración
- is_mandatory: boolean (no puede desactivarse)
- is_active: boolean
- priority: enum (LOW, NORMAL, HIGH, URGENT)

- metadata: jsonb
```

#### Ejemplos de Plantillas

**Postulación Recibida**
```
Código: NOTIF_APPLICATION_SUBMITTED

Email:
Asunto: Postulación Recibida - {{convocatoria.codigo}}
Cuerpo:
Estimado/a {{postulante.nombres}},

Hemos recibido su postulación para el cargo de {{perfil.nombre}} 
en la convocatoria {{convocatoria.codigo}}.

Código de postulación: {{postulacion.codigo}}
Fecha de postulación: {{postulacion.fecha}}

Puede hacer seguimiento ingresando al sistema con su usuario.

Sistema:
🎯 Postulación recibida para {{perfil.nombre}}
Código: {{postulacion.codigo}}

SMS:
Postulacion recibida. Codigo: {{postulacion.codigo}}. 
Ver estado en www.ejemplo.gob.pe
```

**Evaluación Asignada**
```
Código: NOTIF_EVALUATION_ASSIGNED

Email:
Asunto: Nueva Evaluación Asignada
Cuerpo:
Estimado/a {{jurado.nombres}},

Se le ha asignado {{cantidad}} evaluaciones para la fase de 
{{fase.nombre}} en la convocatoria {{convocatoria.codigo}}.

Plazo de evaluación: {{evaluacion.fecha_limite}}

Ingrese al sistema para iniciar las evaluaciones.

Sistema:
📝 Nuevas evaluaciones asignadas ({{cantidad}})
Plazo: {{evaluacion.fecha_limite}}
```

#### Campos de NotificationPreference
```
- id: UUID
- user_id: UUID (FK)
- notification_template_id: UUID (FK)

// Activación por canal
- system_enabled: boolean
- email_enabled: boolean
- sms_enabled: boolean
- push_enabled: boolean

// Horarios (para emails no urgentes)
- quiet_hours_start: time (nullable)
- quiet_hours_end: time (nullable)

// Frecuencia
- digest_enabled: boolean (agrupar notificaciones)
- digest_frequency: enum (DAILY, WEEKLY) (nullable)

- metadata: jsonb
```

#### Campos de NotificationQueue
```
- id: UUID
- notification_id: UUID (FK)
- channel: enum
- priority: enum
- scheduled_for: timestamp
- attempts: integer (intentos de envío)
- max_attempts: integer (default: 3)
- status: enum (PENDING, PROCESSING, SENT, FAILED)
- last_attempt_at: timestamp (nullable)
- sent_at: timestamp (nullable)
- error_message: text (nullable)
- metadata: jsonb
```

#### Campos de NotificationLog
```
- id: UUID
- notification_id: UUID (FK)
- channel: enum
- status: enum (SENT, DELIVERED, BOUNCED, FAILED, OPENED, CLICKED)
- attempted_at: timestamp
- delivered_at: timestamp (nullable)
- error_code: string (nullable)
- error_message: text (nullable)
- response_data: jsonb (respuesta del proveedor)
- metadata: jsonb
```

#### Eventos que Disparan Notificaciones

**Convocatorias**
- Convocatoria publicada → Usuarios interesados
- Convocatoria actualizada → Postulantes
- Fase próxima → Postulantes y jurados

**Postulaciones**
- Postulación enviada → Postulante
- Postulación en revisión → Postulante
- Postulación APTO/NO APTO → Postulante
- Subsanación requerida → Postulante
- Postulación aprobada → Postulante

**Evaluaciones**
- Evaluación asignada → Jurado
- Plazo próximo a vencer → Jurado
- Evaluación completada → Admin
- Resultados publicados → Postulantes

**Sistema**
- Nuevo mensaje → Destinatario
- Cambio en cronograma → Afectados
- Alerta de seguridad → Administradores

#### Services
- `NotificationService`: Gestión de notificaciones
- `DispatchService`: Despacho de notificaciones
- `ChannelService`: Gestión de canales
- `TemplateService`: Gestión de plantillas
- `PreferenceService`: Preferencias de usuario
- `DigestService`: Resumen de notificaciones

#### Integración con Proveedores

**Email**
```
- Laravel Mail (SMTP)
- SendGrid (opcional)
- Amazon SES (opcional)
```

**SMS**
```
- Twilio
- Vonage (Nexmo)
- Proveedor local
```

**Push Notifications**
```
- Firebase Cloud Messaging (FCM)
- OneSignal
```

#### Events
- `NotificationCreated`
- `NotificationSent`
- `NotificationFailed`
- `NotificationRead`
- `NotificationDeleted`

#### API Expuesta
```php
use Modules\Notification\Services\NotificationService;
use Modules\Notification\Services\DispatchService;
```

---

### 12. Reporting Module

**Responsabilidad**: Generación de reportes y analíticas

#### Entidades Principales
- `Report` (reportes)
- `ReportDefinition` (definiciones)
- `ReportSchedule` (programación)
- `ReportExport` (exportaciones)
- `Dashboard` (dashboards personalizados)
- `Widget` (widgets de dashboard)

#### Campos de ReportDefinition
```
- id: UUID
- code: string (unique, ej: "RPT_CONVOCATORIA_GENERAL")
- name: string
- description: text
- category: enum (CONVOCATORIA, POSTULACION, EVALUACION, USUARIO, SISTEMA)

// Query
- query_type: enum (SQL, ELOQUENT, CUSTOM)
- query_definition: text (SQL o clase)
- parameters: jsonb (parámetros configurables)

// Formato
- default_format: enum (PDF, EXCEL, CSV, HTML, JSON)
- available_formats: jsonb (array)
- template_path: string (nullable)

// Permisos
- required_permission: string
- is_public: boolean (accesible sin autenticación con token)

// Configuración
- cache_ttl: integer (segundos, nullable)
- is_active: boolean

- metadata: jsonb
```

#### Reportes Predefinidos

**1. Reporte General de Convocatoria**
```
Código: RPT_CONVOCATORIA_GENERAL
Incluye:
- Datos de la convocatoria
- Cronograma ejecutado vs planificado
- Perfiles publicados
- Total de postulaciones
- Estadísticas de aprobación
- Vacantes cubiertas/disponibles
- Tiempo total del proceso
```

**2. Reporte de Postulaciones**
```
Código: RPT_POSTULACIONES
Incluye:
- Lista de postulaciones
- Datos demográficos
- Estado de cada postulación
- Documentos presentados
- Condiciones especiales
- Puntajes (si aplicable)
```

**3. Reporte de Evaluaciones**
```
Código: RPT_EVALUACIONES
Incluye:
- Evaluaciones por fase
- Puntajes por criterio
- Estadísticas de puntajes
- Tiempo de evaluación
- Evaluadores participantes
- Distribución de calificaciones
```

**4. Reporte de Jurados**
```
Código: RPT_JURADOS_DESEMPEÑO
Incluye:
- Jurados participantes
- Evaluaciones asignadas/completadas
- Métricas de desempeño
- Puntualidad
- Consistencia en calificaciones
```

**5. Análisis Comparativo Anual**
```
Código: RPT_COMPARATIVO_ANUAL
Incluye:
- Convocatorias por año
- Total de postulaciones
- Tasa de aprobación
- Tiempo promedio de proceso
- Tendencias
```

#### Campos de Report
```
- id: UUID
- report_definition_id: UUID (FK)
- generated_by: UUID (FK User)
- generated_at: timestamp

// Parámetros usados
- parameters: jsonb

// Resultado
- status: enum (PENDING, PROCESSING, COMPLETED, FAILED)
- output_format: enum
- file_path: string (nullable)
- file_size: bigint (nullable)
- row_count: integer (nullable)

// Tiempo
- processing_started_at: timestamp (nullable)
- processing_ended_at: timestamp (nullable)
- processing_time_seconds: integer (nullable)

- error_message: text (nullable)
- metadata: jsonb
```

#### Campos de ReportSchedule
```
- id: UUID
- report_definition_id: UUID (FK)
- scheduled_by: UUID (FK User)

// Programación
- name: string
- frequency: enum (DAILY, WEEKLY, MONTHLY, QUARTERLY, YEARLY)
- day_of_week: integer (nullable, 1-7 para semanal)
- day_of_month: integer (nullable, 1-31 para mensual)
- time_of_day: time (hora de ejecución)
- timezone: string

// Parámetros fijos
- parameters: jsonb

// Destinatarios
- recipients: jsonb (array de user_ids o emails)
- send_via: enum (EMAIL, SYSTEM, BOTH)
- format: enum

// Estado
- is_active: boolean
- last_run_at: timestamp (nullable)
- next_run_at: timestamp (calculado)

- metadata: jsonb
```

#### Campos de Dashboard
```
- id: UUID
- user_id: UUID (FK, nullable, si es personal)
- name: string
- description: text
- is_default: boolean
- is_public: boolean (dashboard compartido)
- layout: jsonb (configuración del layout)
- metadata: jsonb
```

#### Campos de Widget
```
- id: UUID
- dashboard_id: UUID (FK)
- widget_type: enum (METRIC, CHART, TABLE, MAP, CALENDAR)
- title: string
- description: text

// Posición
- position_x: integer
- position_y: integer
- width: integer
- height: integer

// Datos
- data_source: string (código del reporte o query)
- refresh_interval: integer (segundos, nullable)
- parameters: jsonb

// Visualización
- chart_type: enum (LINE, BAR, PIE, DONUT, AREA) (nullable)
- color_scheme: string (nullable)
- configuration: jsonb

- metadata: jsonb
```

#### KPIs Principales

**Dashboard Ejecutivo**
```
1. Convocatorias Activas (contador)
2. Total de Postulaciones (contador + tendencia)
3. Postulaciones por Estado (gráfico de barras)
4. Evaluaciones Pendientes (contador + alerta)
5. Vacantes Cubiertas vs Disponibles (gráfico de dona)
6. Tiempo Promedio de Proceso (métrica + comparación)
7. Tasa de Conversión (embudo)
8. Línea de Tiempo de Convocatorias (calendario)
```

**Dashboard de Postulaciones**
```
1. Postulaciones por Mes (gráfico de línea)
2. Distribución por Género (gráfico de dona)
3. Distribución por Edad (histograma)
4. Distribución Geográfica (mapa)
5. Nivel Educativo (gráfico de barras)
6. Años de Experiencia (gráfico de barras)
7. Condiciones Especiales (gráfico de barras)
8. Tasa de Aptos vs No Aptos (gráfico de dona)
```

**Dashboard de Evaluaciones**
```
1. Evaluaciones Completadas (progreso)
2. Puntajes Promedios por Criterio (radar chart)
3. Distribución de Puntajes (histograma)
4. Comparación entre Evaluadores (gráfico de líneas)
5. Tiempo Promedio de Evaluación (métrica)
6. Ranking Top 10 (tabla)
```

#### Services
- `ReportService`: Gestión de reportes
- `GenerationService`: Generación de reportes
- `ExportService`: Exportación a diferentes formatos
- `ScheduleService`: Reportes programados
- `DashboardService`: Gestión de dashboards
- `AnalyticsService`: Cálculo de métricas

#### Events
- `ReportGenerated`
- `ReportScheduled`
- `ReportFailed`
- `DashboardCreated`
- `WidgetUpdated`

#### API Expuesta
```php
use Modules\Reporting\Services\ReportService;
use Modules\Reporting\Services\DashboardService;
use Modules\Reporting\Services\AnalyticsService;
```

---

### 13. Audit Module

**Responsabilidad**: Auditoría completa y trazabilidad del sistema

#### Entidades Principales
- `AuditLog` (log de auditoría)
- `ActivityLog` (log de actividades)
- `SecurityEvent` (eventos de seguridad)
- `DataChange` (cambios de datos)
- `SystemAccess` (accesos al sistema)

#### Campos de AuditLog
```
- id: UUID
- auditable_type: string (morph, tipo de entidad)
- auditable_id: UUID (morph, id de la entidad)

// Usuario
- user_id: UUID (FK, nullable si es acción del sistema)
- user_type: string (morph, nullable)

// Acción
- event: enum (CREATED, UPDATED, DELETED, VIEWED, EXPORTED, RESTORED)
- action_description: string

// Datos
- old_values: jsonb (valores anteriores)
- new_values: jsonb (valores nuevos)
- changes: jsonb (solo los campos que cambiaron)

// Contexto
- ip_address: inet
- user_agent: text
- url: string
- http_method: string
- tags: jsonb (array, para categorización)

// Metadata
- performed_at: timestamp
- metadata: jsonb
```

#### Implementación con Traits

**HasAudit Trait**
```php
// Agregar a modelos que necesitan auditoría
use Modules\Audit\Traits\HasAudit;

class JobPosting extends Model
{
    use HasAudit;
    
    // Automáticamente registra:
    // - Creación
    // - Actualización (con valores old/new)
    // - Eliminación (soft/hard)
}
```

#### Campos de ActivityLog
```
- id: UUID
- subject_type: string (morph, quién realizó la acción)
- subject_id: UUID (morph)
- causer_type: string (morph, nullable)
- causer_id: UUID (morph, nullable)

- log_name: string (categoría)
- description: text
- properties: jsonb (datos adicionales)

- performed_at: timestamp
```

#### Categorías de Activity Log
```
- authentication: Logins, logouts, password changes
- authorization: Cambios de roles, permisos
- job_posting: Acciones en convocatorias
- application: Acciones en postulaciones
- evaluation: Acciones en evaluaciones
- document: Acciones con documentos
- system: Configuración, backups
```

#### Campos de SecurityEvent
```
- id: UUID
- event_type: enum (LOGIN_ATTEMPT, LOGIN_SUCCESS, LOGIN_FAILED, LOGOUT, PASSWORD_CHANGE, SUSPICIOUS_ACTIVITY, IP_BLOCKED, BRUTE_FORCE, UNAUTHORIZED_ACCESS)
- severity: enum (INFO, WARNING, CRITICAL)

// Usuario
- user_id: UUID (FK, nullable)
- username: string (nullable)
- email: string (nullable)

// Contexto
- ip_address: inet
- user_agent: text
- country_code: string (nullable)
- city: string (nullable)

// Detalles
- description: text
- details: jsonb
- action_taken: text (nullable)

// Alertas
- requires_action: boolean
- action_taken_by: UUID (FK User, nullable)
- action_taken_at: timestamp (nullable)
- action_notes: text (nullable)

- detected_at: timestamp
- metadata: jsonb
```

#### Detección de Actividades Sospechosas

**Reglas de Alerta**
```
1. Múltiples intentos fallidos (> 5 en 15 min)
2. Login desde IP inusual
3. Login desde país diferente
4. Cambios masivos (> 10 registros en 1 min)
5. Eliminaciones múltiples (> 5 en 1 min)
6. Accesos en horarios inusuales (2am-6am)
7. Cambios en configuración crítica
8. Descarga masiva de datos
9. Intentos de acceso no autorizado
10. Cambios de rol/permisos
```

#### Campos de DataChange
```
- id: UUID
- table_name: string
- record_id: UUID
- field_name: string
- old_value: text (nullable)
- new_value: text (nullable)
- change_type: enum (INSERT, UPDATE, DELETE)
- changed_by: UUID (FK User)
- changed_at: timestamp
- reason: text (nullable)
- metadata: jsonb
```

#### Campos de SystemAccess
```
- id: UUID
- user_id: UUID (FK)
- session_id: string
- access_type: enum (WEB, API, MOBILE)

// Inicio de sesión
- login_at: timestamp
- login_ip: inet
- login_user_agent: text
- login_method: enum (PASSWORD, TWO_FACTOR, SSO, TOKEN)

// Actividad
- last_activity_at: timestamp
- requests_count: integer
- actions_performed: jsonb (resumen)

// Cierre de sesión
- logout_at: timestamp (nullable)
- logout_reason: enum (USER, TIMEOUT, FORCED, ERROR) (nullable)

// Duración
- session_duration_seconds: integer (nullable, calculado)

- metadata: jsonb
```

#### Análisis de Auditoría

**Reportes de Auditoría**
```
1. Actividad por usuario
2. Cambios en entidad específica
3. Accesos al sistema
4. Eventos de seguridad
5. Trazabilidad de documento
6. Línea de tiempo de cambios
7. Comparación de versiones
```

#### Retención de Logs
```
- AuditLog: 7 años (normativa)
- ActivityLog: 2 años
- SecurityEvent: 5 años
- DataChange: 7 años
- SystemAccess: 1 año
```

#### Services
- `AuditService`: Gestión de auditoría
- `ActivityService`: Log de actividades
- `SecurityService`: Eventos de seguridad
- `TraceabilityService`: Trazabilidad completa
- `ComplianceService`: Reportes de cumplimiento
- `ForensicsService`: Análisis forense

#### Events
- `AuditLogCreated`
- `SecurityEventDetected`
- `SuspiciousActivityDetected`
- `CriticalChangeDetected`

#### API Expuesta
```php
use Modules\Audit\Services\AuditService;
use Modules\Audit\Services\SecurityService;
use Modules\Audit\Services\TraceabilityService;
```

---

### 14. Configuration Module

**Responsabilidad**: Configuración centralizada del sistema

#### Entidades Principales
- `SystemConfig` (configuración del sistema)
- `ConfigGroup` (grupos de configuración)
- `ConfigHistory` (historial de cambios)

#### Campos de SystemConfig
```
- id: UUID
- config_group_id: UUID (FK)
- key: string (unique)
- value: text
- value_type: enum (STRING, INTEGER, DECIMAL, BOOLEAN, JSON, DATE, DATETIME, TEXT, FILE)
- default_value: text
- description: text

// Validación
- validation_rules: jsonb (reglas de validación Laravel)
- options: jsonb (si es select/enum)
- min_value: decimal (nullable, para numéricos)
- max_value: decimal (nullable, para numéricos)

// UI
- display_name: string
- help_text: text (nullable)
- display_order: integer
- input_type: enum (TEXT, NUMBER, BOOLEAN, SELECT, TEXTAREA, DATE, FILE, COLOR)

// Permisos
- is_public: boolean (visible sin autenticación)
- required_permission: string (nullable)
- is_editable: boolean (puede cambiarse desde UI)
- is_system: boolean (config crítica del sistema)

- metadata: jsonb
```

#### Grupos de Configuración

**1. General**
```
- SYSTEM_NAME: Nombre de la institución
- SYSTEM_LOGO: Logo (URL o file path)
- SYSTEM_FAVICON: Favicon
- SYSTEM_PRIMARY_COLOR: Color primario (hex)
- SYSTEM_SECONDARY_COLOR: Color secundario
- SYSTEM_TIMEZONE: Zona horaria
- SYSTEM_LOCALE: Idioma (es, en)
- CONTACT_EMAIL: Email de contacto
- CONTACT_PHONE: Teléfono
- CONTACT_ADDRESS: Dirección
- SOCIAL_FACEBOOK: URL Facebook
- SOCIAL_TWITTER: URL Twitter
- SOCIAL_LINKEDIN: URL LinkedIn
```

**2. Proceso**
```
- DEFAULT_APPLICATION_DEADLINE_DAYS: Días de plazo (ej: 15)
- DEFAULT_AMENDMENT_DEADLINE_DAYS: Días para subsanar (ej: 3)
- DEFAULT_APPEAL_DEADLINE_DAYS: Días para recursos (ej: 3)
- MAX_APPLICATIONS_PER_USER: Límite de postulaciones (ej: 5)
- MAX_EVALUATIONS_PER_JURY: Límite de evaluaciones (ej: 20)
- AUTO_GENERATE_JOB_CODE: Generación automática de códigos
- JOB_CODE_PREFIX: Prefijo de códigos (ej: "CONV")
- APPLICATION_CODE_PREFIX: Prefijo de postulaciones (ej: "APP")
```

**3. Documentos**
```
- MAX_FILE_SIZE_MB: Tamaño máximo de archivo (ej: 10)
- ALLOWED_DOCUMENT_TYPES: Tipos permitidos (json: ["pdf","docx","jpg"])
- DOCUMENT_STORAGE_DRIVER: Driver de storage (local, s3)
- DOCUMENT_RETENTION_YEARS: Años de retención (ej: 7)
- REQUIRE_DIGITAL_SIGNATURE: Requerir firma digital (boolean)
- AUTO_DELETE_OLD_DOCUMENTS: Eliminar automáticamente (boolean)
```

**4. Notificaciones**
```
- NOTIFICATIONS_ENABLED: Activar notificaciones (boolean)
- EMAIL_FROM_ADDRESS: Email remitente
- EMAIL_FROM_NAME: Nombre remitente
- EMAIL_FOOTER_TEXT: Pie de email
- SMS_PROVIDER: Proveedor SMS (twilio, vonage)
- SMS_SENDER_ID: ID de remitente SMS
- PUSH_ENABLED: Notificaciones push (boolean)
- PUSH_PROVIDER: Proveedor push (fcm, onesignal)
```

**5. Seguridad**
```
- SESSION_LIFETIME: Duración de sesión (minutos, ej: 120)
- PASSWORD_MIN_LENGTH: Longitud mínima (ej: 8)
- PASSWORD_REQUIRE_UPPERCASE: Requerir mayúsculas (boolean)
- PASSWORD_REQUIRE_NUMBERS: Requerir números (boolean)
- PASSWORD_REQUIRE_SYMBOLS: Requerir símbolos (boolean)
- TWO_FACTOR_ENABLED: 2FA habilitado (boolean)
- TWO_FACTOR_MANDATORY: 2FA obligatorio (boolean)
- MAX_LOGIN_ATTEMPTS: Intentos máximos (ej: 5)
- LOCKOUT_DURATION_MINUTES: Duración de bloqueo (ej: 15)
- IP_WHITELIST_ENABLED: Lista blanca de IPs (boolean)
- IP_WHITELIST: IPs permitidas (json array)
```

**6. Integrations**
```
- RENIEC_API_ENABLED: Integración RENIEC (boolean)
- RENIEC_API_KEY: Key de RENIEC
- SUNAT_API_ENABLED: Integración SUNAT (boolean)
- SUNAT_API_KEY: Key de SUNAT
- SMTP_HOST: Host SMTP
- SMTP_PORT: Puerto SMTP
- SMTP_USERNAME: Usuario SMTP
- SMTP_PASSWORD: Password SMTP (encrypted)
```

**7. Reports**
```
- DEFAULT_REPORT_FORMAT: Formato por defecto (pdf, excel)
- REPORT_LOGO_PATH: Logo para reportes
- REPORT_FOOTER_TEXT: Pie de página
- CACHE_REPORTS: Cachear reportes (boolean)
- REPORT_CACHE_TTL: TTL de caché (segundos)
```

**8. Audit**
```
- AUDIT_ENABLED: Auditoría habilitada (boolean)
- AUDIT_RETENTION_DAYS: Días de retención (ej: 2555 = 7 años)
- LOG_QUERIES: Registrar queries SQL (boolean)
- LOG_FAILED_LOGINS: Registrar intentos fallidos (boolean)
- SECURITY_ALERTS_ENABLED: Alertas de seguridad (boolean)
- SECURITY_ALERT_EMAIL: Email para alertas
```

#### Acceso a Configuración

**Desde el código**
```php
use Modules\Configuration\Facades\Config;

// Obtener valor
$systemName = Config::get('SYSTEM_NAME');
$maxSize = Config::get('MAX_FILE_SIZE_MB', 10); // con default

// Establecer valor
Config::set('SYSTEM_NAME', 'Nueva Municipalidad');

// Verificar si existe
if (Config::has('SOME_KEY')) {
    // ...
}

// Obtener grupo completo
$generalConfig = Config::group('general');
```

#### Campos de ConfigHistory
```
- id: UUID
- system_config_id: UUID (FK)
- old_value: text
- new_value: text
- changed_by: UUID (FK User)
- changed_at: timestamp
- change_reason: text (nullable)
- ip_address: inet
- metadata: jsonb
```

#### Services
- `ConfigService`: Gestión de configuración
- `CacheService`: Caché de configuración
- `ValidationService`: Validación de valores
- `MigrationService`: Migración de configuraciones

#### Events
- `ConfigUpdated`
- `ConfigCacheCleared`
- `CriticalConfigChanged`

#### API Expuesta
```php
use Modules\Configuration\Services\ConfigService;
use Modules\Configuration\Facades\Config;
```

---

## 🔀 MÓDULOS TRANSVERSALES

### Consideraciones Cross-Cutting

#### Caché
```
- User roles/permissions (1 hora)
- Configuration (hasta cambio manual)
- Reports (según configuración)
- Organizational structure (1 hora)
- Templates (hasta cambio manual)
```

#### Queues
```
- Envío de emails (queue: notifications)
- Envío de SMS (queue: notifications)
- Generación de reportes (queue: reports)
- Procesamiento de documentos (queue: documents)
- Cálculo de rankings (queue: evaluations)
```

#### Jobs Programados
```
- Limpiar sesiones expiradas (diario)
- Enviar notificaciones programadas (cada 5 min)
- Generar reportes programados (según schedule)
- Actualizar métricas (cada hora)
- Verificar certificados expirados (diario)
- Archivar convocatorias antiguas (mensual)
- Limpiar caché antiguo (semanal)
```

---

## 🔗 RELACIONES ENTRE MÓDULOS

### Diagrama de Dependencias

```
┌──────────────────────────────────────────────────────┐
│                      CORE                            │
│  (Base Models, Traits, Services, Helpers)            │
└──────────────────────────────────────────────────────┘
                        ▲
                        │
        ┌───────────────┼───────────────┐
        │               │               │
   ┌────▼───┐     ┌────▼───┐     ┌────▼────┐
   │  AUTH  │     │  USER  │     │ AUDIT   │
   └────┬───┘     └────┬───┘     └────┬────┘
        │              │              │
        └──────┬───────┴──────┬───────┘
               │              │
        ┌──────▼──────┐   ┌──▼──────────┐
        │ ORGANIZATION│   │CONFIGURATION│
        └──────┬──────┘   └─────────────┘
               │
    ┌──────────┼──────────┐
    │          │          │
┌───▼───┐  ┌──▼──────┐ ┌─▼──────┐
│PROFILE│  │POSTING  │ │  JURY  │
└───┬───┘  └──┬──────┘ └─┬──────┘
    │         │          │
    └────┬────┴────┬─────┘
         │         │
      ┌──▼─────────▼──┐
      │ APPLICATION   │
      └──┬───────────┬┘
         │           │
    ┌────▼───┐   ┌──▼────────┐
    │DOCUMENT│   │EVALUATION │
    └────────┘   └──┬────────┘
                    │
            ┌───────┴────────┐
            │                │
      ┌─────▼─────┐   ┌─────▼──────┐
      │NOTIFICATION│   │ REPORTING  │
      └───────────┘   └────────────┘
```

### Comunicación entre Módulos

**Events & Listeners**
```
- Los módulos se comunican mediante eventos
- Evitar dependencias directas cuando sea posible
- Usar Event Sourcing para acciones críticas
```

**Service Contracts**
```
- Definir interfaces para servicios expuestos
- Implementar en cada módulo
- Inyección de dependencias vía Service Provider
```

**Shared Models**
```
- User es compartido (módulo User)
- OrganizationalUnit es compartido (módulo Organization)
- Documentos pueden ser adjuntos en múltiples módulos
```

---

## 📐 PATRONES Y CONVENCIONES

### Estructura de un Módulo

```
Modules/
└── NombreModulo/
    ├── Config/
    │   └── config.php
    ├── Console/
    │   └── Commands/
    ├── Database/
    │   ├── Migrations/
    │   ├── Seeders/
    │   └── Factories/
    ├── Entities/ (Models)
    │   ├── NombreEntidad.php
    │   └── Relations/
    ├── Http/
    │   ├── Controllers/
    │   ├── Middleware/
    │   ├── Requests/
    │   └── Resources/
    ├── Providers/
    │   ├── NombreModuloServiceProvider.php
    │   └── RouteServiceProvider.php
    ├── Repositories/
    │   ├── Contracts/
    │   └── Eloquent/
    ├── Routes/
    │   ├── api.php
    │   └── web.php
    ├── Services/
    │   ├── NombreService.php
    │   └── Contracts/
    ├── Events/
    ├── Listeners/
    ├── Policies/
    ├── Traits/
    ├── ValueObjects/
    ├── DTOs/
    ├── Enums/
    ├── Exceptions/
    ├── Tests/
    │   ├── Unit/
    │   └── Feature/
    ├── Resources/
    │   ├── views/
    │   ├── lang/
    │   └── assets/
    └── module.json
```

### Naming Conventions

**Entidades (Models)**
```
- Singular
- PascalCase
- Sufijo: ninguno
Ejemplo: User, Application, JobPosting
```

**Services**
```
- PascalCase
- Sufijo: Service
Ejemplo: ApplicationService, EvaluationService
```

**Repositories**
```
- PascalCase
- Sufijo: Repository
Ejemplo: UserRepository, JobPostingRepository
```

**Controllers**
```
- PascalCase
- Sufijo: Controller
Ejemplo: ApplicationController, EvaluationController
```

**Requests**
```
- PascalCase
- Sufijo: Request
Ejemplo: StoreApplicationRequest, UpdateProfileRequest
```

**Resources**
```
- PascalCase
- Sufijo: Resource
Ejemplo: UserResource, ApplicationResource
```

**Jobs**
```
- PascalCase
- Verbo en infinitivo
Ejemplo: SendNotification, GenerateReport
```

**Events**
```
- PascalCase
- Tiempo pasado
Ejemplo: ApplicationSubmitted, EvaluationCompleted
```

**Listeners**
```
- PascalCase
- Iniciar con verbo
Ejemplo: SendApplicationConfirmation, NotifyJuryAssigned
```

### Repository Pattern

**Interface (Contrato)**
```php
namespace Modules\Application\Repositories\Contracts;

interface ApplicationRepositoryInterface
{
    public function findByCode(string $code);
    public function getByVacancy(string $vacancyId);
    public function getEligible();
    public function updateStatus(string $id, string $status);
}
```

**Implementación**
```php
namespace Modules\Application\Repositories\Eloquent;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    protected $model;
    
    public function __construct(Application $model)
    {
        $this->model = $model;
    }
    
    // Implementación de métodos...
}
```

**Registro en Service Provider**
```php
$this->app->bind(
    ApplicationRepositoryInterface::class,
    ApplicationRepository::class
);
```

### Service Layer

**Service con Repository**
```php
namespace Modules\Application\Services;

class ApplicationService
{
    protected $repository;
    protected $eligibilityService;
    protected $notificationService;
    
    public function __construct(
        ApplicationRepositoryInterface $repository,
        EligibilityService $eligibilityService,
        NotificationService $notificationService
    ) {
        $this->repository = $repository;
        $this->eligibilityService = $eligibilityService;
        $this->notificationService = $notificationService;
    }
    
    public function submit(array $data): Application
    {
        DB::beginTransaction();
        try {
            $application = $this->repository->create($data);
            
            event(new ApplicationSubmitted($application));
            
            DB::commit();
            return $application;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### Value Objects

**Ejemplo: DNI**
```php
namespace Modules\Core\ValueObjects;

class DNI
{
    private string $value;
    
    public function __construct(string $value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException('DNI inválido');
        }
        $this->value = $value;
    }
    
    private function isValid(string $value): bool
    {
        return preg_match('/^\d{8}$/', $value);
    }
    
    public function toString(): string
    {
        return $this->value;
    }
}
```

### DTOs (Data Transfer Objects)

**Ejemplo: FilterDTO**
```php
namespace Modules\Core\DTOs;

class FilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?array $filters = [],
        public readonly ?string $sortBy = null,
        public readonly ?string $sortDirection = 'asc',
        public readonly int $perPage = 15
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            search: $request->input('search'),
            filters: $request->input('filters', []),
            sortBy: $request->input('sort_by'),
            sortDirection: $request->input('sort_direction', 'asc'),
            perPage: $request->input('per_page', 15)
        );
    }
}
```

### Enums

**Usando Enum PHP 8.1+**
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
            self::IN_PROCESS => 'En Proceso',
            self::FINALIZED => 'Finalizada',
            self::CANCELLED => 'Cancelada',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'blue',
            self::IN_PROCESS => 'yellow',
            self::FINALIZED => 'green',
            self::CANCELLED => 'red',
        };
    }
    
    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::DRAFT => in_array($status, [self::PUBLISHED, self::CANCELLED]),
            self::PUBLISHED => in_array($status, [self::IN_PROCESS, self::CANCELLED]),
            self::IN_PROCESS => in_array($status, [self::FINALIZED, self::CANCELLED]),
            self::FINALIZED => false,
            self::CANCELLED => false,
        };
    }
}
```

### Event Sourcing

**Evento**
```php
namespace Modules\Application\Events;

class ApplicationSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Application $application
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->application->applicant_id),
            new PrivateChannel('admin.applications'),
        ];
    }
}
```

**Listener**
```php
namespace Modules\Application\Listeners;

class SendApplicationConfirmation
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    public function handle(ApplicationSubmitted $event): void
    {
        $this->notificationService->send(
            $event->application->applicant,
            'application_submitted',
            [
                'application' => $event->application,
                'code' => $event->application->code,
            ]
        );
    }
}
```

### API Resources

**Resource Simple**
```php
namespace Modules\Application\Http\Resources;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'application_date' => $this->application_date->toISOString(),
            'vacancy' => new VacancyResource($this->whenLoaded('vacancy')),
            'applicant' => new UserResource($this->whenLoaded('applicant')),
            'documents_count' => $this->whenCounted('documents'),
            'scores' => $this->when($this->isEvaluated(), [
                'curriculum' => $this->curriculum_score,
                'interview' => $this->interview_score,
                'final' => $this->final_score,
            ]),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```

**Resource Collection**
```php
namespace Modules\Application\Http\Resources;

class ApplicationCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => ApplicationResource::collection($this->collection),
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
            ],
            'filters' => $request->only(['status', 'search', 'sort_by']),
        ];
    }
}
```

### Form Requests

**Request de Validación**
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
            'documents.*.type' => ['required', 'exists:document_types,id'],
            'documents.*.file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'vacancy_id.required' => 'Debe seleccionar una vacante',
            'vacancy_id.exists' => 'La vacante seleccionada no existe',
            'terms_accepted.accepted' => 'Debe aceptar los términos y condiciones',
            'documents.required' => 'Debe adjuntar documentos',
            'documents.min' => 'Debe adjuntar al menos 3 documentos',
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

**Policy de Autorización**
```php
namespace Modules\Application\Policies;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('application.view.any');
    }
    
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            || $user->hasPermissionTo('application.view.all');
    }
    
    public function create(User $user): bool
    {
        return $user->hasRole('APPLICANT')
            && $user->active_applications_count < config('jobposting.max_applications_per_user');
    }
    
    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            && $application->status === ApplicationStatus::DRAFT;
    }
    
    public function delete(User $user, Application $application): bool
    {
        return $user->hasPermissionTo('application.delete')
            && !$application->isInEvaluation();
    }
    
    public function withdraw(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            && in_array($application->status, [
                ApplicationStatus::SUBMITTED,
                ApplicationStatus::IN_REVIEW
            ]);
    }
}
```

---

## 🚀 ROADMAP DE IMPLEMENTACIÓN

### Fase 1: Fundación (Semanas 1-3)

**Semana 1: Setup y Core**
```
✓ Configurar Laravel 11 + nwidart/laravel-modules
✓ Configurar base de datos PostgreSQL
✓ Configurar Redis para caché y queues
✓ Módulo Core:
  - BaseModel, Traits, Helpers
  - Exceptions personalizadas
  - Value Objects básicos
```

**Semana 2: Auth y User**
```
✓ Módulo Auth:
  - Autenticación
  - Roles y Permisos (Spatie)
  - Middleware
✓ Módulo User:
  - CRUD de usuarios
  - Perfiles
  - Preferencias
```

**Semana 3: Organization y Configuration**
```
✓ Módulo Organization:
  - Estructura jerárquica
  - Closure table
✓ Módulo Configuration:
  - Sistema de configuración
✓ Módulo Audit (básico):
  - Logging de auditoría
```

### Fase 2: Core Business (Semanas 4-8)

**Semana 4: JobPosting**
```
✓ Módulo JobPosting:
  - CRUD de convocatorias
  - Estados y transiciones
  - Fases del proceso
```

**Semana 5: JobProfile**
```
✓ Módulo JobProfile:
  - Solicitud de perfiles
  - Flujo de revisión
  - Códigos de posición
  - Criterios de evaluación
```

**Semana 6: Application (Parte 1)**
```
✓ Módulo Application:
  - Postulaciones
  - Gestión de documentos
  - Verificación de elegibilidad
```

**Semana 7: Application (Parte 2)**
```
✓ Condiciones especiales
✓ Subsanación
✓ Cálculo de bonificaciones
```

**Semana 8: Jury**
```
✓ Módulo Jury:
  - Asignación de jurados
  - Capacitaciones
  - Conflictos de interés
```

### Fase 3: Evaluación (Semanas 9-11)

**Semana 9: Evaluation (Parte 1)**
```
✓ Módulo Evaluation:
  - Asignación de evaluadores
  - Proceso de evaluación
  - Calificación por criterios
```

**Semana 10: Evaluation (Parte 2)**
```
✓ Evaluación colaborativa
✓ Cálculo de puntajes
✓ Rankings
```

**Semana 11: Appeals**
```
✓ Sistema de recursos
✓ Revisión y resolución
✓ Recálculo de rankings
```

### Fase 4: Documentos y Firma (Semanas 12-14)

**Semana 12: Document (Parte 1)**
```
✓ Módulo Document:
  - Gestión de plantillas
  - Generación de documentos
  - Storage
```

**Semana 13: Document (Parte 2) - Firma Digital**
```
✓ PKI Infrastructure
✓ Certificados digitales
✓ Proceso de firma
✓ Verificación de firmas
```

**Semana 14: Document (Parte 3)**
```
✓ Integración con PDF
✓ Firmas visuales
✓ Trazabilidad de documentos
✓ Testing exhaustivo
```

### Fase 5: Soporte (Semanas 15-17)

**Semana 15: Notification**
```
✓ Módulo Notification:
  - Sistema de notificaciones
  - Plantillas
  - Multi-canal (Email, SMS, Push)
  - Preferencias de usuario
```

**Semana 16: Reporting**
```
✓ Módulo Reporting:
  - Definiciones de reportes
  - Generación dinámica
  - Dashboards
  - Exportación
```

**Semana 17: Audit (Completo)**
```
✓ Completar módulo Audit:
  - Activity Log
  - Security Events
  - Trazabilidad completa
  - Reportes de compliance
```

### Fase 6: Testing y Optimización (Semanas 18-20)

**Semana 18: Testing**
```
✓ Unit Tests (>80% coverage)
✓ Feature Tests
✓ Integration Tests
✓ API Tests
```

**Semana 19: Performance**
```
✓ Optimización de queries
✓ Índices de base de datos
✓ Caché estratégico
✓ Queue optimization
```

**Semana 20: Security**
```
✓ Security audit
✓ Penetration testing
✓ OWASP compliance
✓ Data encryption
```

### Fase 7: Frontend y UX (Semanas 21-24)

**Semana 21-22: Admin Panel**
```
✓ Dashboard administrativo
✓ Gestión de convocatorias
✓ Gestión de postulaciones
✓ Panel de evaluación
```

**Semana 23: Applicant Portal**
```
✓ Portal de postulante
✓ Búsqueda de convocatorias
✓ Postulación
✓ Seguimiento
```

**Semana 24: Jury Portal**
```
✓ Portal de jurado
✓ Evaluaciones asignadas
✓ Proceso de calificación
✓ Reportes
```

### Fase 8: Deployment y Go-Live (Semanas 25-26)

**Semana 25: Staging**
```
✓ Deploy a staging
✓ User Acceptance Testing
✓ Bug fixes
✓ Performance tuning
```

**Semana 26: Production**
```
✓ Deploy a producción
✓ Capacitación de usuarios
✓ Monitoreo
✓ Soporte post-lanzamiento
```

---

## 🔧 HERRAMIENTAS Y LIBRERÍAS

### Backend Core
```
- laravel/framework: ^11.0
- nwidart/laravel-modules: ^11.0
- spatie/laravel-permission: ^6.0
- spatie/laravel-activitylog: ^4.0
- spatie/laravel-query-builder: ^5.0
```

### Base de Datos
```
- doctrine/dbal: ^3.0 (para migraciones avanzadas)
- staudenmeir/laravel-adjacency-list: ^1.0 (para árboles)
```

### Firma Digital
```
- phpseclib/phpseclib: ^3.0 (criptografía)
- tecnickcom/tcpdf: ^6.0 (PDF con firma)
- setasign/fpdi: ^2.0 (manipulación de PDF)
```

### Documentos
```
- barryvdh/laravel-dompdf: ^2.0
- maatwebsite/excel: ^3.1
- phpoffice/phpword: ^1.0
```

### Notificaciones
```
- laravel/slack-notification-channel: ^3.0
- laravel-notification-channels/telegram: ^5.0
- guzzlehttp/guzzle: ^7.0 (para APIs)
```

### Queue y Jobs
```
- predis/predis: ^2.0
- laravel/horizon: ^5.0 (monitoreo de queues)
```

### Testing
```
- phpunit/phpunit: ^10.0
- pestphp/pest: ^2.0
- pestphp/pest-plugin-laravel: ^2.0
- mockery/mockery: ^1.0
- fakerphp/faker: ^1.0
```

### Dev Tools
```
- laravel/telescope: ^5.0 (debugging)
- barryvdh/laravel-debugbar: ^3.0
- laravel/pint: ^1.0 (code style)
- larastan/larastan: ^2.0 (static analysis)
```

### Frontend (Opcional)
```
- inertiajs/inertia-laravel: ^1.0
- tightenco/ziggy: ^2.0 (rutas en JS)
- Laravel Livewire: ^3.0 (alternativa)
```

---

## 📊 ESTRUCTURA DE BASE DE DATOS

### Esquema General

```sql
-- Core Tables
users
roles
permissions
role_user
permission_role

-- Organization
organizational_units
organizational_unit_closure
organizational_unit_assignments

-- Job Posting
job_postings
job_posting_statuses
process_phases
job_posting_schedules
job_posting_phase_statuses
job_posting_history

-- Job Profile
job_profile_requests
job_profile_statuses
job_profile_vacancies
position_codes
evaluation_criteria
job_profile_history

-- Application
applications
application_statuses
application_documents
document_types
special_conditions
condition_types
application_status_history

-- Evaluation
evaluations
evaluation_details
evaluator_assignments
evaluation_history
appeals

-- Jury
jury_assignments
jury_trainings
jury_performance
conflict_of_interests

-- Document
document_templates
generated_documents
digital_signatures
signature_certificates
document_audits

-- Notification
notifications
notification_templates
notification_preferences
notification_queue
notification_logs

-- Reporting
report_definitions
reports
report_schedules
report_exports
dashboards
widgets

-- Audit
audit_logs
activity_logs
security_events
data_changes
system_accesses

-- Configuration
config_groups
system_configs
config_history
```

### Índices Importantes

**Performance Crítico**
```sql
-- Applications
CREATE INDEX idx_applications_vacancy ON applications(job_profile_vacancy_id);
CREATE INDEX idx_applications_applicant ON applications(applicant_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_code ON applications(code);

-- Evaluations
CREATE INDEX idx_evaluations_application ON evaluations(application_id);
CREATE INDEX idx_evaluations_evaluator ON evaluations(evaluator_id);
CREATE INDEX idx_evaluations_phase ON evaluations(process_phase_id);
CREATE INDEX idx_evaluations_status ON evaluations(status);

-- Audit
CREATE INDEX idx_audit_logs_auditable ON audit_logs(auditable_type, auditable_id);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_date ON audit_logs(performed_at);

-- Documents
CREATE INDEX idx_documents_documentable ON generated_documents(documentable_type, documentable_id);
CREATE INDEX idx_documents_number ON generated_documents(document_number);

-- Organizational Units (para closure table)
CREATE INDEX idx_org_closure_ancestor ON organizational_unit_closure(ancestor_id);
CREATE INDEX idx_org_closure_descendant ON organizational_unit_closure(descendant_id);
```

### Full-Text Search

```sql
-- Para búsqueda de postulaciones
CREATE INDEX idx_applications_search ON applications 
USING GIN(to_tsvector('spanish', coalesce(code, '') || ' ' || coalesce(notes, '')));

-- Para búsqueda de convocatorias
CREATE INDEX idx_job_postings_search ON job_postings 
USING GIN(to_tsvector('spanish', coalesce(title, '') || ' ' || coalesce(description, '')));
```

---

## 🔐 SEGURIDAD

### Checklist de Seguridad

**Autenticación y Autorización**
```
✓ Password hashing (bcrypt)
✓ 2FA opcional
✓ Rate limiting en login
✓ Bloqueo por intentos fallidos
✓ Session timeout
✓ CSRF protection
✓ Políticas de contraseña fuertes
```

**Datos Sensibles**
```
✓ Encriptación de datos en reposo
✓ Encriptación en tránsito (HTTPS/TLS)
✓ Encriptación de backups
✓ Sanitización de inputs
✓ Protección contra SQL injection
✓ Protección contra XSS
```

**API Security**
```
✓ Token-based authentication (Sanctum)
✓ API rate limiting
✓ CORS configurado correctamente
✓ Validación estricta de payloads
✓ Versionado de API
```

**Auditoría**
```
✓ Log de todos los accesos
✓ Log de cambios críticos
✓ Alertas de seguridad
✓ Monitoreo en tiempo real
✓ Retención de logs según normativa
```

**Firma Digital**
```
✓ Certificados X.509
✓ Algoritmos seguros (RSA-SHA256)
✓ Timestamp authority
✓ Validación de certificados
✓ Revocación de certificados
✓ Protección de llaves privadas
```

---

## 📈 MONITOREO Y OBSERVABILIDAD

### Métricas a Monitorear

**Performance**
```
- Response time (p50, p95, p99)
- Database query time
- Queue processing time
- Cache hit rate
- API throughput
```

**Negocio**
```
- Convocatorias activas
- Postulaciones por día/semana/mes
- Tasa de conversión
- Tiempo promedio de proceso
- Evaluaciones completadas vs pendientes
```

**Sistema**
```
- CPU usage
- Memory usage
- Disk usage
- Queue size
- Failed jobs
- Error rate
```

**Seguridad**
```
- Failed login attempts
- Suspicious activities
- Certificate expirations
- Unusual access patterns
```

### Herramientas

```
- Laravel Telescope (desarrollo)
- Laravel Horizon (queues)
- Sentry (error tracking)
- New Relic / DataDog (APM)
- Prometheus + Grafana (métricas)
- ELK Stack (logs)
```

---

## 🧪 ESTRATEGIA DE TESTING

### Pirámide de Testing

```
        /\
       /E2E\         10% - End to End
      /------\
     /Feature\       20% - Feature Tests
    /----------\
   /  Unit Tests\    70% - Unit Tests
  /--------------\
```

### Unit Tests

**Qué testear:**
```
✓ Value Objects
✓ DTOs
✓ Enums
✓ Helpers
✓ Services (lógica de negocio)
✓ Repositories
✓ Validaciones
✓ Cálculos (puntajes, bonificaciones)
```

**Ejemplo:**
```php
test('can calculate final score with bonus', function () {
    $application = Application::factory()->create([
        'curriculum_score' => 85.00
    ]);
    
    $application->specialConditions()->create([
        'condition_type_id' => ConditionType::DISABILITY,
        'bonus_percentage' => 15.00
    ]);
    
    $service = app(ApplicationService::class);
    $finalScore = $service->calculateFinalScore($application);
    
    expect($finalScore)->toBe(97.75); // 85 + (85 * 0.15) = 97.75
});
```

### Feature Tests

**Qué testear:**
```
✓ HTTP endpoints
✓ Flujos completos
✓ Autenticación y autorización
✓ Validaciones de requests
✓ Respuestas de API
✓ Eventos y listeners
```

**Ejemplo:**
```php
test('applicant can submit application', function () {
    $user = User::factory()->applicant()->create();
    $vacancy = JobProfileVacancy::factory()->available()->create();
    
    actingAs($user)
        ->post('/api/applications', [
            'vacancy_id' => $vacancy->id,
            'terms_accepted' => true,
            'documents' => [
                ['type' => 'DOC_DNI', 'file' => UploadedFile::fake()->create('dni.pdf')],
                ['type' => 'DOC_CV', 'file' => UploadedFile::fake()->create('cv.pdf')],
            ]
        ])
        ->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'code', 'status']]);
        
    assertDatabaseHas('applications', [
        'applicant_id' => $user->id,
        'status' => ApplicationStatus::SUBMITTED
    ]);
});
```

### Integration Tests

**Qué testear:**
```
✓ Interacción entre módulos
✓ Flujos de trabajo completos
✓ Transacciones de base de datos
✓ Jobs y queues
✓ Notificaciones
✓ Generación de documentos
```

### Coverage Goal

```
Objetivo: > 80% de cobertura
Crítico: Services, Repositories, Controllers
Medio: Models, Events, Listeners
Bajo: Migrations, Seeders, Config
```

---

## 📚 DOCUMENTACIÓN

### Documentación Requerida

**1. Documentación Técnica**
```
- README.md (setup, instalación)
- ARCHITECTURE.md (este documento)
- API.md (documentación de API)
- DATABASE.md (esquema de BD)
- DEPLOYMENT.md (guía de despliegue)
```

**2. Documentación de Usuario**
```
- Manual de Usuario (Postulante)
- Manual de Usuario (Jurado)
- Manual de Administrador
- FAQs
```

**3. Documentación de API**
```
- Swagger/OpenAPI spec
- Postman collection
- Ejemplos de uso
- Rate limits y autenticación
```

**4. Diagramas**
```
- Diagrama de arquitectura
- Diagrama de base de datos (ERD)
- Diagramas de flujo
- Diagramas de secuencia
```

---

## 🎓 MEJORES PRÁCTICAS

### Desarrollo

1. **Usar Interfaces y Contratos**
   - Facilita testing y desacoplamiento
   - Permite cambiar implementaciones

2. **Implementar Repository Pattern**
   - Abstrae la capa de datos
   - Facilita cambios en el ORM

3. **Service Layer**
   - Lógica de negocio en Services
   - Controllers delgados
   - Reutilización de código

4. **Event-Driven Architecture**
   - Desacoplar módulos con eventos
   - Side effects en listeners
   - Fácil agregar funcionalidad

5. **Inmutabilidad cuando sea posible**
   - Value Objects inmutables
   - DTOs readonly (PHP 8.1+)

6. **Type Hinting estricto**
   - Usar tipos en todos los parámetros
   - declare(strict_types=1)

### Base de Datos

1. **Usar UUIDs en lugar de auto-increment**
   - Mejor seguridad
   - Fácil replicación

2. **Soft Deletes por defecto**
   - Permite recuperación
   - Mantiene integridad referencial

3. **Índices adecuados**
   - En foreign keys
   - En campos de búsqueda frecuente
   - En campos de ordenamiento

4. **Usar transacciones**
   - Para operaciones críticas
   - Mantener consistencia

### API

1. **RESTful Design**
   - Usar verbos HTTP correctamente
   - Recursos en plural
   - Respuestas consistentes

2. **Versionado**
   - /api/v1/...
   - Mantener compatibilidad

3. **Rate Limiting**
   - Proteger contra abuso
   - Diferentes límites por rol

4. **Paginación**
   - Siempre paginar listados
   - Metadata de paginación

### Seguridad

1. **Nunca confiar en input del usuario**
   - Validar todo
   - Sanitizar datos

2. **Principio de mínimo privilegio**
   - Dar solo permisos necesarios
   - Revisar permisos regularmente

3. **Auditar acciones críticas**
   - Cambios de estado
   - Modificaciones de datos sensibles

4. **Encriptar datos sensibles**
   - Contraseñas (bcrypt)
   - Tokens
   - Datos personales críticos

---

## 🚦 CRITERIOS DE ACEPTACIÓN

### Por Módulo

Cada módulo debe cumplir:

```
✓ Tests con > 80% coverage
✓ Documentación completa
✓ Code style (PSR-12)
✓ Sin errores de Larastan (level 5+)
✓ API documentada (si aplica)
✓ Migrations con seeders
✓ Events y Listeners documentados
✓ Políticas de autorización
✓ Validaciones exhaustivas
```

### Sistema Completo

```
✓ Todos los módulos integrados
✓ Flujos end-to-end funcionando
✓ Performance < 200ms (p95)
✓ Zero downtime deployment
✓ Backups automatizados
✓ Monitoreo configurado
✓ Documentación completa
✓ UAT aprobado
✓ Security audit pasado
```

---

## 📞 SOPORTE Y MANTENIMIENTO

### Post-Lanzamiento

**Semana 1-4:**
```
- Monitoreo intensivo 24/7
- Soporte inmediato
- Bug fixes críticos
- Performance tuning
```

**Mes 2-3:**
```
- Soporte en horario laboral
- Bug fixes prioritarios
- Mejoras menores
- Feedback de usuarios
```

**Mes 4+:**
```
- Mantenimiento regular
- Nuevas features
- Actualizaciones de seguridad
- Optimizaciones
```

### SLA Sugeridos

```
- Crítico (sistema caído): 1 hora
- Alto (funcionalidad crítica): 4 horas
- Medio (funcionalidad no crítica): 1 día
- Bajo (mejora, cosmético): 1 semana
```

---

## 🎯 CONCLUSIÓN

Este documento define la arquitectura modular del Sistema de Convocatorias usando Laravel y nwidart/laravel-modules. La arquitectura propuesta es:

✅ **Modular**: Módulos independientes y reutilizables
✅ **Escalable**: Fácil agregar funcionalidad sin afectar existente
✅ **Mantenible**: Código organizado por dominio
✅ **Testeable**: Alta cobertura de tests
✅ **Segura**: Múltiples capas de seguridad incluida firma digital
✅ **Auditable**: Trazabilidad completa
✅ **Performante**: Optimizada para alto volumen

### Próximos Pasos

1. **Revisar y aprobar esta arquitectura**
2. **Setup del proyecto base**
3. **Comenzar Fase 1 del roadmap**
4. **Iteraciones semanales con revisión**
5. **Deployment continuo a staging**

---

**Documento versión:** 1.0  
**Última actualización:** 2025  
**Mantenido por:** Equipo de Desarrollo