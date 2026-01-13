# Propuesta: Mejora de Relación Application - JobProfile

## Problema Actual

El diseño actual vincula `Application` a `JobProfileVacancy` desde el momento de la postulación:

```php
Application
├── job_profile_vacancy_id (asignado al postular) ❌
```

**Problemas:**
1. Asigna una "vacante" antes de saber si el postulante ganará
2. Confusión semántica: ¿Ya ganó la vacante V01 o solo postuló?
3. Queries complejos para obtener postulantes de un perfil
4. No refleja el proceso CAS real

## Propuesta de Mejora

Vincular `Application` directamente con `JobProfile` y asignar vacante solo después de evaluación:

```php
Application
├── job_profile_id (relación directa con el perfil) ✅
├── assigned_vacancy_id (NULL hasta ganar) ✅
```

## Ventajas

### 1. Refleja el Proceso CAS Real
- El postulante aplica al **perfil de puesto**, no a una vacante específica
- Las vacantes son intercambiables (V01, V02, V03 son equivalentes)
- La asignación ocurre después de conocer resultados

### 2. Semántica Clara
- `job_profile_id` = "Postuló a este perfil"
- `assigned_vacancy_id = NULL` = "No ha ganado vacante"
- `assigned_vacancy_id = V01` = "Ganó la vacante V01"

### 3. Queries Simplificados
```php
// Actual (complejo)
$applications = Application::whereHas('vacancy', function($q) use ($jobProfileId) {
    $q->where('job_profile_id', $jobProfileId);
})->get();

// Propuesto (directo)
$applications = Application::where('job_profile_id', $jobProfileId)->get();
```

### 4. Estados Bien Definidos
```php
// Ver ganadores
$winners = Application::whereNotNull('assigned_vacancy_id')->get();

// Ver elegibles sin vacante
$pending = Application::whereNull('assigned_vacancy_id')
                      ->where('is_eligible', true)->get();
```

## Cambios Necesarios

### 1. Migración de Base de Datos

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // 1. Agregar nueva columna job_profile_id
            $table->foreignUuid('job_profile_id')
                  ->after('code')
                  ->nullable() // Temporal para migración
                  ->constrained('job_profiles')
                  ->onDelete('restrict');

            // 2. Renombrar job_profile_vacancy_id a assigned_vacancy_id
            $table->renameColumn('job_profile_vacancy_id', 'assigned_vacancy_id');
        });

        // 3. Migrar datos existentes
        DB::statement("
            UPDATE applications a
            INNER JOIN job_profile_vacancies v ON a.assigned_vacancy_id = v.id
            SET a.job_profile_id = v.job_profile_id
        ");

        // 4. Limpiar assigned_vacancy_id (solo mantener ganadores reales)
        DB::statement("
            UPDATE applications a
            LEFT JOIN job_profile_vacancies v ON v.assigned_application_id = a.id
            SET a.assigned_vacancy_id = CASE
                WHEN v.id IS NOT NULL THEN a.assigned_vacancy_id
                ELSE NULL
            END
        ");

        Schema::table('applications', function (Blueprint $table) {
            // 5. Hacer job_profile_id NOT NULL
            $table->uuid('job_profile_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('assigned_vacancy_id', 'job_profile_vacancy_id');
            $table->dropForeign(['job_profile_id']);
            $table->dropColumn('job_profile_id');
        });
    }
};
```

### 2. Actualizar Modelo Application

```php
<?php

namespace Modules\Application\Entities;

class Application extends Model
{
    protected $fillable = [
        'code',
        'job_profile_id',        // ← NUEVO
        'assigned_vacancy_id',   // ← RENOMBRADO (era job_profile_vacancy_id)
        'applicant_id',
        // ... resto
    ];

    /**
     * Perfil al que postula (relación principal)
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfile::class, 'job_profile_id');
    }

    /**
     * Vacante asignada si ganó (puede ser NULL)
     */
    public function assignedVacancy(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfileVacancy::class, 'assigned_vacancy_id');
    }

    /**
     * @deprecated Use assignedVacancy() instead
     */
    public function vacancy(): BelongsTo
    {
        return $this->assignedVacancy();
    }

    /**
     * Verificar si ganó una vacante
     */
    public function hasWon(): bool
    {
        return !is_null($this->assigned_vacancy_id);
    }

    /**
     * Verificar si está pendiente de asignación
     */
    public function isPendingAssignment(): bool
    {
        return $this->is_eligible && is_null($this->assigned_vacancy_id);
    }

    /**
     * Scope: Solo ganadores
     */
    public function scopeWinners($query)
    {
        return $query->whereNotNull('assigned_vacancy_id');
    }

    /**
     * Scope: Elegibles sin vacante asignada
     */
    public function scopePendingAssignment($query)
    {
        return $query->where('is_eligible', true)
                     ->whereNull('assigned_vacancy_id');
    }
}
```

### 3. Actualizar Modelo JobProfile

```php
<?php

namespace Modules\JobProfile\Entities;

class JobProfile extends BaseSoftDelete
{
    /**
     * Todas las postulaciones a este perfil
     */
    public function applications(): HasMany
    {
        return $this->hasMany(\Modules\Application\Entities\Application::class, 'job_profile_id');
    }

    /**
     * Postulaciones ganadoras
     */
    public function winners(): HasMany
    {
        return $this->applications()->winners();
    }

    /**
     * Postulaciones elegibles pendientes de asignación
     */
    public function eligiblePending(): HasMany
    {
        return $this->applications()->pendingAssignment();
    }

    /**
     * Obtener estadísticas de postulaciones
     */
    public function getApplicationStats(): array
    {
        $applications = $this->applications;

        return [
            'total' => $applications->count(),
            'aptos' => $applications->where('is_eligible', true)->count(),
            'no_aptos' => $applications->where('is_eligible', false)->count(),
            'ganadores' => $applications->whereNotNull('assigned_vacancy_id')->count(),
            'pendientes' => $applications->where('is_eligible', true)
                                       ->whereNull('assigned_vacancy_id')
                                       ->count(),
        ];
    }
}
```

### 4. Nuevo Servicio de Asignación

```php
<?php

namespace Modules\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Application\Entities\Application;
use Modules\JobProfile\Entities\JobProfile;

class VacancyAssignmentService
{
    /**
     * Asignar vacantes automáticamente según ranking
     */
    public function assignVacanciesByRanking(string $jobProfileId): array
    {
        return DB::transaction(function () use ($jobProfileId) {
            $jobProfile = JobProfile::findOrFail($jobProfileId);

            // 1. Obtener vacantes disponibles
            $availableVacancies = $jobProfile->vacancies()
                ->where('status', 'available')
                ->orderBy('vacancy_number')
                ->get();

            if ($availableVacancies->isEmpty()) {
                throw new \Exception('No hay vacantes disponibles');
            }

            // 2. Obtener ganadores (top N según ranking)
            $winners = Application::where('job_profile_id', $jobProfileId)
                ->where('is_eligible', true)
                ->whereNull('assigned_vacancy_id')
                ->orderBy('final_ranking', 'asc')
                ->limit($availableVacancies->count())
                ->get();

            if ($winners->isEmpty()) {
                throw new \Exception('No hay postulantes elegibles');
            }

            $assignments = [];

            // 3. Asignar vacantes
            foreach ($winners as $index => $winner) {
                if (!isset($availableVacancies[$index])) {
                    break;
                }

                $vacancy = $availableVacancies[$index];

                // Actualizar Application
                $winner->assigned_vacancy_id = $vacancy->id;
                $winner->status = 'GANADOR';
                $winner->save();

                // Actualizar Vacancy
                $vacancy->status = 'filled';
                $vacancy->assigned_application_id = $winner->id;
                $vacancy->save();

                // Registrar en historial
                $winner->history()->create([
                    'action' => 'vacancy_assigned',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                    'details' => [
                        'vacancy_code' => $vacancy->code,
                        'ranking' => $winner->final_ranking,
                        'score' => $winner->final_score,
                    ],
                ]);

                $assignments[] = [
                    'application' => $winner->fresh(),
                    'vacancy' => $vacancy->fresh(),
                ];
            }

            return $assignments;
        });
    }

    /**
     * Reasignar vacante (si ganador renuncia)
     */
    public function reassignVacancy(string $vacancyId, string $newApplicationId): array
    {
        return DB::transaction(function () use ($vacancyId, $newApplicationId) {
            $vacancy = \Modules\JobProfile\Entities\JobProfileVacancy::findOrFail($vacancyId);
            $newWinner = Application::findOrFail($newApplicationId);

            // Liberar vacante del anterior ganador
            if ($vacancy->assigned_application_id) {
                $previousWinner = Application::find($vacancy->assigned_application_id);
                if ($previousWinner) {
                    $previousWinner->assigned_vacancy_id = null;
                    $previousWinner->status = 'APTO';
                    $previousWinner->save();
                }
            }

            // Asignar al nuevo ganador
            $newWinner->assigned_vacancy_id = $vacancy->id;
            $newWinner->status = 'GANADOR';
            $newWinner->save();

            $vacancy->assigned_application_id = $newWinner->id;
            $vacancy->status = 'filled';
            $vacancy->save();

            return [
                'vacancy' => $vacancy->fresh(),
                'new_winner' => $newWinner->fresh(),
            ];
        });
    }
}
```

### 5. Actualizar Controladores

```php
<?php

namespace Modules\Application\Http\Controllers;

class ApplicationController extends Controller
{
    /**
     * Crear postulación
     */
    public function store(Request $request, string $jobProfileId)
    {
        $validated = $request->validate([
            'applicant_id' => 'required|uuid',
            'terms_accepted' => 'required|boolean',
            // ... otros campos
        ]);

        $application = Application::create([
            'code' => Application::generateCode(),
            'job_profile_id' => $jobProfileId, // ← Relación directa
            'assigned_vacancy_id' => null,     // ← Sin vacante aún
            'applicant_id' => $validated['applicant_id'],
            'status' => 'PRESENTADA',
            // ... otros campos
        ]);

        return response()->json($application);
    }

    /**
     * Asignar vacantes a ganadores
     */
    public function assignVacancies(string $jobProfileId, VacancyAssignmentService $service)
    {
        try {
            $assignments = $service->assignVacanciesByRanking($jobProfileId);

            return response()->json([
                'message' => 'Vacantes asignadas correctamente',
                'assignments' => $assignments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
```

## Flujo Completo con Nuevo Diseño

### 1. Postulación
```php
User → Postula al perfil "Contador Público"
Application {
    job_profile_id: JOB_CONTADOR
    assigned_vacancy_id: NULL
    status: PRESENTADA
}
```

### 2. Evaluación
```php
AutoGrader → Evalúa elegibilidad y calcula scores
Application {
    is_eligible: true
    final_score: 95
    final_ranking: 1
    status: APTO
}
```

### 3. Asignación
```php
VacancyAssignmentService → Asigna vacantes por ranking
Application {
    assigned_vacancy_id: V01
    status: GANADOR
}

JobProfileVacancy {
    assigned_application_id: APP_001
    status: filled
}
```

## Plan de Implementación

### Fase 1: Preparación (1 día)
- [ ] Crear migración para agregar `job_profile_id`
- [ ] Actualizar modelos con nuevas relaciones
- [ ] Mantener compatibilidad con código existente

### Fase 2: Migración de Datos (1 día)
- [ ] Ejecutar migración en desarrollo
- [ ] Migrar datos existentes
- [ ] Verificar integridad de datos

### Fase 3: Refactorización (2-3 días)
- [ ] Actualizar servicios (ApplicationService, AutoGraderService)
- [ ] Actualizar controladores
- [ ] Crear VacancyAssignmentService

### Fase 4: Testing (1-2 días)
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Tests de regresión

### Fase 5: Deployment (1 día)
- [ ] Backup de base de datos
- [ ] Ejecutar migración en producción
- [ ] Monitoreo post-deployment

## Riesgos y Mitigación

### Riesgo 1: Datos Existentes
**Mitigación:** La migración maneja datos existentes automáticamente

### Riesgo 2: Breaking Changes
**Mitigación:** Mantener método `vacancy()` como @deprecated para compatibilidad

### Riesgo 3: Queries Lentos
**Mitigación:** Agregar índices apropiados en `job_profile_id` y `assigned_vacancy_id`

## Conclusión

Este cambio mejora significativamente la claridad y mantenibilidad del sistema:

✅ Semántica clara
✅ Refleja proceso CAS real
✅ Queries más simples
✅ Mejor tracking de estados
✅ Escalable para futuras mejoras

**Recomendación:** Implementar este cambio lo antes posible para evitar deuda técnica.
