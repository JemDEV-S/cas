# 📚 Ejemplos de Uso del Sistema de Evaluación Automática

## 🎯 Tabla de Contenidos

1. [Configuración de Requisitos](#configuración-de-requisitos)
2. [Ejecución de Evaluaciones](#ejecución-de-evaluaciones)
3. [Consulta de Resultados](#consulta-de-resultados)
4. [Gestión Manual](#gestión-manual)
5. [Ejemplos de Código](#ejemplos-de-código)

---

## 1. Configuración de Requisitos

### 1.1 Configurar Cursos Requeridos

```php
// En el controlador o seeder de JobProfile
use Modules\JobProfile\Entities\JobProfile;

$jobProfile = JobProfile::find($jobProfileId);

$jobProfile->update([
    'required_courses' => [
        'Microsoft Office Avanzado',
        'Gestión Pública',
        'Ofimática Básica',
    ]
]);
```

**Nota**: La validación busca coincidencias parciales, por lo que:
- ✅ "Curso de Microsoft Office Avanzado 2024" → Coincide con "Microsoft Office Avanzado"
- ✅ "Ofimática Básica - SENATI" → Coincide con "Ofimática Básica"
- ❌ "Excel Básico" → NO coincide con "Microsoft Office Avanzado"

---

### 1.2 Configurar Conocimientos Técnicos

#### Opción A: Solo Nombre (Cualquier Nivel)

```php
$jobProfile->update([
    'knowledge_areas' => [
        'Microsoft Excel',
        'Microsoft Word',
        'AutoCAD',
    ]
]);
```

#### Opción B: Con Nivel de Dominio Requerido

```php
$jobProfile->update([
    'knowledge_areas' => [
        ['name' => 'Microsoft Excel', 'level' => 'INTERMEDIO'],
        ['name' => 'SQL', 'level' => 'BASICO'],
        ['name' => 'Power BI', 'level' => 'AVANZADO'],
        'Microsoft Word', // Sin nivel específico
    ]
]);
```

**Niveles disponibles**:
- `BASICO`
- `INTERMEDIO`
- `AVANZADO`

**Nota**: Si se especifica nivel, el postulante debe tener ese nivel o superior.

---

### 1.3 Ejemplo Completo de JobProfile

```php
use Modules\JobProfile\Entities\JobProfile;

$jobProfile = JobProfile::create([
    'title' => 'Especialista en Sistemas',
    'education_levels' => ['TITULO', 'MAESTRIA'],
    'general_experience_years' => 3,
    'specific_experience_years' => 2,
    'colegiatura_required' => true,
    'requires_osce_certification' => false,
    'requires_driver_license' => true,

    // 🆕 Cursos requeridos
    'required_courses' => [
        'Administración de Redes',
        'Seguridad Informática',
        'Gestión de Proyectos TI',
    ],

    // 🆕 Conocimientos técnicos
    'knowledge_areas' => [
        ['name' => 'Linux', 'level' => 'AVANZADO'],
        ['name' => 'Windows Server', 'level' => 'INTERMEDIO'],
        ['name' => 'Python', 'level' => 'BASICO'],
        'Docker',
        'Kubernetes',
    ],
]);
```

---

## 2. Ejecución de Evaluaciones

### 2.1 Usando Artisan Command

#### Evaluación Real

```bash
# Sintaxis básica
php artisan applications:evaluate {posting-id}

# Con usuario específico
php artisan applications:evaluate conv-2024-001 --user=admin-uuid-123

# Ejemplo real
php artisan applications:evaluate 9d6f1234-5678-90ab-cdef-1234567890ab --user=9d6f1111-1111-1111-1111-111111111111
```

#### Modo Simulación (Dry Run)

```bash
# Ver resultados sin guardar cambios
php artisan applications:evaluate conv-2024-001 --dry-run

# Útil para verificar configuración antes de evaluar
php artisan applications:evaluate {posting-id} --dry-run --user={admin-id}
```

**Salida esperada**:
```
🚀 Iniciando evaluación automática para convocatoria: conv-2024-001
📊 Total de postulaciones a evaluar: 45

 45/45 [████████████████████████████] 100% Evaluando: Juan Pérez

📈 Resumen de Evaluación:
┌─────────────────┬──────────┬────────────┐
│ Métrica         │ Cantidad │ Porcentaje │
├─────────────────┼──────────┼────────────┤
│ Total evaluadas │ 45       │ 100%       │
│ ✅ APTOS        │ 30       │ 66.67%     │
│ ❌ NO APTOS     │ 15       │ 33.33%     │
│ ⚠️  Errores     │ 0        │ 0%         │
└─────────────────┴──────────┴────────────┘

❌ Postulantes NO APTOS:
• Juan Pérez (DNI: 12345678)
  - No cumple con capacitación requerida. Falta: Seguridad Informática
  - No cumple con conocimientos técnicos requeridos. Falta: Linux, Python

✅ Evaluación completada exitosamente
```

---

### 2.2 Usando Controlador Web (Admin)

```php
// POST /admin/applications/evaluation/{posting-id}/evaluate
// Automáticamente decide entre síncrono o asíncrono

// Ejemplo en Blade/Vue
<form method="POST" action="{{ route('admin.applications.evaluation.evaluate', $posting->id) }}">
    @csrf
    <button type="submit" class="btn btn-primary">
        Evaluar Todas las Postulaciones
    </button>
</form>
```

**Comportamiento**:
- ≤10 postulaciones: Procesamiento síncrono (respuesta inmediata)
- >10 postulaciones: Procesamiento asíncrono (en background con queue)

---

### 2.3 Usando Jobs Directamente

```php
use Modules\Application\Jobs\EvaluateApplicationBatch;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;

// Obtener IDs de postulaciones pendientes
$applicationIds = Application::where('status', ApplicationStatus::SUBMITTED)
    ->whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) =>
        $q->where('id', $postingId)
    )
    ->pluck('id');

// Dividir en lotes de 50 y despachar
$batches = $applicationIds->chunk(50);

foreach ($batches as $batch) {
    EvaluateApplicationBatch::dispatch($batch->toArray(), auth()->id());
}
```

---

### 2.4 Evaluación Individual (Testing)

```php
use Modules\Application\Services\AutoGraderService;
use Modules\Application\Entities\Application;

$autoGrader = app(AutoGraderService::class);
$application = Application::find($applicationId);

// Solo evaluar (sin guardar)
$result = $autoGrader->evaluateEligibility($application);

// Evaluar y guardar en BD
$autoGrader->applyAutoGrading($application, auth()->id());
```

---

## 3. Consulta de Resultados

### 3.1 Obtener Resultado de una Postulación

```php
use Modules\Application\Entities\Application;

$application = Application::with('latestEvaluation')->find($applicationId);

// Resultado general
$isEligible = $application->is_eligible; // true/false
$reasons = $application->ineligibility_reason; // String con razones

// Evaluación detallada
$evaluation = $application->latestEvaluation;

if ($evaluation) {
    // Verificar criterio específico
    $passedCourses = $evaluation->passedCriteria('required_courses');
    $passedKnowledge = $evaluation->passedCriteria('technical_knowledge');

    // Ver detalles completos
    $coursesDetail = $evaluation->required_courses_evaluation;
    $knowledgeDetail = $evaluation->technical_knowledge_evaluation;

    // Resumen estadístico
    $summary = $evaluation->getSummary();
    // [
    //   'is_eligible' => false,
    //   'total_criteria' => 8,
    //   'passed_criteria' => 6,
    //   'failed_criteria' => [...]
    // ]
}
```

---

### 3.2 Estadísticas por Convocatoria

```php
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;

$posting = JobPosting::find($postingId);

$applications = Application::whereHas('vacancy.jobProfileRequest.jobPosting',
    fn($q) => $q->where('id', $postingId)
)->get();

$stats = [
    'total' => $applications->count(),
    'aptos' => $applications->where('is_eligible', true)->count(),
    'no_aptos' => $applications->where('is_eligible', false)->count(),
    'pendientes' => $applications->whereNull('eligibility_checked_at')->count(),
    'evaluados' => $applications->whereNotNull('eligibility_checked_at')->count(),
];

// Porcentajes
$stats['porcentaje_aptos'] = ($stats['aptos'] / max($stats['evaluados'], 1)) * 100;
$stats['porcentaje_no_aptos'] = ($stats['no_aptos'] / max($stats['evaluados'], 1)) * 100;
```

---

### 3.3 Listar NO APTOS con Razones

```php
$notEligible = Application::whereHas('vacancy.jobProfileRequest.jobPosting',
        fn($q) => $q->where('id', $postingId)
    )
    ->where('is_eligible', false)
    ->with('latestEvaluation')
    ->get();

foreach ($notEligible as $application) {
    echo "Postulante: {$application->full_name}\n";
    echo "DNI: {$application->dni}\n";
    echo "Razones:\n";

    $reasons = explode("\n", $application->ineligibility_reason);
    foreach ($reasons as $reason) {
        echo "  - {$reason}\n";
    }

    echo "\n";
}
```

---

### 3.4 Análisis de Criterios Fallidos

```php
$application = Application::with('latestEvaluation')->find($applicationId);
$evaluation = $application->latestEvaluation;

$failedCriteria = $evaluation->getFailedCriteria();

foreach ($failedCriteria as $criterion) {
    echo "Criterio: {$criterion['criteria']}\n";
    echo "Razón: {$criterion['reason']}\n\n";
}

// Ejemplo de salida:
// Criterio: required_courses
// Razón: No cumple con capacitación requerida. Falta: Gestión Pública
//
// Criterio: technical_knowledge
// Razón: No cumple con conocimientos técnicos requeridos. Falta: SQL, Power BI
```

---

## 4. Gestión Manual

### 4.1 Modificar Resultado Manualmente

```php
// POST /admin/applications/evaluation/{application-id}/override

// Marcar como APTO manualmente
$application->update([
    'is_eligible' => true,
    'status' => ApplicationStatus::ELIGIBLE,
    'ineligibility_reason' => null,
    'eligibility_checked_at' => now(),
    'eligibility_checked_by' => auth()->id(),
]);

// Marcar como NO APTO manualmente
$application->update([
    'is_eligible' => false,
    'status' => ApplicationStatus::NOT_ELIGIBLE,
    'ineligibility_reason' => 'No cumple con experiencia específica verificada',
    'eligibility_checked_at' => now(),
    'eligibility_checked_by' => auth()->id(),
]);
```

**Desde formulario**:
```html
<form method="POST" action="{{ route('admin.applications.evaluation.override', $application->id) }}">
    @csrf

    <label>
        <input type="radio" name="is_eligible" value="1"> APTO
    </label>

    <label>
        <input type="radio" name="is_eligible" value="0" checked> NO APTO
    </label>

    <textarea name="reason" placeholder="Razón de no elegibilidad..."></textarea>

    <button type="submit">Guardar Cambio</button>
</form>
```

---

### 4.2 Publicar Resultados

```php
// POST /admin/applications/evaluation/{posting-id}/publish

use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Facades\DB;

$posting = JobPosting::find($postingId);

// Verificar que todas estén evaluadas
$pending = Application::where('status', ApplicationStatus::SUBMITTED)
    ->whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) => $q->where('id', $postingId))
    ->whereNull('eligibility_checked_at')
    ->count();

if ($pending > 0) {
    return "Error: Aún hay {$pending} postulaciones sin evaluar";
}

// Publicar
DB::transaction(function () use ($posting) {
    $posting->update([
        'results_published' => true,
        'results_published_at' => now(),
        'results_published_by' => auth()->id()
    ]);
});
```

---

## 5. Ejemplos de Código Completos

### 5.1 Controller de Evaluación Personalizado

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Application\Services\AutoGraderService;
use Modules\Application\Entities\Application;
use Modules\JobPosting\Entities\JobPosting;

class CustomEvaluationController extends Controller
{
    public function __construct(
        private AutoGraderService $autoGrader
    ) {}

    /**
     * Evaluar y generar reporte
     */
    public function evaluateWithReport(Request $request, string $postingId)
    {
        $posting = JobPosting::findOrFail($postingId);

        $applications = Application::whereHas('vacancy.jobProfileRequest.jobPosting',
            fn($q) => $q->where('id', $postingId)
        )
        ->where('status', ApplicationStatus::SUBMITTED)
        ->with([
            'academics.career',
            'experiences',
            'trainings',
            'professionalRegistrations',
            'knowledge',
        ])
        ->get();

        $results = [
            'total' => $applications->count(),
            'aptos' => [],
            'no_aptos' => [],
        ];

        foreach ($applications as $application) {
            $evaluation = $this->autoGrader->evaluateEligibility($application);
            $this->autoGrader->applyAutoGrading($application, auth()->id());

            $data = [
                'code' => $application->code,
                'name' => $application->full_name,
                'dni' => $application->dni,
                'evaluation' => $evaluation,
            ];

            if ($evaluation['is_eligible']) {
                $results['aptos'][] = $data;
            } else {
                $results['no_aptos'][] = $data;
            }
        }

        // Generar reporte Excel/PDF
        return $this->generateReport($results);
    }
}
```

---

### 5.2 Análisis de Brechas de Conocimiento

```php
use Modules\Application\Entities\Application;
use Illuminate\Support\Facades\DB;

/**
 * Analizar qué conocimientos técnicos son más comunes en NO APTOS
 */
function analyzeKnowledgeGaps(string $postingId)
{
    $applications = Application::whereHas('vacancy.jobProfileRequest.jobPosting',
        fn($q) => $q->where('id', $postingId)
    )
    ->where('is_eligible', false)
    ->with('latestEvaluation')
    ->get();

    $missingKnowledge = [];

    foreach ($applications as $application) {
        $evaluation = $application->latestEvaluation;

        if ($evaluation && !empty($evaluation->technical_knowledge_evaluation)) {
            $missing = $evaluation->technical_knowledge_evaluation['missing'] ?? [];

            foreach ($missing as $knowledge) {
                $knowledgeName = is_array($knowledge) ? $knowledge['name'] : $knowledge;

                if (!isset($missingKnowledge[$knowledgeName])) {
                    $missingKnowledge[$knowledgeName] = 0;
                }

                $missingKnowledge[$knowledgeName]++;
            }
        }
    }

    arsort($missingKnowledge);

    return $missingKnowledge;
}

// Uso
$gaps = analyzeKnowledgeGaps('conv-2024-001');

// Resultado:
// [
//   'SQL' => 12,
//   'Power BI' => 8,
//   'Python' => 5,
// ]
```

---

### 5.3 Notificación a Postulantes

```php
use Modules\Application\Events\ApplicationEvaluated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;

// En EventServiceProvider
Event::listen(ApplicationEvaluated::class, function ($event) {
    $application = $event->application;
    $evaluation = $event->evaluationResult;

    if ($evaluation['is_eligible']) {
        // Enviar email de felicitación
        Mail::to($application->email)->send(
            new EligibleNotification($application)
        );
    } else {
        // Enviar email con razones
        Mail::to($application->email)->send(
            new NotEligibleNotification($application, $evaluation['reasons'])
        );
    }
});
```

---

## 📊 Casos de Uso Avanzados

### Caso 1: Re-evaluación Después de Subsanación

```php
$application = Application::find($applicationId);

// Postulante agregó nuevos cursos/conocimientos
$application->trainings()->create([
    'course_name' => 'Gestión Pública Avanzada',
    'institution' => 'SENATI',
    'academic_hours' => 120,
]);

// Re-evaluar
$autoGrader = app(AutoGraderService::class);
$autoGrader->applyAutoGrading($application->fresh(), auth()->id());
```

---

### Caso 2: Evaluación Condicional

```php
// Solo evaluar si la postulación tiene todos los documentos
$application = Application::find($applicationId);

if ($application->documents()->where('is_verified', true)->count() >= 5) {
    $autoGrader->applyAutoGrading($application, auth()->id());
} else {
    $application->update([
        'requires_amendment' => true,
        'amendment_notes' => 'Faltan documentos por cargar',
    ]);
}
```

---

## 🔧 Troubleshooting

### Problema: Evaluación no encuentra cursos que sí existen

**Causa**: Nombres no coinciden exactamente

**Solución**: Verificar nombres en BD
```php
$application = Application::with('trainings')->find($id);
foreach ($application->trainings as $training) {
    echo "Curso registrado: '{$training->course_name}'\n";
}

// Comparar con:
$jobProfile = $application->vacancy->jobProfileRequest;
echo "Cursos requeridos: " . json_encode($jobProfile->required_courses);
```

---

### Problema: Nivel de conocimiento no se valida

**Causa**: Nivel guardado en formato incorrecto

**Solución**: Verificar formato
```php
$knowledge = ApplicationKnowledge::find($id);
echo "Nivel guardado: '{$knowledge->proficiency_level}'\n";
// Debe ser: 'BASICO', 'INTERMEDIO', o 'AVANZADO'
```

---

**Versión**: 1.0
**Fecha**: 2026-01-09
**Autor**: Equipo de Desarrollo CAS-MDSJ
