# Reevaluación de Elegibilidad - Documentación de Implementación

## Resumen

Funcionalidad para que usuarios con permiso puedan:
1. Ver postulaciones NO_APTO y pendientes de calificación
2. Aprobar/Rechazar manualmente con justificación
3. Generar PDF de resolución de reclamo

---

## 1. Flujo

```
Postulación NO_APTO o PENDIENTE
        ↓
Admin filtra por convocatoria/perfil
        ↓
Selecciona postulación a reevaluar
        ↓
Ve detalle de evaluación automática original
        ↓
Decide: APROBAR (→ APTO) o RECHAZAR (→ mantiene NO_APTO)
        ↓
Escribe justificación/resolución
        ↓
Genera PDF de resolución (opcional)
```

---

## 2. Entidad: EligibilityOverride

```php
// Modules/Application/app/Entities/EligibilityOverride.php

Schema: eligibility_overrides
- id (UUID, PK)
- application_id (UUID, FK → applications, unique)
- original_status (STRING) // NOT_ELIGIBLE, SUBMITTED, IN_REVIEW
- original_reason (TEXT, nullable) // razón original de NO_APTO
- new_status (STRING) // ELIGIBLE o NOT_ELIGIBLE
- decision (ENUM: APPROVED, REJECTED)
- resolution_type (STRING) // CLAIM, CORRECTION, OTHER
- resolution_summary (STRING) // "Reclamo procede por..." resumen corto
- resolution_detail (TEXT) // detalle completo de la resolución
- resolved_by (UUID, FK → users)
- resolved_at (TIMESTAMP)
- metadata (JSON, nullable) // datos adicionales
- created_at, updated_at
```

---

## 3. Enum: OverrideDecision

```php
// Modules/Application/app/Enums/OverrideDecisionEnum.php

enum OverrideDecisionEnum: string
{
    case APPROVED = 'APPROVED';   // Reclamo procede → cambia a APTO
    case REJECTED = 'REJECTED';   // Reclamo no procede → mantiene NO_APTO

    public function label(): string
    {
        return match($this) {
            self::APPROVED => 'Procede',
            self::REJECTED => 'No Procede',
        };
    }
}
```

---

## 4. Servicio: EligibilityOverrideService

```php
// Modules/Application/app/Services/EligibilityOverrideService.php

class EligibilityOverrideService
{
    /**
     * Obtener postulaciones que pueden ser reevaluadas
     * (NO_APTO sin override, o PENDIENTES de calificación)
     */
    public function getApplicationsForReview(string $jobPostingId, ?string $jobProfileId = null): Collection
    {
        return Application::where('job_posting_id', $jobPostingId)
            ->when($jobProfileId, fn($q) => $q->where('job_profile_id', $jobProfileId))
            ->where(function ($q) {
                $q->where('status', ApplicationStatus::NOT_ELIGIBLE)
                  ->orWhereIn('status', [
                      ApplicationStatus::SUBMITTED,
                      ApplicationStatus::IN_REVIEW
                  ]);
            })
            ->whereDoesntHave('eligibilityOverride') // sin override previo
            ->with(['applicant', 'jobProfile', 'latestEvaluation'])
            ->get();
    }

    /**
     * Aprobar postulación (cambiar a APTO)
     */
    public function approve(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType = 'CLAIM'
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType) {
            // 1. Crear registro de override
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $application->status->value,
                'original_reason' => $application->ineligibility_reason,
                'new_status' => ApplicationStatus::ELIGIBLE->value,
                'decision' => OverrideDecisionEnum::APPROVED,
                'resolution_type' => $resolutionType,
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
            ]);

            // 2. Actualizar Application
            $application->update([
                'is_eligible' => true,
                'status' => ApplicationStatus::ELIGIBLE,
                'ineligibility_reason' => null,
                'eligibility_checked_by' => $resolvedBy,
                'eligibility_checked_at' => now(),
            ]);

            // 3. Registrar en historial
            ApplicationHistory::create([
                'application_id' => $application->id,
                'event_type' => 'ELIGIBILITY_OVERRIDE',
                'description' => "Reevaluación: APROBADO - {$resolutionSummary}",
                'old_status' => $override->original_status,
                'new_status' => ApplicationStatus::ELIGIBLE->value,
                'performed_by' => $resolvedBy,
                'metadata' => [
                    'override_id' => $override->id,
                    'decision' => 'APPROVED',
                    'resolution_type' => $resolutionType,
                ],
            ]);

            return $override;
        });
    }

    /**
     * Rechazar reevaluación (mantener NO_APTO)
     */
    public function reject(
        Application $application,
        string $resolutionSummary,
        string $resolutionDetail,
        string $resolvedBy,
        string $resolutionType = 'CLAIM'
    ): EligibilityOverride {
        return DB::transaction(function () use ($application, $resolutionSummary, $resolutionDetail, $resolvedBy, $resolutionType) {
            // 1. Crear registro de override (sin cambiar estado)
            $override = EligibilityOverride::create([
                'application_id' => $application->id,
                'original_status' => $application->status->value,
                'original_reason' => $application->ineligibility_reason,
                'new_status' => ApplicationStatus::NOT_ELIGIBLE->value,
                'decision' => OverrideDecisionEnum::REJECTED,
                'resolution_type' => $resolutionType,
                'resolution_summary' => $resolutionSummary,
                'resolution_detail' => $resolutionDetail,
                'resolved_by' => $resolvedBy,
                'resolved_at' => now(),
            ]);

            // 2. Si estaba PENDIENTE, marcarlo como NO_APTO
            if ($application->status !== ApplicationStatus::NOT_ELIGIBLE) {
                $application->update([
                    'is_eligible' => false,
                    'status' => ApplicationStatus::NOT_ELIGIBLE,
                    'eligibility_checked_by' => $resolvedBy,
                    'eligibility_checked_at' => now(),
                ]);
            }

            // 3. Registrar en historial
            ApplicationHistory::create([
                'application_id' => $application->id,
                'event_type' => 'ELIGIBILITY_OVERRIDE',
                'description' => "Reevaluación: RECHAZADO - {$resolutionSummary}",
                'performed_by' => $resolvedBy,
                'metadata' => [
                    'override_id' => $override->id,
                    'decision' => 'REJECTED',
                    'resolution_type' => $resolutionType,
                ],
            ]);

            return $override;
        });
    }
}
```

---

## 5. Controlador

```php
// Modules/Application/app/Http/Controllers/EligibilityOverrideController.php

class EligibilityOverrideController extends Controller
{
    public function __construct(
        private EligibilityOverrideService $service
    ) {}

    /**
     * GET /api/eligibility-overrides
     * Listar postulaciones para reevaluar
     */
    public function index(Request $request)
    {
        $applications = $this->service->getApplicationsForReview(
            $request->job_posting_id,
            $request->job_profile_id
        );
        return ApplicationResource::collection($applications);
    }

    /**
     * GET /api/eligibility-overrides/{application}
     * Ver detalle de postulación con evaluación original
     */
    public function show(Application $application)
    {
        $application->load(['latestEvaluation', 'academics', 'experiences', 'eligibilityOverride']);
        return new ApplicationDetailResource($application);
    }

    /**
     * POST /api/eligibility-overrides/{application}/approve
     */
    public function approve(ApproveOverrideRequest $request, Application $application)
    {
        $override = $this->service->approve(
            $application,
            $request->resolution_summary,
            $request->resolution_detail,
            auth()->id(),
            $request->resolution_type ?? 'CLAIM'
        );
        return new EligibilityOverrideResource($override);
    }

    /**
     * POST /api/eligibility-overrides/{application}/reject
     */
    public function reject(RejectOverrideRequest $request, Application $application)
    {
        $override = $this->service->reject(
            $application,
            $request->resolution_summary,
            $request->resolution_detail,
            auth()->id(),
            $request->resolution_type ?? 'CLAIM'
        );
        return new EligibilityOverrideResource($override);
    }

    /**
     * GET /api/eligibility-overrides/{application}/pdf
     * Generar PDF de resolución
     */
    public function generatePdf(Application $application)
    {
        $override = $application->eligibilityOverride;
        if (!$override) {
            return response()->json(['error' => 'No hay resolución para esta postulación'], 404);
        }

        $pdf = PDF::loadView('document::templates.eligibility_resolution', [
            'application' => $application,
            'override' => $override,
            'posting' => $application->jobProfile->jobPosting,
        ]);

        return $pdf->download("resolucion-{$application->code}.pdf");
    }
}
```

---

## 6. Rutas API

```php
// Modules/Application/routes/api.php

Route::prefix('eligibility-overrides')
    ->middleware(['auth:sanctum', 'permission:eligibility.override'])
    ->group(function () {
        Route::get('/', [EligibilityOverrideController::class, 'index']);
        Route::get('/{application}', [EligibilityOverrideController::class, 'show']);
        Route::post('/{application}/approve', [EligibilityOverrideController::class, 'approve']);
        Route::post('/{application}/reject', [EligibilityOverrideController::class, 'reject']);
        Route::get('/{application}/pdf', [EligibilityOverrideController::class, 'generatePdf']);
    });
```

---

## 7. Request Validations

```php
// ApproveOverrideRequest.php
public function rules(): array
{
    return [
        'resolution_summary' => 'required|string|max:255',
        'resolution_detail' => 'required|string|min:20|max:2000',
        'resolution_type' => 'nullable|in:CLAIM,CORRECTION,OTHER',
    ];
}

// RejectOverrideRequest.php
public function rules(): array
{
    return [
        'resolution_summary' => 'required|string|max:255',
        'resolution_detail' => 'required|string|min:20|max:2000',
        'resolution_type' => 'nullable|in:CLAIM,CORRECTION,OTHER',
    ];
}
```

---

## 8. Migración

```php
// create_eligibility_overrides_table.php

Schema::create('eligibility_overrides', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('application_id')->unique();
    $table->string('original_status', 30);
    $table->text('original_reason')->nullable();
    $table->string('new_status', 30);
    $table->string('decision', 20); // APPROVED, REJECTED
    $table->string('resolution_type', 30)->default('CLAIM');
    $table->string('resolution_summary', 255);
    $table->text('resolution_detail');
    $table->uuid('resolved_by');
    $table->timestamp('resolved_at');
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('application_id')->references('id')->on('applications');
    $table->foreign('resolved_by')->references('id')->on('users');
});
```

---

## 9. Template PDF - Resolución de Reclamo

```blade
{{-- Modules/Document/resources/views/templates/eligibility_resolution.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resolución de Reclamo - {{ $application->code }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .institution-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
        }
        .institution-subtitle {
            font-size: 9pt;
            color: #666;
        }
        .document-title {
            background-color: #1e3a5f;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .info-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 3px solid #1e3a5f;
        }
        .info-row {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .result-box {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid;
            text-align: center;
        }
        .result-approved {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .result-rejected {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .result-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .result-approved .result-title { color: #155724; }
        .result-rejected .result-title { color: #721c24; }
        .resolution-section {
            margin: 20px 0;
        }
        .resolution-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            color: #1e3a5f;
        }
        .resolution-content {
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            min-height: 80px;
        }
        .original-reason {
            margin: 15px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 3px solid #ffc107;
        }
        .signatures {
            margin-top: 60px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin: 50px auto 5px;
        }
        .signature-name {
            font-weight: bold;
        }
        .signature-role {
            font-size: 9pt;
            color: #666;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }
    </style>
</head>
<body>
    {{-- Header institucional --}}
    <div class="header">
        <div class="institution-name">MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO</div>
        <div class="institution-subtitle">Provincia de Cusco - Región Cusco | Oficina de Recursos Humanos</div>
    </div>

    {{-- Título --}}
    <div class="document-title">
        Resolución de Reevaluación de Elegibilidad
    </div>

    {{-- Información de la convocatoria --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Convocatoria:</span>
            <span>{{ $posting->code }} - {{ $posting->title }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Perfil de Puesto:</span>
            <span>{{ $application->jobProfile->code }} - {{ $application->jobProfile->title }}</span>
        </div>
    </div>

    {{-- Información del postulante --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Código de Postulación:</span>
            <span>{{ $application->code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">DNI:</span>
            <span>{{ $application->applicant->document_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Postulante:</span>
            <span>{{ strtoupper($application->applicant->full_name) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado Original:</span>
            <span>{{ $override->original_status }}</span>
        </div>
    </div>

    {{-- Motivo original de NO APTO (si aplica) --}}
    @if($override->original_reason)
    <div class="original-reason">
        <strong>Motivo original de inelegibilidad:</strong><br>
        {{ $override->original_reason }}
    </div>
    @endif

    {{-- Resultado de la reevaluación --}}
    <div class="result-box {{ $override->decision->value === 'APPROVED' ? 'result-approved' : 'result-rejected' }}">
        <div class="result-title">
            @if($override->decision->value === 'APPROVED')
                EL RECLAMO PROCEDE
            @else
                EL RECLAMO NO PROCEDE
            @endif
        </div>
        <div style="margin-top: 5px;">
            Nuevo Estado: <strong>{{ $override->new_status }}</strong>
        </div>
    </div>

    {{-- Resolución --}}
    <div class="resolution-section">
        <div class="resolution-title">FUNDAMENTO DE LA RESOLUCIÓN:</div>
        <div class="resolution-content">
            <strong>{{ $override->resolution_summary }}</strong>
            <br><br>
            {{ $override->resolution_detail }}
        </div>
    </div>

    {{-- Datos de la resolución --}}
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Fecha de Resolución:</span>
            <span>{{ $override->resolved_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo de Resolución:</span>
            <span>{{ $override->resolution_type }}</span>
        </div>
    </div>

    {{-- Firma --}}
    <div class="signatures">
        <div class="signature-line"></div>
        <div class="signature-name">{{ $override->resolver->full_name ?? 'Responsable' }}</div>
        <div class="signature-role">Comisión de Selección CAS</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Documento generado por Sistema CAS - MDSJ | {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
```

---

## 10. Relación en Application

```php
// Modules/Application/app/Entities/Application.php

public function eligibilityOverride(): HasOne
{
    return $this->hasOne(EligibilityOverride::class);
}
```

---

## 11. Permisos

```php
// Nuevo permiso a crear en seeder
'eligibility.override' => 'Reevaluar elegibilidad de postulaciones'
```

---

## 12. Archivos a Crear

```
Modules/Application/
├── app/
│   ├── Entities/
│   │   └── EligibilityOverride.php
│   ├── Enums/
│   │   └── OverrideDecisionEnum.php
│   ├── Services/
│   │   └── EligibilityOverrideService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── EligibilityOverrideController.php
│   │   ├── Requests/
│   │   │   ├── ApproveOverrideRequest.php
│   │   │   └── RejectOverrideRequest.php
│   │   └── Resources/
│   │       └── EligibilityOverrideResource.php
├── database/
│   └── migrations/
│       └── xxxx_create_eligibility_overrides_table.php
└── routes/
    └── web.php (agregar rutas)
    

Modules/Document/
└── resources/views/templates/
    └── eligibility_resolution.blade.php
```

---

## 13. Orden de Implementación

1. Migración `eligibility_overrides`
2. Enum `OverrideDecisionEnum`
3. Entidad `EligibilityOverride`
4. Relación en `Application`
5. Servicio `EligibilityOverrideService`
6. Requests de validación
7. Controlador
8. Rutas API
9. Template PDF
10. Permiso en seeder
