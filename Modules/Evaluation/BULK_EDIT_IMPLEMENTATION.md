# Documentación de Implementación: Edición Masiva de Evaluaciones

## 📋 Resumen Ejecutivo

Esta documentación detalla la implementación de una nueva funcionalidad de **edición masiva de evaluaciones** para el módulo de Evaluación del sistema CAS. Esta funcionalidad permitirá a los administradores ver y modificar todos los puntajes de evaluación de todas las postulaciones de una convocatoria y fase específica en una interfaz tipo Excel, con guardado automático y filtros inteligentes.

## 🎯 Objetivo

Crear una interfaz administrativa donde:
1. Se selecciona un **JobPosting** (convocatoria)
2. Se selecciona una **ProcessPhase** (fase del proceso)
3. Se cargan todas las evaluaciones de todas las postulaciones con sus puntajes
4. Los puntajes se pueden modificar directamente en una tabla editable
5. Los cambios se guardan automáticamente al salir del campo
6. Se muestran indicadores visuales de guardado
7. Se incluyen filtros inteligentes para búsqueda y visualización

## 🏗️ Contexto del Sistema Actual

### Estructura de Base de Datos

#### Tabla: `evaluations`
```sql
- id (bigint, PK)
- uuid (string, unique)
- evaluator_assignment_id (FK nullable)
- application_id (FK to applications, UUID)
- evaluator_id (FK to users, UUID)
- phase_id (FK to process_phases, UUID)
- job_posting_id (FK to job_postings, UUID)
- status (enum: ASSIGNED, IN_PROGRESS, SUBMITTED, MODIFIED, CANCELLED)
- total_score (decimal 8,2, nullable)
- max_possible_score (decimal 8,2, nullable)
- percentage (decimal 8,2, nullable)
- submitted_at (timestamp, nullable)
- deadline_at (timestamp, nullable)
- general_comments (text, nullable)
- internal_notes (text, nullable)
- modified_by (FK to users, UUID, nullable)
- modified_at (timestamp, nullable)
- modification_reason (text, nullable)
- metadata (json, nullable)
- timestamps, soft_deletes
```

#### Tabla: `evaluation_details`
```sql
- id (bigint, PK)
- uuid (string, unique)
- evaluation_id (FK to evaluations, bigint)
- criterion_id (FK to evaluation_criteria, bigint)
- score (decimal 8,2) -- El puntaje que editaremos
- weighted_score (decimal 8,2, nullable)
- comments (text, nullable)
- evidence (text, nullable)
- version (integer, default 1)
- change_reason (text, nullable)
- metadata (json, nullable)
- timestamps, soft_deletes
- UNIQUE KEY: (evaluation_id, criterion_id)
```

#### Tabla: `evaluation_criteria`
```sql
- id (bigint, PK)
- uuid (string, unique)
- phase_id (FK to process_phases, UUID)
- job_posting_id (FK to job_postings, UUID, nullable)
- position_code_id (FK to position_codes, UUID, nullable)
- code (string 50, unique)
- name (string)
- description (text, nullable)
- min_score (decimal 8,2, default 0)
- max_score (decimal 8,2)
- weight (decimal 8,2, default 1)
- order (integer, default 0)
- requires_comment (boolean, default false)
- requires_evidence (boolean, default false)
- score_type (enum: NUMERIC, PERCENTAGE, QUALITATIVE)
- is_active (boolean, default true)
- is_system (boolean, default false)
- timestamps, soft_deletes
```

#### Tabla: `evaluation_history`
```sql
- id (bigint, PK)
- evaluation_id (FK to evaluations)
- user_id (FK to users, UUID)
- action (string: CREATED, UPDATED, SUBMITTED, MODIFIED, CRITERION_CHANGED, etc.)
- description (text)
- old_values (json, nullable)
- new_values (json, nullable)
- reason (text, nullable)
- timestamps
```

### Modelos Eloquent Existentes

#### `Modules\Evaluation\Entities\Evaluation`
- Relaciones: `application()`, `evaluator()`, `phase()`, `jobPosting()`, `details()`, `history()`
- Métodos importantes: `updateScores()`, `submit()`, `canEdit()`, `isCompleted()`
- Scopes: `byEvaluator()`, `byPhase()`, `byStatus()`, `pending()`, `completed()`

#### `Modules\Evaluation\Entities\EvaluationDetail`
- Relaciones: `evaluation()`, `criterion()`
- Métodos importantes: `calculateWeightedScore()`, `validateScore()`
- **IMPORTANTE**: Tiene eventos en boot() que actualizan automáticamente:
  - `saving`: Calcula weighted_score
  - `saved`: Actualiza total_score de la evaluación padre
  - `deleted`: Actualiza total_score de la evaluación padre

#### `Modules\Evaluation\Entities\EvaluationCriterion`
- Relaciones: `phase()`, `jobPosting()`, `positionCode()`, `details()`
- Métodos importantes: `validateScore()`, `calculateWeightedScore()`
- Scopes: `active()`, `byPhase()`, `byJobPosting()`, `byPositionCode()`, `ordered()`

#### `Modules\Evaluation\Entities\EvaluationHistory`
- Método estático: `logChange(evaluation_id, user_id, action, description, old_values, new_values, reason)`

### Servicio Existente

#### `Modules\Evaluation\Services\EvaluationService`
Métodos relevantes:
- `saveEvaluationDetail(Evaluation $evaluation, array $detailData)`: Guarda o actualiza un detalle de evaluación
- `modifySubmittedEvaluation(Evaluation $evaluation, array $data, string $reason)`: Modifica una evaluación ya enviada
- Registra cambios en `evaluation_history` automáticamente

## 📐 Especificaciones Técnicas

### Requisitos Funcionales

1. **Selección de Contexto**
   - Página inicial con selectores de JobPosting y ProcessPhase
   - Carga dinámica de fases según convocatoria seleccionada
   - Botón "Cargar Evaluaciones" que redirige a la vista de edición

2. **Vista de Edición Masiva**
   - Tabla estilo Excel con todas las evaluaciones
   - **Filas**: Cada postulación (application)
   - **Columnas**:
     - Información fija: Nombre completo, DNI, Position Code, Estado de evaluación
     - Columnas editables: Un input por cada criterio de evaluación
     - Columna de acciones: Ver detalles, ver CV
   - Campos de puntaje editables inline (input type="number")
   - Validación de rango (min_score - max_score) en tiempo real

3. **Guardado Automático**
   - Evento: `blur` (al salir del campo)
   - Indicadores visuales:
     - Spinner mientras guarda
     - Check verde si guarda exitosamente
     - X roja si hay error
     - Mensaje de error debajo del campo si falla validación
   - Request AJAX a endpoint dedicado
   - No permitir editar otro campo hasta que se complete el guardado actual

4. **Filtros Inteligentes**
   - Por postulante (nombre/DNI): búsqueda en tiempo real
   - Por rango de puntaje: slider o inputs min/max
   - Por estado de evaluación: select multiple (SUBMITTED, MODIFIED, IN_PROGRESS, etc.)
   - Filtros persistentes en la URL (query params)
   - Botón "Limpiar filtros"

5. **Auditoría y Seguridad**
   - Cada cambio registrado en `evaluation_history`
   - Usuario autenticado como `modified_by`
   - Permiso requerido: `assign-evaluators`
   - Solo permitir edición de evaluaciones en estado SUBMITTED o MODIFIED

### Requisitos Técnicos

1. **Frontend**: Alpine.js + Blade
2. **Backend**: Laravel (Controllers, Services, Resources)
3. **Estilos**: Tailwind CSS (ya presente en el proyecto)
4. **Validación**: Cliente y servidor
5. **Performance**: Paginación de resultados (50 por página)

## 🗂️ Estructura de Archivos a Crear

```
Modules/Evaluation/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── BulkEditEvaluationController.php (NUEVO)
│   │   ├── Requests/
│   │   │   ├── BulkEditScoreRequest.php (NUEVO)
│   │   │   └── LoadBulkEditDataRequest.php (NUEVO)
│   │   └── Resources/
│   │       └── BulkEditEvaluationResource.php (NUEVO)
│   └── Services/
│       └── (MODIFICAR) EvaluationService.php
│           └── Agregar: bulkUpdateScore()
├── resources/
│   └── views/
│       └── bulk-edit/
│           ├── index.blade.php (NUEVO - Selección de JobPosting/Phase)
│           └── edit.blade.php (NUEVO - Tabla editable)
└── routes/
    └── (MODIFICAR) web.php
        └── Agregar rutas para bulk-edit
```

## 📝 Implementación Detallada

### 1. Crear Request de Validación: BulkEditScoreRequest

**Ruta**: `Modules/Evaluation/app/Http/Requests/BulkEditScoreRequest.php`

```php
<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Evaluation\Entities\{Evaluation, EvaluationCriterion};

class BulkEditScoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign-evaluators');
    }

    public function rules(): array
    {
        return [
            'evaluation_id' => ['required', 'exists:evaluations,id'],
            'criterion_id' => ['required', 'exists:evaluation_criteria,id'],
            'score' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $evaluation = Evaluation::find($this->evaluation_id);
            $criterion = EvaluationCriterion::find($this->criterion_id);

            if ($evaluation && !in_array($evaluation->status->value, ['SUBMITTED', 'MODIFIED'])) {
                $validator->errors()->add('evaluation_id', 'Solo se pueden editar evaluaciones completadas.');
            }

            if ($criterion && !$criterion->validateScore($this->score)) {
                $validator->errors()->add('score', "El puntaje debe estar entre {$criterion->min_score} y {$criterion->max_score}");
            }
        });
    }

    public function messages(): array
    {
        return [
            'evaluation_id.required' => 'La evaluación es requerida',
            'evaluation_id.exists' => 'La evaluación no existe',
            'criterion_id.required' => 'El criterio es requerido',
            'criterion_id.exists' => 'El criterio no existe',
            'score.required' => 'El puntaje es requerido',
            'score.numeric' => 'El puntaje debe ser un número',
            'score.min' => 'El puntaje no puede ser negativo',
        ];
    }
}
```

### 2. Crear Request de Validación: LoadBulkEditDataRequest

**Ruta**: `Modules/Evaluation/app/Http/Requests/LoadBulkEditDataRequest.php`

```php
<?php

namespace Modules\Evaluation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadBulkEditDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assign-evaluators');
    }

    public function rules(): array
    {
        return [
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
            'phase_id' => ['required', 'exists:process_phases,uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_posting_id.required' => 'Debe seleccionar una convocatoria',
            'job_posting_id.exists' => 'La convocatoria seleccionada no existe',
            'phase_id.required' => 'Debe seleccionar una fase',
            'phase_id.exists' => 'La fase seleccionada no existe',
        ];
    }
}
```

### 3. Crear Resource: BulkEditEvaluationResource

**Ruta**: `Modules/Evaluation/app/Http/Resources/BulkEditEvaluationResource.php`

```php
<?php

namespace Modules\Evaluation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BulkEditEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $application = $this->application;
        $jobProfile = $application?->jobProfile;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_score' => $this->total_score,
            'max_possible_score' => $this->max_possible_score,
            'percentage' => $this->percentage,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'modified_at' => $this->modified_at?->format('Y-m-d H:i:s'),

            // Información del postulante
            'applicant' => [
                'id' => $application->id ?? null,
                'uuid' => $application->uuid ?? null,
                'full_name' => $application->full_name ?? 'N/A',
                'dni' => $application->dni ?? 'N/A',
                'position_code' => $jobProfile?->positionCode?->code ?? 'N/A',
                'position_name' => $jobProfile?->positionCode?->name ?? 'N/A',
            ],

            // Evaluador
            'evaluator' => [
                'id' => $this->evaluator_id,
                'name' => $this->evaluator?->name ?? 'N/A',
            ],

            // Detalles de criterios (puntajes)
            'details' => $this->details->mapWithKeys(function ($detail) {
                return [
                    'criterion_' . $detail->criterion_id => [
                        'detail_id' => $detail->id,
                        'score' => $detail->score,
                        'weighted_score' => $detail->weighted_score,
                        'version' => $detail->version,
                        'comments' => $detail->comments,
                    ]
                ];
            }),

            // Metadata para frontend
            'can_edit' => in_array($this->status->value, ['SUBMITTED', 'MODIFIED']),
        ];
    }
}
```

### 4. Modificar EvaluationService: Agregar método bulkUpdateScore

**Ruta**: `Modules/Evaluation/app/Services/EvaluationService.php`

**INSTRUCCIONES**: Agregar el siguiente método al final de la clase (antes del último `}`):

```php
    /**
     * Actualizar un puntaje individual en modo bulk edit (para administradores)
     * Este método es similar a saveEvaluationDetail pero optimizado para edición masiva
     *
     * @param int $evaluationId
     * @param int $criterionId
     * @param float $score
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function bulkUpdateScore(int $evaluationId, int $criterionId, float $score): array
    {
        try {
            $evaluation = Evaluation::with(['details', 'application.jobProfile.positionCode'])
                ->findOrFail($evaluationId);

            // Validar que la evaluación esté en estado válido para edición bulk
            if (!in_array($evaluation->status->value, ['SUBMITTED', 'MODIFIED'])) {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden editar evaluaciones completadas (SUBMITTED o MODIFIED)',
                    'data' => null,
                ];
            }

            $criterion = EvaluationCriterion::findOrFail($criterionId);

            // Validar rango de puntaje
            if (!$criterion->validateScore($score)) {
                return [
                    'success' => false,
                    'message' => "El puntaje debe estar entre {$criterion->min_score} y {$criterion->max_score}",
                    'data' => null,
                ];
            }

            return DB::transaction(function () use ($evaluation, $criterion, $score) {
                // Buscar o crear el detalle
                $detail = $evaluation->details()
                    ->where('criterion_id', $criterion->id)
                    ->first();

                $oldScore = $detail?->score;
                $isNewDetail = !$detail;

                if ($detail) {
                    // Actualizar existente
                    $detail->update([
                        'score' => $score,
                        'version' => $detail->version + 1,
                        'change_reason' => 'Actualización masiva por administrador',
                    ]);
                } else {
                    // Crear nuevo detalle
                    $detail = $evaluation->details()->create([
                        'criterion_id' => $criterion->id,
                        'score' => $score,
                        'change_reason' => 'Creado en edición masiva por administrador',
                    ]);
                }

                // Actualizar estado de la evaluación a MODIFIED si estaba SUBMITTED
                if ($evaluation->status->value === 'SUBMITTED') {
                    $evaluation->update([
                        'status' => \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED,
                        'modified_by' => auth()->id(),
                        'modified_at' => now(),
                        'modification_reason' => 'Modificación masiva de puntajes',
                    ]);
                }

                // Registrar en historial
                $userId = auth()->id();
                $action = $isNewDetail ? 'CRITERION_ADDED' : 'CRITERION_CHANGED';
                $description = $isNewDetail
                    ? "Criterio '{$criterion->name}' agregado en edición masiva"
                    : "Criterio '{$criterion->name}' actualizado en edición masiva";

                EvaluationHistory::logChange(
                    $evaluation->id,
                    $userId,
                    $action,
                    $description,
                    ['score' => $oldScore],
                    ['score' => $score],
                    'Edición masiva por administrador'
                );

                // Refrescar para obtener los scores actualizados (se calculan automáticamente)
                $evaluation->refresh();

                return [
                    'success' => true,
                    'message' => 'Puntaje actualizado correctamente',
                    'data' => [
                        'detail_id' => $detail->id,
                        'score' => $detail->score,
                        'weighted_score' => $detail->weighted_score,
                        'version' => $detail->version,
                        'total_score' => $evaluation->total_score,
                        'percentage' => $evaluation->percentage,
                    ],
                ];
            });

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Evaluación o criterio no encontrado',
                'data' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Error en bulkUpdateScore: ' . $e->getMessage(), [
                'evaluation_id' => $evaluationId,
                'criterion_id' => $criterionId,
                'score' => $score,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el puntaje: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }
```

**IMPORTANTE**: No olvidar agregar el import de `DB` al inicio del archivo si no está:
```php
use Illuminate\Support\Facades\DB;
```

### 5. Crear Controlador: BulkEditEvaluationController

**Ruta**: `Modules/Evaluation/app/Http/Controllers/BulkEditEvaluationController.php`

```php
<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Services\EvaluationService;
use Modules\Evaluation\Entities\{Evaluation, EvaluationCriterion};
use Modules\Evaluation\Http\Requests\{BulkEditScoreRequest, LoadBulkEditDataRequest};
use Modules\Evaluation\Http\Resources\BulkEditEvaluationResource;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};

class BulkEditEvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(EvaluationService $evaluationService)
    {
        $this->middleware('auth');
        $this->middleware('can:assign-evaluators');
        $this->evaluationService = $evaluationService;
    }

    /**
     * Vista inicial: Selección de JobPosting y Phase
     * GET /evaluation/bulk-edit
     */
    public function index()
    {
        $jobPostings = JobPosting::with('processPhases')
            ->where('status', 'PUBLISHED')
            ->orWhere('status', 'IN_PROGRESS')
            ->orWhere('status', 'COMPLETED')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('evaluation::bulk-edit.index', compact('jobPostings'));
    }

    /**
     * Vista de edición masiva: Tabla con todas las evaluaciones
     * GET /evaluation/bulk-edit/edit?job_posting_id={uuid}&phase_id={uuid}
     */
    public function edit(Request $request)
    {
        // Validar parámetros
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
            'phase_id' => ['required', 'exists:process_phases,uuid'],
        ]);

        $jobPosting = JobPosting::where('uuid', $validated['job_posting_id'])->firstOrFail();
        $phase = ProcessPhase::where('uuid', $validated['phase_id'])->firstOrFail();

        // Obtener criterios de evaluación para esta fase/convocatoria
        $criteria = EvaluationCriterion::active()
            ->byPhase($phase->uuid)
            ->byJobPosting($jobPosting->uuid)
            ->ordered()
            ->get();

        if ($criteria->isEmpty()) {
            return redirect()->route('evaluation.bulk-edit.index')
                ->with('error', 'No hay criterios de evaluación definidos para esta fase y convocatoria.');
        }

        // Obtener todas las evaluaciones con sus relaciones
        $evaluations = Evaluation::with([
                'application.jobProfile.positionCode',
                'application.jobProfile.requestingUnit',
                'evaluator',
                'details.criterion',
            ])
            ->where('job_posting_id', $jobPosting->uuid)
            ->where('phase_id', $phase->uuid)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED'])
            ->get();

        // Filtros aplicados
        $filters = [
            'search' => $request->get('search', ''),
            'score_min' => $request->get('score_min', ''),
            'score_max' => $request->get('score_max', ''),
            'status' => $request->get('status', []),
        ];

        return view('evaluation::bulk-edit.edit', compact(
            'jobPosting',
            'phase',
            'criteria',
            'evaluations',
            'filters'
        ));
    }

    /**
     * API Endpoint: Cargar datos de evaluaciones (con filtros)
     * GET /evaluation/bulk-edit/data?job_posting_id={uuid}&phase_id={uuid}&filters...
     */
    public function loadData(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
            'phase_id' => ['required', 'exists:process_phases,uuid'],
            'search' => ['nullable', 'string', 'max:255'],
            'score_min' => ['nullable', 'numeric', 'min:0'],
            'score_max' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string', 'in:SUBMITTED,MODIFIED,IN_PROGRESS'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $jobPosting = JobPosting::where('uuid', $validated['job_posting_id'])->firstOrFail();
        $phase = ProcessPhase::where('uuid', $validated['phase_id'])->firstOrFail();

        // Query base
        $query = Evaluation::with([
                'application.jobProfile.positionCode',
                'evaluator',
                'details.criterion',
            ])
            ->where('job_posting_id', $jobPosting->uuid)
            ->where('phase_id', $phase->uuid)
            ->whereIn('status', ['SUBMITTED', 'MODIFIED']);

        // Aplicar filtros
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['score_min'])) {
            $query->where('total_score', '>=', $validated['score_min']);
        }

        if (!empty($validated['score_max'])) {
            $query->where('total_score', '<=', $validated['score_max']);
        }

        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }

        // Ordenar por nombre de postulante
        $query->join('applications', 'evaluations.application_id', '=', 'applications.id')
            ->select('evaluations.*')
            ->orderBy('applications.full_name', 'asc');

        // Paginar
        $evaluations = $query->paginate(50);

        return BulkEditEvaluationResource::collection($evaluations);
    }

    /**
     * API Endpoint: Actualizar un puntaje específico
     * POST /evaluation/bulk-edit/update-score
     * Body: {evaluation_id, criterion_id, score}
     */
    public function updateScore(BulkEditScoreRequest $request)
    {
        $result = $this->evaluationService->bulkUpdateScore(
            $request->evaluation_id,
            $request->criterion_id,
            $request->score
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => ['score' => [$result['message']]],
            ], 422);
        }
    }

    /**
     * API Endpoint: Obtener criterios de evaluación para una fase/convocatoria
     * GET /evaluation/bulk-edit/criteria?job_posting_id={uuid}&phase_id={uuid}
     */
    public function getCriteria(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
            'phase_id' => ['required', 'exists:process_phases,uuid'],
        ]);

        $jobPosting = JobPosting::where('uuid', $validated['job_posting_id'])->firstOrFail();
        $phase = ProcessPhase::where('uuid', $validated['phase_id'])->firstOrFail();

        $criteria = EvaluationCriterion::active()
            ->byPhase($phase->uuid)
            ->byJobPosting($jobPosting->uuid)
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $criteria->map(function ($criterion) {
                return [
                    'id' => $criterion->id,
                    'code' => $criterion->code,
                    'name' => $criterion->name,
                    'min_score' => $criterion->min_score,
                    'max_score' => $criterion->max_score,
                    'weight' => $criterion->weight,
                ];
            }),
        ]);
    }

    /**
     * API Endpoint: Obtener fases de una convocatoria
     * GET /evaluation/bulk-edit/phases?job_posting_id={uuid}
     */
    public function getPhases(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,uuid'],
        ]);

        $jobPosting = JobPosting::where('uuid', $validated['job_posting_id'])
            ->with('processPhases')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $jobPosting->processPhases->map(function ($phase) {
                return [
                    'uuid' => $phase->uuid,
                    'name' => $phase->name,
                    'order' => $phase->order,
                ];
            }),
        ]);
    }
}
```

### 6. Agregar Rutas al módulo

**Ruta**: `Modules/Evaluation/routes/web.php`

**INSTRUCCIONES**: Agregar el siguiente bloque de rutas ANTES del cierre del middleware group `auth, verified` (alrededor de la línea 179):

```php
    // ========================================
    // BULK EDIT - Edición Masiva de Evaluaciones (Solo Admin)
    // ========================================
    Route::prefix('bulk-edit')
        ->name('bulk-edit.')
        ->middleware('can:assign-evaluators')
        ->group(function () {

            // Vista de selección de convocatoria y fase
            Route::get('/', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'index'])
                ->name('index');

            // Vista de edición masiva
            Route::get('edit', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'edit'])
                ->name('edit');

            // API Endpoints (AJAX)
            Route::get('data', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'loadData'])
                ->name('data');

            Route::post('update-score', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'updateScore'])
                ->name('update-score');

            Route::get('criteria', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'getCriteria'])
                ->name('criteria');

            Route::get('phases', [\Modules\Evaluation\Http\Controllers\BulkEditEvaluationController::class, 'getPhases'])
                ->name('phases');
        });
```

### 7. Crear Vista: index.blade.php (Selección)

**Ruta**: `Modules/Evaluation/resources/views/bulk-edit/index.blade.php`

```blade
@extends('evaluation::layouts.master')

@section('title', 'Edición Masiva de Evaluaciones')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edición Masiva de Evaluaciones</h1>
        <p class="mt-2 text-gray-600">Seleccione una convocatoria y una fase para editar las evaluaciones de forma masiva</p>
    </div>

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6" x-data="bulkEditSelector()">
        <form method="GET" action="{{ route('evaluation.bulk-edit.edit') }}" @submit="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Selector de Convocatoria -->
                <div>
                    <label for="job_posting_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Convocatoria <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="job_posting_id"
                        name="job_posting_id"
                        x-model="selectedJobPosting"
                        @change="loadPhases()"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        required
                    >
                        <option value="">-- Seleccione una convocatoria --</option>
                        @foreach($jobPostings as $posting)
                            <option value="{{ $posting->uuid }}">
                                {{ $posting->title }} ({{ $posting->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Seleccione la convocatoria que desea gestionar</p>
                </div>

                <!-- Selector de Fase -->
                <div>
                    <label for="phase_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Fase del Proceso <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="phase_id"
                        name="phase_id"
                        x-model="selectedPhase"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        :disabled="!selectedJobPosting || loadingPhases"
                        required
                    >
                        <option value="">-- Seleccione una fase --</option>
                        <template x-for="phase in phases" :key="phase.uuid">
                            <option :value="phase.uuid" x-text="phase.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-sm text-gray-500" x-show="loadingPhases">Cargando fases...</p>
                    <p class="mt-1 text-sm text-gray-500" x-show="!selectedJobPosting && !loadingPhases">Primero seleccione una convocatoria</p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a
                    href="{{ route('evaluation.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!selectedJobPosting || !selectedPhase || loadingPhases"
                    x-text="loadingPhases ? 'Cargando...' : 'Cargar Evaluaciones'"
                >
                    Cargar Evaluaciones
                </button>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Solo se mostrarán evaluaciones en estado <strong>ENVIADO</strong> o <strong>MODIFICADO</strong></li>
                        <li>Los cambios se guardarán automáticamente al salir de cada campo</li>
                        <li>Cada modificación quedará registrada en el historial de la evaluación</li>
                        <li>Se requiere el permiso de <strong>administrador de evaluaciones</strong> para acceder</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bulkEditSelector() {
    return {
        selectedJobPosting: '',
        selectedPhase: '',
        phases: [],
        loadingPhases: false,

        async loadPhases() {
            if (!this.selectedJobPosting) {
                this.phases = [];
                this.selectedPhase = '';
                return;
            }

            this.loadingPhases = true;
            this.selectedPhase = '';

            try {
                const response = await fetch(`{{ route('evaluation.bulk-edit.phases') }}?job_posting_id=${this.selectedJobPosting}`);
                const result = await response.json();

                if (result.success) {
                    this.phases = result.data;
                } else {
                    alert('Error al cargar las fases');
                    this.phases = [];
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cargar las fases');
                this.phases = [];
            } finally {
                this.loadingPhases = false;
            }
        },

        handleSubmit(event) {
            if (!this.selectedJobPosting || !this.selectedPhase) {
                event.preventDefault();
                alert('Por favor seleccione una convocatoria y una fase');
            }
        }
    };
}
</script>
@endsection
```

### 8. Crear Vista: edit.blade.php (Tabla Editable)

**Ruta**: `Modules/Evaluation/resources/views/bulk-edit/edit.blade.php`

Esta vista es EXTENSA. Por el límite de caracteres, continuaré en el siguiente bloque.

```blade
@extends('evaluation::layouts.master')

@section('title', 'Edición Masiva - ' . $jobPosting->title)

@section('content')
<div class="container-fluid px-4 py-6" x-data="bulkEditTable()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edición Masiva de Evaluaciones</h1>
                <p class="mt-1 text-sm text-gray-600">
                    <strong>Convocatoria:</strong> {{ $jobPosting->title }} ({{ $jobPosting->code }}) |
                    <strong>Fase:</strong> {{ $phase->name }}
                </p>
            </div>
            <div>
                <a href="{{ route('evaluation.bulk-edit.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Búsqueda por nombre/DNI -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Postulante</label>
                <input
                    type="text"
                    x-model="filters.search"
                    @input.debounce.500ms="applyFilters()"
                    placeholder="Nombre o DNI..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <!-- Filtro por rango de puntaje -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puntaje Mínimo</label>
                <input
                    type="number"
                    x-model="filters.score_min"
                    @input.debounce.500ms="applyFilters()"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puntaje Máximo</label>
                <input
                    type="number"
                    x-model="filters.score_max"
                    @input.debounce.500ms="applyFilters()"
                    step="0.01"
                    min="0"
                    placeholder="100.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <!-- Filtro por estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select
                    x-model="filters.status"
                    @change="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                >
                    <option value="">Todos</option>
                    <option value="SUBMITTED">Enviado</option>
                    <option value="MODIFIED">Modificado</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <button
                @click="clearFilters()"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
            >
                Limpiar filtros
            </button>
            <div class="text-sm text-gray-600">
                Mostrando <span x-text="filteredEvaluations.length"></span> evaluación(es)
            </div>
        </div>
    </div>

    <!-- Indicador de guardado global -->
    <div x-show="saving" class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center z-50">
        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
    </div>

    <!-- Tabla editable -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">#</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Postulante</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">DNI</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Cargo</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Estado</th>

                        <!-- Columnas dinámicas por criterio -->
                        @foreach($criteria as $criterion)
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-indigo-50" title="{{ $criterion->description }}">
                            {{ $criterion->name }}
                            <br>
                            <span class="text-xs font-normal text-gray-400">({{ $criterion->min_score }}-{{ $criterion->max_score }})</span>
                        </th>
                        @endforeach

                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-green-50">Puntaje Total</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(evaluation, index) in filteredEvaluations" :key="evaluation.id">
                        <tr :class="{'bg-gray-50': index % 2 === 0}">
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="evaluation.application.full_name"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500" x-text="evaluation.application.dni"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="evaluation.application.position_code"></span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800': evaluation.status === 'SUBMITTED',
                                        'bg-yellow-100 text-yellow-800': evaluation.status === 'MODIFIED'
                                    }"
                                    x-text="evaluation.status_label"
                                ></span>
                            </td>

                            <!-- Inputs editables por criterio -->
                            @foreach($criteria as $criterion)
                            <td class="px-3 py-2 text-center bg-indigo-50">
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="{{ $criterion->min_score }}"
                                        max="{{ $criterion->max_score }}"
                                        :value="getScore(evaluation, {{ $criterion->id }})"
                                        @blur="updateScore(evaluation.id, {{ $criterion->id }}, $event.target.value, $event.target, {{ $criterion->min_score }}, {{ $criterion->max_score }})"
                                        @keydown.enter="$event.target.blur()"
                                        class="w-20 px-2 py-1 text-center border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                        :disabled="!evaluation.can_edit"
                                    />
                                    <!-- Indicadores de guardado por campo -->
                                    <div class="absolute -right-6 top-1/2 transform -translate-y-1/2">
                                        <div :id="`indicator-${evaluation.id}-{{ $criterion->id }}`" class="hidden">
                                            <!-- Spinner -->
                                            <svg class="saving-spinner animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <!-- Check -->
                                            <svg class="success-icon h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <!-- Error -->
                                            <svg class="error-icon h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endforeach

                            <!-- Puntaje Total -->
                            <td class="px-3 py-2 text-center font-semibold bg-green-50">
                                <span x-text="evaluation.total_score ? evaluation.total_score.toFixed(2) : '0.00'"></span>
                                <span class="text-xs text-gray-500 block" x-text="'(' + (evaluation.percentage ? evaluation.percentage.toFixed(1) : '0.0') + '%)'"></span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-3 py-2 whitespace-nowrap text-center text-sm">
                                <a :href="`{{ route('evaluation.show', '') }}/${evaluation.id}`" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                    <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <tr x-show="filteredEvaluations.length === 0">
                        <td :colspan="{{ 6 + count($criteria) }}" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">No se encontraron evaluaciones con los filtros aplicados</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Instrucciones:</h4>
        <ul class="text-sm text-gray-600 space-y-1">
            <li>• Haga clic en cualquier puntaje para editarlo</li>
            <li>• Los cambios se guardan automáticamente al salir del campo (presione Enter o haga clic fuera)</li>
            <li>• Los puntajes deben estar dentro del rango indicado entre paréntesis</li>
            <li>• El puntaje total se actualiza automáticamente después de cada cambio</li>
            <li>• Todos los cambios quedan registrados en el historial de la evaluación</li>
        </ul>
    </div>
</div>

<script>
function bulkEditTable() {
    return {
        evaluations: @json($evaluations),
        criteria: @json($criteria),
        filters: {
            search: '{{ $filters["search"] }}',
            score_min: '{{ $filters["score_min"] }}',
            score_max: '{{ $filters["score_max"] }}',
            status: '{{ $filters["status"] }}',
        },
        saving: false,
        filteredEvaluations: [],

        init() {
            this.applyFilters();
        },

        applyFilters() {
            let filtered = [...this.evaluations];

            // Filtro por búsqueda
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(e =>
                    e.application.full_name.toLowerCase().includes(search) ||
                    e.application.dni.includes(search)
                );
            }

            // Filtro por puntaje mínimo
            if (this.filters.score_min) {
                const min = parseFloat(this.filters.score_min);
                filtered = filtered.filter(e => (e.total_score || 0) >= min);
            }

            // Filtro por puntaje máximo
            if (this.filters.score_max) {
                const max = parseFloat(this.filters.score_max);
                filtered = filtered.filter(e => (e.total_score || 0) <= max);
            }

            // Filtro por estado
            if (this.filters.status) {
                filtered = filtered.filter(e => e.status === this.filters.status);
            }

            this.filteredEvaluations = filtered;
        },

        clearFilters() {
            this.filters = {
                search: '',
                score_min: '',
                score_max: '',
                status: '',
            };
            this.applyFilters();
        },

        getScore(evaluation, criterionId) {
            const key = `criterion_${criterionId}`;
            return evaluation.details[key]?.score || '';
        },

        async updateScore(evaluationId, criterionId, newScore, inputElement, minScore, maxScore) {
            // Validar que el valor no esté vacío
            if (newScore === '' || newScore === null) {
                this.showError(inputElement, evaluationId, criterionId, 'El puntaje no puede estar vacío');
                return;
            }

            const score = parseFloat(newScore);

            // Validar rango
            if (score < minScore || score > maxScore) {
                this.showError(inputElement, evaluationId, criterionId, `El puntaje debe estar entre ${minScore} y ${maxScore}`);
                return;
            }

            // Mostrar indicador de guardando
            this.showSaving(evaluationId, criterionId);
            this.saving = true;

            try {
                const response = await fetch('{{ route("evaluation.bulk-edit.update-score") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        evaluation_id: evaluationId,
                        criterion_id: criterionId,
                        score: score,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    // Actualizar datos en el frontend
                    this.updateEvaluationData(evaluationId, criterionId, result.data);

                    // Mostrar indicador de éxito
                    this.showSuccess(evaluationId, criterionId);
                } else {
                    this.showError(inputElement, evaluationId, criterionId, result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError(inputElement, evaluationId, criterionId, 'Error de conexión. Intente nuevamente.');
            } finally {
                this.saving = false;
            }
        },

        updateEvaluationData(evaluationId, criterionId, data) {
            // Buscar la evaluación en el array
            const evaluation = this.evaluations.find(e => e.id === evaluationId);
            if (evaluation) {
                const key = `criterion_${criterionId}`;

                // Actualizar o crear el detalle del criterio
                if (!evaluation.details[key]) {
                    evaluation.details[key] = {};
                }

                evaluation.details[key].score = data.score;
                evaluation.details[key].weighted_score = data.weighted_score;
                evaluation.details[key].detail_id = data.detail_id;
                evaluation.details[key].version = data.version;

                // Actualizar puntaje total
                evaluation.total_score = data.total_score;
                evaluation.percentage = data.percentage;

                // Actualizar estado a MODIFIED
                if (evaluation.status === 'SUBMITTED') {
                    evaluation.status = 'MODIFIED';
                    evaluation.status_label = 'Modificado';
                }
            }

            // Re-aplicar filtros para actualizar la vista
            this.applyFilters();
        },

        showSaving(evaluationId, criterionId) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.classList.remove('hidden');
                indicator.querySelector('.saving-spinner').classList.remove('hidden');
                indicator.querySelector('.success-icon').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.add('hidden');
            }
        },

        showSuccess(evaluationId, criterionId) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.querySelector('.saving-spinner').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.add('hidden');
                indicator.querySelector('.success-icon').classList.remove('hidden');

                // Ocultar después de 2 segundos
                setTimeout(() => {
                    indicator.classList.add('hidden');
                }, 2000);
            }
        },

        showError(inputElement, evaluationId, criterionId, message) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.querySelector('.saving-spinner').classList.add('hidden');
                indicator.querySelector('.success-icon').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.remove('hidden');
            }

            // Mostrar error visualmente en el input
            inputElement.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            // Mostrar tooltip con el error
            alert(message);

            // Restaurar border después de 3 segundos
            setTimeout(() => {
                inputElement.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                if (indicator) {
                    indicator.classList.add('hidden');
                }
            }, 3000);
        }
    };
}
</script>
@endsection
```

### 9. Actualizar Navegación del Módulo

**INSTRUCCIONES**: Agregar enlace en el menú de navegación del módulo de evaluación.

**Ruta**: `Modules/Evaluation/resources/views/components/navigation.blade.php` (o donde esté el menú)

Agregar:
```blade
@can('assign-evaluators')
<li>
    <a href="{{ route('evaluation.bulk-edit.index') }}"
       class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('evaluation.bulk-edit.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">
        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edición Masiva
    </a>
</li>
@endcan
```

## ✅ Checklist de Implementación

Al implementar esta funcionalidad, asegúrate de cumplir con todos estos puntos:

### Backend
- [ ] Crear `BulkEditScoreRequest` con validación completa
- [ ] Crear `LoadBulkEditDataRequest` con validación de params
- [ ] Crear `BulkEditEvaluationResource` para serializar datos
- [ ] Agregar método `bulkUpdateScore()` en `EvaluationService`
- [ ] Crear `BulkEditEvaluationController` con todos los endpoints
- [ ] Agregar rutas en `web.php` dentro del middleware `can:assign-evaluators`
- [ ] Verificar que los imports estén correctos en todos los archivos

### Frontend
- [ ] Crear directorio `resources/views/bulk-edit/`
- [ ] Crear `index.blade.php` (selección)
- [ ] Crear `edit.blade.php` (tabla editable)
- [ ] Verificar que Alpine.js esté disponible en el layout
- [ ] Verificar que Tailwind CSS esté compilado correctamente
- [ ] Agregar meta tag CSRF en el layout si no existe

### Funcionalidades
- [ ] Selección de JobPosting funciona correctamente
- [ ] Carga dinámica de fases según convocatoria
- [ ] Tabla muestra todas las evaluaciones correctamente
- [ ] Inputs son editables y validados en el rango correcto
- [ ] Guardado automático funciona al blur
- [ ] Indicadores visuales (spinner, check, error) funcionan
- [ ] Filtros por búsqueda, puntaje y estado funcionan
- [ ] Botón "Limpiar filtros" funciona
- [ ] Puntaje total se actualiza automáticamente
- [ ] Estado cambia de SUBMITTED a MODIFIED al editar
- [ ] Cambios se registran en `evaluation_history`

### Seguridad y Permisos
- [ ] Solo usuarios con `assign-evaluators` pueden acceder
- [ ] Solo evaluaciones en estado SUBMITTED o MODIFIED son editables
- [ ] Validación server-side de rangos de puntaje
- [ ] CSRF token incluido en requests AJAX
- [ ] SQL injection prevenido (usar Eloquent ORM)

### Optimización
- [ ] Queries con eager loading (`with()`)
- [ ] Paginación implementada (50 por página)
- [ ] Filtros no realizan requests innecesarios (debounce)
- [ ] Indicadores de carga para mejor UX

## 🧪 Pruebas Recomendadas

Después de implementar, realizar las siguientes pruebas:

1. **Prueba de Acceso**:
   - Usuario sin permiso NO puede acceder
   - Usuario con permiso SÍ puede acceder

2. **Prueba de Selección**:
   - Seleccionar convocatoria carga las fases correctas
   - Botón "Cargar Evaluaciones" está deshabilitado hasta seleccionar ambos
   - Redirección funciona correctamente

3. **Prueba de Tabla**:
   - Se muestran todas las evaluaciones correctas
   - Columnas de criterios son dinámicas
   - Puntajes actuales se muestran correctamente

4. **Prueba de Edición**:
   - Editar un puntaje válido guarda correctamente
   - Editar con puntaje fuera de rango muestra error
   - Puntaje total se actualiza automáticamente
   - Estado cambia a MODIFIED

5. **Prueba de Filtros**:
   - Búsqueda por nombre funciona
   - Búsqueda por DNI funciona
   - Filtro por rango de puntaje funciona
   - Filtro por estado funciona
   - Limpiar filtros restaura todo

6. **Prueba de Historial**:
   - Verificar que en `evaluation_history` se registre cada cambio
   - Verificar que incluya usuario, fecha y puntajes old/new

## 📚 Notas Adicionales

### Patrón de Nombres
- Controllers: PascalCase (ej: `BulkEditEvaluationController`)
- Métodos: camelCase (ej: `updateScore`, `loadData`)
- Rutas: kebab-case (ej: `bulk-edit`, `update-score`)
- Vistas: kebab-case (ej: `bulk-edit/index.blade.php`)

### Convenciones del Proyecto
- UUID se usa para `job_postings`, `process_phases`, `users`, `applications`
- ID incremental se usa para `evaluations`, `evaluation_details`, `evaluation_criteria`
- Timestamps automáticos con `$table->timestamps()`
- Soft deletes con `$table->softDeletes()`

### Estructura de Respuestas JSON
```json
{
  "success": true|false,
  "message": "Mensaje descriptivo",
  "data": {...} | null,
  "errors": {...} | null  // Solo si success = false
}
```

### Eventos de Eloquent Importantes
- `EvaluationDetail::saved()` → actualiza `evaluation.total_score`
- `EvaluationDetail::saving()` → calcula `weighted_score`
- NO desactivar estos eventos, son cruciales

## 🔧 Solución de Problemas Comunes

### Error: "Evaluation or criterion not found"
- Verificar que los IDs enviados sean correctos
- Verificar que existan en la base de datos
- Verificar que no estén soft-deleted

### Error: "Solo se pueden editar evaluaciones completadas"
- Verificar que la evaluación esté en estado SUBMITTED o MODIFIED
- Verificar que el enum `EvaluationStatusEnum` tenga estos valores

### Error: "El puntaje debe estar entre X y Y"
- Verificar rangos en `evaluation_criteria.min_score` y `max_score`
- Asegurarse que la validación frontend coincida con backend

### Indicadores visuales no aparecen
- Verificar que Alpine.js esté cargado
- Verificar que los IDs de los indicadores sean únicos
- Abrir consola del navegador para ver errores JS

### Filtros no funcionan
- Verificar que `@input.debounce` esté correctamente escrito
- Verificar que `applyFilters()` se llame correctamente
- Verificar que los datos en `this.evaluations` existan

## 📞 Contacto y Soporte

Esta documentación fue creada para guiar la implementación completa de la funcionalidad de **Edición Masiva de Evaluaciones**.

Si encuentras algún problema o necesitas aclaraciones:
- Revisa la sección de "Solución de Problemas Comunes"
- Verifica los logs de Laravel (`storage/logs/laravel.log`)
- Verifica la consola del navegador para errores de JavaScript

---

**Versión**: 1.0
**Fecha**: 2026-01-30
**Módulo**: Evaluation
**Sistema**: CAS - MDSJ
