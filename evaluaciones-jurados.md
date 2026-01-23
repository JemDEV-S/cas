# Sistema de Evaluaciones y Jurados - CAS

## Índice
1. [Resumen del Sistema](#resumen-del-sistema)
2. [Fases de Evaluación](#fases-de-evaluación)
3. [Evaluación Curricular (CV)](#evaluación-curricular-cv)
4. [Evaluación de Entrevista](#evaluación-de-entrevista)
5. [Módulo Jury - Gestión de Jurados](#módulo-jury---gestión-de-jurados)
6. [Módulo Evaluation - Sistema de Evaluación](#módulo-evaluation---sistema-de-evaluación)
7. [Conflictos de Interés](#conflictos-de-interés)
8. [Cálculo de Puntajes](#cálculo-de-puntajes)

---

## Resumen del Sistema

El sistema de evaluación CAS consta de **dos fases evaluativas** con un puntaje total de **100 puntos**:

| Fase | Puntaje Máximo | Puntaje Mínimo Aprobatorio |
|------|----------------|---------------------------|
| Evaluación Curricular (CV) | 50 pts | 35 pts |
| Entrevista Personal | 50 pts | - |
| **TOTAL** | **100 pts** | - |

**Flujo:** El postulante debe obtener mínimo **35 puntos en CV** para pasar a la fase de Entrevista.

---

## Fases de Evaluación

| Fase | Código | Descripción | Requiere Jurado |
|------|--------|-------------|-----------------|
| 2 | CV_EVALUATION | Evaluación Curricular | Sí |
| 3 | INTERVIEW | Entrevista Personal | Sí |

---

## Evaluación Curricular (CV)

**Puntaje:** Máximo 50 puntos | Mínimo aprobatorio: 35 puntos

Los criterios están asociados a un `position_code_id` específico según el tipo de puesto.

### Estructura de Criterios CV

| Criterio | Mín | Máx | Composición |
|----------|-----|-----|-------------|
| Formación Académica | 17 | 25 | Base (17) + Adicionales (hasta 8) |
| Experiencia Específica | 18 | 25 | Base (18) + Adicionales (hasta 7) |
| **TOTAL CV** | **35** | **50** | |

### Sistema de Puntos Adicionales

Los puntos adicionales son **acumulables** (se suman todos los que el postulante cumple).

**Formato en base de datos:**
```json
{
  "is_cumulative": true,
  "additional_points": [
    { "value": 4, "description": "Egresado de Maestría" },
    { "value": 4, "description": "Grado de Magister" }
  ]
}
```

### Criterios por Tipo de Puesto

#### ESP_I - Profesional Especialista

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Maestría (+4), Grado Magíster (+4) |
| Experiencia Específica | 18 | >1 año <2 años gestión pública (+3), >2 años gestión pública (+4) |

#### PP_I, PP_II - Profesional de Planta I/II

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudio Maestría (+3), Egresado Maestría (+5) |
| Experiencia Específica | 18 | >1 año <2 años gestión pública (+3), >2-3 años gestión pública (+4) |

#### PP_III - Profesional de Planta III

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudio Maestría (+3), Egresado Maestría (+5) |
| Experiencia Específica | 18 | >6 meses <1 año gestión pública (+3), >1 año gestión pública (+4) |

#### AA_I - Asistente Administrativo

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Título Profesional (+4), Estudio Maestría (+4) |
| Experiencia Específica | 18 | >6 meses <1 año gestión pública (+3), >1 año gestión pública (+4) |

#### TEC_I - Técnico Administrativo

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Bachiller/Título Técnico (+4), Título Universitario (+4) |
| Experiencia Específica | 18 | >1 año <2 años exp. general (+3), >2 años exp. general (+4) |

#### TEC_II - Técnico de Soporte

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Bachiller/Título Técnico (+8) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+3), >1 año exp. general (+4) |

#### AAI - Auxiliar I

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Universitario/Técnico (+4), Bachiller (+4) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+7) |

*Nota: Experiencia general desde culminación secundaria.*

#### AAII - Auxiliar II

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Egresado Universitario/Técnico (+8) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+4), >1 año exp. general (+3) |

*Nota: Experiencia general desde culminación secundaria.*

#### AAIII - Auxiliar III

| Criterio | Base | Adicionales |
|----------|------|-------------|
| Formación Académica | 17 | Estudios Universitarios/Técnicos (+7) |
| Experiencia Específica | 18 | >6 meses <1 año exp. general (+8) |

---

## Evaluación de Entrevista

**Puntaje:** Máximo 50 puntos

Los criterios de entrevista son **genéricos** (sin `position_code_id`), aplicables a todos los puestos.

### Criterios de Entrevista

| Criterio | Mín | Máx | Descripción |
|----------|-----|-----|-------------|
| Dominio y conocimiento de las funciones del puesto | 0 | 12.50 | Comprensión de funciones y responsabilidades del cargo |
| Grado de Análisis | 0 | 12.50 | Capacidad de analizar situaciones, resolver problemas y tomar decisiones |
| Ética y Actitud | 0 | 12.50 | Valores, principios y disposición hacia el trabajo |
| Comunicación | 0 | 12.50 | Capacidad de expresarse de manera clara, coherente y profesional |
| **TOTAL ENTREVISTA** | **0** | **50** | |

---

## Módulo Jury - Gestión de Jurados

**Ubicación:** `Modules/Jury/`

### Entidades del Módulo

#### 1. JuryMember (Miembro de Jurado)

Representa a un usuario habilitado para ser jurado evaluador.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| user_id | FK | Usuario asociado |
| specialty | string | Especialidad del jurado |
| years_of_experience | int | Años de experiencia |
| professional_title | string | Título profesional |
| is_active | boolean | Estado activo |
| is_available | boolean | Disponibilidad actual |
| unavailable_from/until | date | Período de no disponibilidad |
| training_completed | boolean | Capacitación completada |
| max_concurrent_assignments | int | Máximo de asignaciones simultáneas |
| total_evaluations | int | Total de evaluaciones realizadas |
| consistency_score | decimal | Puntaje de consistencia |
| average_rating | decimal | Calificación promedio |

**Requisitos para evaluar:**
- `is_active = true`
- `is_available = true`
- `training_completed = true`
- No estar sobrecargado (asignaciones < max_concurrent_assignments)

#### 2. JuryAssignment (Asignación a Convocatoria)

Asigna un miembro de jurado a una convocatoria específica.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| jury_member_id | FK | Miembro de jurado |
| job_posting_id | FK | Convocatoria |
| member_type | enum | TITULAR / SUPLENTE |
| role_in_jury | enum | Rol dentro del jurado |
| status | enum | Estado de la asignación |
| assignment_resolution | string | Documento de resolución |
| max_evaluations | int | Máximo de evaluaciones asignables |
| current_evaluations | int | Evaluaciones actuales |
| completed_evaluations | int | Evaluaciones completadas |

### Enums del Módulo Jury

#### MemberType (Tipo de Miembro)
| Valor | Label | Descripción |
|-------|-------|-------------|
| TITULAR | Titular | Miembro titular del jurado |
| SUPLENTE | Suplente | Miembro suplente del jurado |

#### JuryRole (Rol en el Jurado)
| Valor | Label | Tiene Autoridad |
|-------|-------|-----------------|
| PRESIDENTE | Presidente | Sí |
| SECRETARIO | Secretario | Sí |
| VOCAL | Vocal | No |
| MIEMBRO | Miembro | No |

#### AssignmentStatus (Estado de Asignación)
| Valor | Label | Puede Evaluar |
|-------|-------|---------------|
| ACTIVE | Activo | Sí |
| REPLACED | Reemplazado | No |
| EXCUSED | Excusado | No |
| REMOVED | Removido | No |
| SUSPENDED | Suspendido | No |

---

## Módulo Evaluation - Sistema de Evaluación

**Ubicación:** `Modules/Evaluation/`

### Entidades del Módulo

#### 1. Evaluation (Evaluación)

Registro principal de una evaluación realizada por un jurado a una postulación.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| uuid | uuid | Identificador único |
| application_id | FK | Postulación evaluada |
| evaluator_id | FK | Jurado evaluador |
| phase_id | FK | Fase del proceso (CV/Entrevista) |
| job_posting_id | FK | Convocatoria |
| status | enum | Estado de la evaluación |
| total_score | decimal(5,2) | Puntaje total |
| max_possible_score | decimal(5,2) | Puntaje máximo posible |
| percentage | decimal(5,2) | Porcentaje obtenido |
| submitted_at | timestamp | Fecha de envío |
| deadline_at | timestamp | Fecha límite |
| is_anonymous | boolean | Evaluación anónima |
| is_collaborative | boolean | Evaluación colaborativa |
| general_comments | text | Comentarios generales |
| modified_by | FK | Modificado por (si aplica) |
| modification_reason | text | Razón de modificación |

#### 2. EvaluationCriterion (Criterio de Evaluación)

Define los criterios que se evalúan en cada fase.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| uuid | uuid | Identificador único |
| phase_id | FK | Fase del proceso |
| job_posting_id | FK/null | Convocatoria (null = genérico) |
| code | string | Código del criterio |
| name | string | Nombre del criterio |
| description | text | Descripción/guía |
| min_score | decimal(5,2) | Puntaje mínimo |
| max_score | decimal(5,2) | Puntaje máximo |
| weight | decimal(5,2) | Peso/ponderación |
| order | int | Orden de presentación |
| requires_comment | boolean | Requiere comentario obligatorio |
| requires_evidence | boolean | Requiere evidencia |
| score_type | enum | Tipo de puntaje |
| is_active | boolean | Estado activo |

#### 3. EvaluationDetail (Detalle de Evaluación)

Puntaje otorgado por criterio.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| uuid | uuid | Identificador único |
| evaluation_id | FK | Evaluación padre |
| criterion_id | FK | Criterio evaluado |
| score | decimal(5,2) | Puntaje otorgado |
| weighted_score | decimal(5,2) | Puntaje ponderado (auto-calculado) |
| comments | text | Comentarios del evaluador |
| evidence | text | Evidencia/sustento |
| version | int | Versión (para historial) |

#### 4. EvaluatorAssignment (Asignación de Evaluador)

Asigna un evaluador a una postulación específica.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| uuid | uuid | Identificador único |
| evaluator_id | FK | Jurado asignado (JuryMember) |
| application_id | FK | Postulación a evaluar |
| phase_id | FK | Fase del proceso |
| job_posting_id | FK | Convocatoria |
| assignment_type | string | MANUAL / AUTOMATIC |
| assigned_by | FK | Usuario que asignó |
| status | enum | Estado de la asignación |
| workload_weight | int | Peso de carga de trabajo |
| deadline_at | timestamp | Fecha límite |
| has_conflict | boolean | Tiene conflicto de interés |
| is_available | boolean | Evaluador disponible |

### Enums del Módulo Evaluation

#### EvaluationStatusEnum (Estado de Evaluación)
| Valor | Label | Puede Editar | Completada |
|-------|-------|--------------|------------|
| ASSIGNED | Asignada | Sí | No |
| IN_PROGRESS | En Progreso | Sí | No |
| SUBMITTED | Enviada | No | Sí |
| MODIFIED | Modificada | No | Sí |
| CANCELLED | Cancelada | No | No |

**Flujo de estados:**
```
ASSIGNED → IN_PROGRESS → SUBMITTED
                              ↓
                         MODIFIED (si se requiere corrección)
```

#### AssignmentStatusEnum (Estado de Asignación de Evaluador)
| Valor | Label | Activo |
|-------|-------|--------|
| PENDING | Pendiente | Sí |
| IN_PROGRESS | En Progreso | Sí |
| COMPLETED | Completada | No |
| CANCELLED | Cancelada | No |
| REASSIGNED | Reasignada | No |

---

## Conflictos de Interés

### Entidad: JuryConflict

Registra conflictos de interés entre jurados y postulantes.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| jury_member_id | FK | Jurado involucrado |
| application_id | FK | Postulación afectada |
| applicant_id | FK | Postulante |
| conflict_type | enum | Tipo de conflicto |
| severity | enum | Severidad |
| status | enum | Estado del conflicto |
| description | text | Descripción del conflicto |
| is_self_reported | boolean | Auto-reportado |
| action_taken | string | Acción tomada |
| resolution | text | Resolución |

### ConflictType (Tipos de Conflicto)

| Tipo | Label | Severidad Recomendada |
|------|-------|----------------------|
| FAMILY | Familiar | CRITICAL |
| LABOR | Laboral | HIGH |
| PROFESSIONAL | Profesional | MEDIUM |
| FINANCIAL | Financiero | CRITICAL |
| PERSONAL | Personal | HIGH |
| ACADEMIC | Académico | HIGH |
| PRIOR_EVALUATION | Evaluación Previa | MEDIUM |
| OTHER | Otro | MEDIUM |

### ConflictSeverity (Severidad)

| Nivel | Label | Requiere Acción Inmediata |
|-------|-------|--------------------------|
| LOW | Baja | No |
| MEDIUM | Media | No |
| HIGH | Alta | Sí |
| CRITICAL | Crítica | Sí |

### ConflictStatus (Estado del Conflicto)

| Estado | Label | Pendiente |
|--------|-------|-----------|
| REPORTED | Reportado | Sí |
| UNDER_REVIEW | En Revisión | Sí |
| CONFIRMED | Confirmado | Sí |
| DISMISSED | Desestimado | No |
| RESOLVED | Resuelto | No |

**Flujo de estados:**
```
REPORTED → UNDER_REVIEW → CONFIRMED → RESOLVED
              ↓
          DISMISSED
```

**Acciones posibles al resolver:**
- `NO_ACTION` - Sin acción requerida
- `EXCUSED` - Jurado excusado de la evaluación
- `REASSIGNED` - Evaluación reasignada a otro jurado

---

## Cálculo de Puntajes

### Fórmula General

```
PUNTAJE_CV = Formación_Académica + Experiencia_Específica
           = (base_FA + adicionales_FA) + (base_EE + adicionales_EE)
           = (17 + [0-8]) + (18 + [0-7])
           = 35 a 50 puntos

PUNTAJE_ENTREVISTA = Dominio + Análisis + Ética + Comunicación
                   = 12.50 + 12.50 + 12.50 + 12.50
                   = 0 a 50 puntos

PUNTAJE_FINAL = PUNTAJE_CV + PUNTAJE_ENTREVISTA
              = 0 a 100 puntos
```

### Puntaje Ponderado (EvaluationDetail)

El sistema calcula automáticamente el `weighted_score` al guardar:
```php
weighted_score = score × criterion.weight
```

### Ejemplo de Cálculo

**Postulante a ESP_I:**

| Criterio | Base | Adicional | Total |
|----------|------|-----------|-------|
| Formación Académica | 17 | +4 (Egresado Maestría) | 21 |
| Experiencia Específica | 18 | +4 (>2 años gestión pública) | 22 |
| **TOTAL CV** | | | **43** |

| Criterio Entrevista | Puntaje |
|---------------------|---------|
| Dominio y conocimiento | 10.00 |
| Grado de Análisis | 11.50 |
| Ética y Actitud | 12.00 |
| Comunicación | 11.00 |
| **TOTAL ENTREVISTA** | **44.50** |

**PUNTAJE FINAL: 43 + 44.50 = 87.50 puntos**

### Bonificaciones Especiales

Se aplican sobre el puntaje final:

| Condición | Bonificación |
|-----------|--------------|
| Discapacidad | +15% |
| Licenciado FFAA | +10% |
| Deportista Nacional | +10% |
| Deportista Internacional | +15% |
| Víctima de Terrorismo | +10% |

```
puntaje_con_bonificacion = min(puntaje_final × (1 + bonificacion), 100)
```

---

## Estructura de Archivos (nwidart/laravel-modules)

### Módulo Jury
```
Modules/Jury/
├── app/
│   ├── Entities/
│   │   ├── JuryMember.php
│   │   ├── JuryAssignment.php
│   │   ├── JuryConflict.php
│   │   └── JuryHistory.php
│   ├── Enums/
│   │   ├── MemberType.php
│   │   ├── JuryRole.php
│   │   ├── AssignmentStatus.php
│   │   ├── ConflictType.php
│   │   ├── ConflictSeverity.php
│   │   └── ConflictStatus.php
│   ├── Services/
│   │   ├── JuryAssignmentService.php
│   │   ├── JuryMemberService.php
│   │   ├── ConflictDetectionService.php
│   │   └── WorkloadBalancerService.php
│   └── Http/Controllers/
├── database/migrations/
└── routes/
```

### Módulo Evaluation
```
Modules/Evaluation/
├── app/
│   ├── Entities/
│   │   ├── Evaluation.php
│   │   ├── EvaluationCriterion.php
│   │   ├── EvaluationDetail.php
│   │   ├── EvaluatorAssignment.php
│   │   └── EvaluationHistory.php
│   ├── Enums/
│   │   ├── EvaluationStatusEnum.php
│   │   ├── AssignmentStatusEnum.php
│   │   └── ScoreTypeEnum.php
│   ├── Services/
│   │   ├── EvaluationService.php
│   │   └── EvaluatorAssignmentService.php
│   ├── Events/
│   │   ├── EvaluationAssigned.php
│   │   ├── EvaluationSubmitted.php
│   │   ├── EvaluationModified.php
│   │   └── EvaluationDeadlineApproaching.php
│   └── Http/Controllers/
├── database/migrations/
└── routes/
```

---

## Relaciones entre Módulos

```
User (App\Models\User)
    ↓
JuryMember (1:1)
    ↓
JuryAssignment (1:N) ←→ JobPosting
    ↓
EvaluatorAssignment (1:N) ←→ Application
    ↓
Evaluation (1:N)
    ↓
EvaluationDetail (1:N) ←→ EvaluationCriterion
```

---

## Vistas Implementadas (Blade + Alpine.js)

### Stack Frontend
- **Templates:** Blade (Laravel)
- **Interactividad:** Alpine.js
- **Estilos:** Tailwind CSS
- **Iconos:** Font Awesome
- **Layout base:** `layouts.app`

### Módulo Jury - Vistas

| Vista | Ruta | Estado | Descripción |
|-------|------|--------|-------------|
| `members/index.blade.php` | `/jury-members` | Implementada | Lista de jurados con filtros, stats cards, barra de carga |
| `members/create.blade.php` | `/jury-members/create` | Implementada | Formulario crear jurado |
| `members/show.blade.php` | `/jury-members/{id}` | Implementada | Detalle de jurado |
| `members/edit.blade.php` | `/jury-members/{id}/edit` | Falta | Editar jurado |
| `assignments/index.blade.php` | `/jury-assignments` | Implementada | Lista asignaciones + modal asignación automática |
| `assignments/show.blade.php` | `/jury-assignments/{id}` | Falta | Detalle de asignación |
| `conflicts/index.blade.php` | `/jury-conflicts` | Implementada | Lista conflictos con filtros + modal reportar |
| `conflicts/show.blade.php` | `/jury-conflicts/{id}` | Falta | Detalle y resolución de conflicto |

**Componentes Alpine.js en Jury:**
- `deleteMember(id)` - Eliminar jurado con confirmación
- `reviewConflict(id)` - Mover conflicto a revisión
- Modal Bootstrap para asignación automática
- Modal Bootstrap para reportar conflicto

### Módulo Evaluation - Vistas

| Vista | Ruta | Estado | Descripción |
|-------|------|--------|-------------|
| `index.blade.php` | `/evaluation` | Implementada | Dashboard de evaluación |
| `my-evaluations.blade.php` | `/evaluation/my-evaluations` | Implementada | Evaluaciones asignadas al jurado actual |
| `evaluations/evaluate.blade.php` | `/evaluation/{id}/evaluate` | Implementada | Formulario de evaluación con auto-guardado |
| `evaluations/show.blade.php` | `/evaluation/{id}` | Falta | Ver evaluación completada |
| `criteria/index.blade.php` | `/evaluation-criteria` | Implementada | Lista criterios por fase con resumen |
| `criteria/create.blade.php` | `/evaluation-criteria/create` | Implementada | Crear criterio |
| `criteria/edit.blade.php` | `/evaluation-criteria/{id}/edit` | Falta | Editar criterio |
| `criteria/show.blade.php` | `/evaluation-criteria/{id}` | Falta | Detalle de criterio |
| `assignments/index.blade.php` | `/evaluator-assignments` | Implementada | Lista asignaciones de evaluadores |
| `automatic/index.blade.php` | `/evaluation/automatic` | Implementada | Evaluación automática |
| `automatic/show.blade.php` | `/evaluation/automatic/{id}` | Implementada | Detalle evaluación automática |
| `automatic/progress.blade.php` | `/evaluation/automatic/progress` | Implementada | Progreso de evaluación |

**Componentes JavaScript en Evaluation:**
```javascript
// evaluate.blade.php - Auto-guardado
calculateWeightedScores()     // Calcula puntajes ponderados en tiempo real
saveCriterionDetail()         // Guarda detalle via API (axios)
// Auto-save después de 1 segundo de inactividad
// Barra de progreso de criterios evaluados
// Sidebar sticky con resumen
```

### Patrones de UI Comunes

**Stats Cards:**
```html
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center">
            <div class="bg-indigo-100 rounded-lg p-3">
                <i class="fas fa-icon text-indigo-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Label</p>
                <p class="text-2xl font-semibold">Value</p>
            </div>
        </div>
    </div>
</div>
```

**Badges de Estado:**
```html
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Activo
</span>
```

**Barra de Progreso:**
```html
<div class="w-full bg-gray-200 rounded-full h-2">
    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
</div>
```

---

## Lista de Tareas - Pendientes y Mejoras

### Alta Prioridad

| # | Tarea | Módulo | Tipo | Descripción |
|---|-------|--------|------|-------------|
| 1 | Vista `members/edit.blade.php` | Jury | Crear | Formulario edición de jurado |
| 2 | Vista `assignments/show.blade.php` | Jury | Crear | Detalle de asignación con historial |
| 3 | Vista `conflicts/show.blade.php` | Jury | Crear | Detalle conflicto + acciones (confirmar, desestimar, resolver) |
| 4 | Vista `evaluations/show.blade.php` | Evaluation | Crear | Ver evaluación completada (solo lectura) |
| 5 | Vista `criteria/edit.blade.php` | Evaluation | Crear | Editar criterio existente |
| 6 | Vista `criteria/show.blade.php` | Evaluation | Crear | Detalle de criterio con estadísticas |
| 7 | Modal asignación automática funcional | Jury | Mejorar | Conectar con WorkloadBalancerService |
| 8 | Validación completa en evaluate.blade.php | Evaluation | Mejorar | Validar todos los criterios antes de enviar |

### Media Prioridad

| # | Tarea | Módulo | Tipo | Descripción |
|---|-------|--------|------|-------------|
| 9 | Migrar modales Bootstrap a Alpine.js | Ambos | Refactor | Usar x-data, @click, x-show para modales |
| 10 | Componente reutilizable de filtros | Ambos | Crear | `<x-filters>` con slots para campos |
| 11 | Componente de tabla con ordenamiento | Ambos | Crear | `<x-data-table>` con Alpine.js |
| 12 | Dashboard de jurado | Jury | Crear | Vista principal del jurado con KPIs |
| 13 | Historial de evaluaciones | Evaluation | Crear | Vista con todas las evaluaciones por jurado |
| 14 | Notificaciones en tiempo real | Ambos | Implementar | Usar Laravel Echo + eventos |
| 15 | Exportar evaluaciones a PDF | Evaluation | Crear | Generar acta de evaluación |
| 16 | Bulk actions en tablas | Ambos | Crear | Selección múltiple + acciones masivas |

### Baja Prioridad

| # | Tarea | Módulo | Tipo | Descripción |
|---|-------|--------|------|-------------|
| 17 | Dark mode | Ambos | Mejorar | Soporte para tema oscuro |
| 18 | Responsive mejorado | Ambos | Mejorar | Optimizar para móviles |
| 19 | Skeleton loaders | Ambos | Mejorar | Mostrar placeholders mientras carga |
| 20 | Tooltips informativos | Ambos | Mejorar | Agregar ayuda contextual |
| 21 | Keyboard shortcuts | Evaluation | Crear | Atajos para evaluar más rápido |
| 22 | Gráficos de rendimiento | Ambos | Crear | Charts con distribución de puntajes |

### Correcciones Identificadas

| # | Bug/Corrección | Archivo | Descripción |
|---|----------------|---------|-------------|
| 1 | Color dinámico no funciona | `members/index.blade.php:193` | `bg-{{ $color }}` no compila en Tailwind |
| 2 | Falta CSRF en fetch | `members/index.blade.php:258` | Ya tiene, verificar en otros |
| 3 | Modal sin Alpine.js | `assignments/index.blade.php` | Usa Bootstrap, migrar a Alpine |
| 4 | Select sin opciones | `assignments/index.blade.php:198-206` | Falta poblar convocatorias/fases |
| 5 | Axios no declarado | `evaluate.blade.php` | Verificar que axios esté incluido |
| 6 | Route no definida | `my-evaluations.blade.php:160` | `evaluation.show` puede no existir |

### Mejoras de Backend Relacionadas

| # | Tarea | Descripción |
|---|-------|-------------|
| 1 | API para asignación automática | Endpoint `POST /api/jury/auto-assign` |
| 2 | Validar conflictos al asignar | Verificar JuryConflict antes de EvaluatorAssignment |
| 3 | Calcular ranking automático | Trigger al completar todas las evaluaciones de una fase |
| 4 | Notificar deadline próximo | Job programado para enviar recordatorios |
| 5 | Auditoría de cambios | Registrar modificaciones en evaluaciones |

---

## Ejemplos de Código Alpine.js

### Modal con Alpine.js
```html
<div x-data="{ open: false }">
    <button @click="open = true">Abrir Modal</button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50">
        <div class="bg-black bg-opacity-50 absolute inset-0" @click="open = false"></div>
        <div class="bg-white rounded-lg p-6 relative z-10 max-w-md mx-auto mt-20">
            <h3>Título del Modal</h3>
            <button @click="open = false">Cerrar</button>
        </div>
    </div>
</div>
```

### Formulario con validación Alpine.js
```html
<form x-data="{
    score: '',
    isValid: false,
    validate() {
        this.isValid = this.score >= 0 && this.score <= 100;
    }
}" @submit.prevent="isValid && $el.submit()">
    <input type="number" x-model="score" @input="validate()">
    <button :disabled="!isValid" :class="{ 'opacity-50': !isValid }">
        Guardar
    </button>
</form>
```

### Auto-save con debounce
```html
<div x-data="{
    value: '',
    saving: false,
    saved: false,
    save() {
        this.saving = true;
        fetch('/api/save', {
            method: 'POST',
            body: JSON.stringify({ value: this.value })
        }).then(() => {
            this.saving = false;
            this.saved = true;
            setTimeout(() => this.saved = false, 2000);
        });
    }
}">
    <input x-model.debounce.500ms="value" @input="save()">
    <span x-show="saving">Guardando...</span>
    <span x-show="saved" class="text-green-600">Guardado</span>
</div>
```

---

**Extraído de:** cas-info.md + evaluation_criteria.json + Modules/Jury + Modules/Evaluation
**Versión:** 3.0
**Fecha:** 2025
