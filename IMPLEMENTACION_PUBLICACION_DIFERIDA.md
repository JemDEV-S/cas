# 📋 Implementación: Flujo de Publicación Diferida con Firma Digital

> **Sistema CAS - Gestión de Convocatorias**
> **Fecha:** Diciembre 2025
> **Arquitectura:** Laravel 11 + nwidart/laravel-modules + DDD + Event-Driven

---

## 🎯 Objetivo

Implementar flujo donde la convocatoria **NO se publica inmediatamente**. Primero se genera un PDF consolidado (vertical A4) con todos los perfiles aprobados en formato de tabla profesional estilo municipalidad, se asignan los jurados titulares activos para firmar digitalmente, y **solo cuando todos firmen** la convocatoria pasa a estado `PUBLICADA`.

---

## 📐 Arquitectura del Flujo

```
Admin solicita publicación
         ↓
JobPosting: BORRADOR → EN_FIRMA
         ↓
Event: JobPostingPublicationRequested
         ↓
GenerateConvocatoriaPdf (Document)
  • Obtiene perfiles APPROVED
  • Genera PDF consolidado vertical
  • Crea workflow de firmas
         ↓
AssignJuriesToSign (Jury)
  • Obtiene jurados TITULARES activos de la convocatoria
  • Los agrega al workflow como firmantes
         ↓
Jurados firman usando FirmaPerú
         ↓
Event: DocumentFullySigned
         ↓
PublishJobPostingAfterSignatures
  • JobPosting → PUBLICADA
  • published_at = now()
         ↓
Event: JobPostingPublished
         ↓
ActivateJobProfiles
  • Perfiles APPROVED → ACTIVE
```

---

## 🔧 Implementación por Fases

### **FASE 1: Actualización de Enums**

#### 1.1. JobPostingStatusEnum
**Archivo:** `Modules/JobPosting/Enums/JobPostingStatusEnum.php`

```php
enum JobPostingStatusEnum: string
{
    case BORRADOR = 'BORRADOR';
    case EN_FIRMA = 'EN_FIRMA';           // ← NUEVO
    case PUBLICADA = 'PUBLICADA';
    case EN_PROCESO = 'EN_PROCESO';
    case FINALIZADA = 'FINALIZADA';
    case CANCELADA = 'CANCELADA';

    public function label(): string
    {
        return match($this) {
            self::BORRADOR => 'Borrador',
            self::EN_FIRMA => 'En Proceso de Firma',  // ← NUEVO
            self::PUBLICADA => 'Publicada',
            self::EN_PROCESO => 'En Proceso',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::BORRADOR => 'secondary',
            self::EN_FIRMA => 'warning',  // ← NUEVO
            self::PUBLICADA => 'success',
            self::EN_PROCESO => 'info',
            self::FINALIZADA => 'primary',
            self::CANCELADA => 'danger',
        };
    }

    public function canBePublished(): bool
    {
        return $this === self::BORRADOR;
    }
}
```

#### 1.2. DocumentCategoryEnum
**Archivo:** `Modules/Document/app/Enums/DocumentCategoryEnum.php`

```php
enum DocumentCategoryEnum: string
{
    case PERFIL = 'perfil';
    case CONVOCATORIA_COMPLETA = 'convocatoria_completa';  // ← NUEVO
    case ACTA = 'acta';
    case EVALUACION = 'evaluacion';
    case CONTRATO = 'contrato';

    public function label(): string
    {
        return match($this) {
            self::PERFIL => 'Perfil de Puesto',
            self::CONVOCATORIA_COMPLETA => 'Convocatoria Completa',  // ← NUEVO
            self::ACTA => 'Acta',
            self::EVALUACION => 'Evaluación',
            self::CONTRATO => 'Contrato',
        };
    }
}
```

---

### **FASE 2: Eventos**

#### 2.1. JobPostingPublicationRequested
**Archivo:** `Modules/JobPosting/app/Events/JobPostingPublicationRequested.php`

```php
<?php

declare(strict_types=1);

namespace Modules\JobPosting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobPosting\Entities\JobPosting;

class JobPostingPublicationRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly JobPosting $jobPosting
    ) {}
}
```

#### 2.2. JobPostingPublished
**Archivo:** `Modules/JobPosting/app/Events/JobPostingPublished.php`

```php
<?php

declare(strict_types=1);

namespace Modules\JobPosting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobPosting\Entities\JobPosting;

class JobPostingPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly JobPosting $jobPosting
    ) {}
}
```

#### 2.3. DocumentFullySigned
**Archivo:** `Modules/Document/app/Events/DocumentFullySigned.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Document\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\GeneratedDocument;

class DocumentFullySigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly GeneratedDocument $document
    ) {}
}
```

---

### **FASE 3: Listeners**

#### 3.1. GenerateConvocatoriaPdf
**Archivo:** `Modules/Document/app/Listeners/GenerateConvocatoriaPdf.php`

**Responsabilidad:** Genera PDF consolidado con todos los perfiles aprobados.

```php
<?php

declare(strict_types=1);

namespace Modules\Document\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\JobPosting\Events\JobPostingPublicationRequested;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Document\Entities\DocumentTemplate;
use Modules\Document\Services\DocumentService;
use Modules\Document\Services\SignatureService;
use Modules\Document\Events\DocumentGenerated;

class GenerateConvocatoriaPdf
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly SignatureService $signatureService
    ) {}

    public function handle(JobPostingPublicationRequested $event): void
    {
        $jobPosting = $event->jobPosting;

        // 1. Obtener plantilla activa
        $template = DocumentTemplate::where('code', 'TPL_CONVOCATORIA_COMPLETA')
            ->where('status', 'active')
            ->first();

        if (!$template) {
            Log::warning('Plantilla TPL_CONVOCATORIA_COMPLETA no encontrada', [
                'job_posting_id' => $jobPosting->id,
            ]);
            return;
        }

        // 2. Obtener perfiles aprobados con todas las relaciones necesarias
        $approvedProfiles = $jobPosting->jobProfiles()
            ->where('status', 'approved')
            ->with([
                'organizationalUnit',
                'requestingUnit.parent',
                'positionCode',
                'requestedBy',
                'reviewedBy',
                'approvedBy'
            ])
            ->get();

        if ($approvedProfiles->isEmpty()) {
            Log::warning('No hay perfiles aprobados para generar convocatoria', [
                'job_posting_id' => $jobPosting->id,
            ]);
            return;
        }

        // 3. Preparar datos para el PDF (texto en mayúsculas según estándar municipal)
        $data = $this->prepareConvocatoriaData($jobPosting, $approvedProfiles);

        // 4. Generar documento usando DocumentService
        $document = $this->documentService->generateFromTemplate(
            template: $template,
            documentable: $jobPosting,
            data: $data
        );

        // 5. Crear workflow de firmas secuencial (sin firmantes aún)
        if ($template->requiresSignature()) {
            $this->signatureService->createWorkflow(
                document: $document,
                signers: [], // Se asignarán en el siguiente listener
                workflowType: $template->getSignatureWorkflowType()
            );
        }

        // 6. Disparar evento de documento generado
        event(new DocumentGenerated($document, auth()->id()));

        Log::info('PDF de convocatoria completa generado exitosamente', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'profiles_count' => $approvedProfiles->count(),
            'total_vacancies' => $approvedProfiles->sum('total_vacancies'),
        ]);
    }

    /**
     * Prepara los datos para la generación del PDF
     */
    private function prepareConvocatoriaData(JobPosting $jobPosting, $approvedProfiles): array
    {
        return [
            'title' => "CONVOCATORIA {$jobPosting->code} - BASES INTEGRADAS",
            'convocatoria_codigo' => mb_strtoupper($jobPosting->code ?? ''),
            'convocatoria_nombre' => mb_strtoupper($jobPosting->name ?? ''),
            'proceso_nombre' => mb_strtoupper($jobPosting->selection_process_name ?? ''),
            'año' => $jobPosting->year,
            'total_perfiles' => $approvedProfiles->count(),
            'total_vacantes' => $approvedProfiles->sum('total_vacancies'),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'perfiles' => $approvedProfiles->map(function($profile) {
                return [
                    'codigo' => mb_strtoupper($profile->code ?? ''),
                    'titulo' => mb_strtoupper($profile->title ?? ''),
                    'nombre_perfil' => mb_strtoupper($profile->profile_name ?? ''),
                    'unidad_organica' => mb_strtoupper($profile->organizationalUnit?->name ?? ''),
                    'vacantes' => $profile->total_vacancies,
                    'tipo_contrato' => mb_strtoupper($profile->contract_type?->label() ?? ''),
                    'regimen_laboral' => mb_strtoupper($profile->work_regime?->label() ?? ''),
                    'nivel_educativo' => mb_strtoupper($profile->education_level_label ?? ''),
                    'experiencia_general' => $profile->general_experience_years?->years ?? 0,
                    'experiencia_especifica' => $profile->specific_experience_years?->years ?? 0,
                    'remuneracion' => $profile->formatted_salary ?? '',
                    'ubicacion' => mb_strtoupper($profile->work_location ?? ''),
                    'funciones_principales' => $this->toUpperArray($profile->main_functions ?? []),
                    'competencias_requeridas' => $this->toUpperArray($profile->required_competencies ?? []),
                    'conocimientos' => $this->toUpperArray($profile->knowledge_areas ?? []),
                ];
            })->toArray(),
        ];
    }

    /**
     * Convierte array de strings a mayúsculas
     */
    private function toUpperArray(array $data): array
    {
        return array_map(fn($item) => is_string($item) ? mb_strtoupper($item) : $item, $data);
    }
}
```

#### 3.2. AssignJuriesToSign
**Archivo:** `Modules/Jury/app/Listeners/AssignJuriesToSign.php`

**Responsabilidad:** Asigna jurados titulares activos al workflow de firmas.

```php
<?php

declare(strict_types=1);

namespace Modules\Jury\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Document\Events\DocumentGenerated;
use Modules\Document\Enums\DocumentCategoryEnum;
use Modules\Jury\Entities\JuryAssignment;
use Modules\Jury\Enums\MemberType;
use Modules\JobPosting\Entities\JobPosting;

class AssignJuriesToSign
{
    public function handle(DocumentGenerated $event): void
    {
        $document = $event->document;

        // Solo procesar documentos de convocatoria completa
        if (!$this->isConvocatoriaDocument($document)) {
            return;
        }

        // Verificar que el documentable sea JobPosting
        if (!$document->documentable instanceof JobPosting) {
            return;
        }

        $jobPosting = $document->documentable;

        // Obtener workflow del documento
        $workflow = $document->signatureWorkflow;
        if (!$workflow) {
            Log::warning('No se encontró workflow de firmas para el documento', [
                'document_id' => $document->id,
            ]);
            return;
        }

        // Obtener jurados titulares activos de esta convocatoria ordenados
        $titularJurors = JuryAssignment::where('job_posting_id', $jobPosting->id)
            ->where('member_type', MemberType::TITULAR)
            ->where('is_active', true)
            ->with('juryMember.user')
            ->orderBy('order')
            ->get();

        if ($titularJurors->isEmpty()) {
            Log::warning('No hay jurados titulares activos para asignar firmas', [
                'job_posting_id' => $jobPosting->id,
                'document_id' => $document->id,
            ]);
            return;
        }

        // Preparar array de firmantes con formato requerido por createWorkflow
        $signers = $titularJurors->map(function($assignment) {
            return [
                'user_id' => $assignment->juryMember->user_id,
                'role' => $assignment->role_in_jury?->label() ?? 'JURADO',
                'type' => 'firma',
            ];
        })->toArray();

        // Actualizar el workflow con los firmantes
        $this->updateWorkflowSigners($workflow, $signers, $document);

        Log::info('Jurados titulares asignados como firmantes', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'signers_count' => count($signers),
            'signers' => $titularJurors->pluck('juryMember.user.name')->toArray(),
        ]);
    }

    /**
     * Actualiza el workflow existente con los firmantes
     */
    private function updateWorkflowSigners($workflow, array $signers, $document): void
    {
        $totalSteps = count($signers);

        // Actualizar workflow
        $workflow->update([
            'total_steps' => $totalSteps,
            'signers_order' => $signers,
        ]);

        // Crear registros de firma para cada firmante
        foreach ($signers as $index => $signer) {
            \Modules\Document\Entities\DigitalSignature::create([
                'generated_document_id' => $document->id,
                'user_id' => $signer['user_id'],
                'signature_type' => $signer['type'] ?? 'firma',
                'signature_order' => $index + 1,
                'role' => $signer['role'] ?? null,
                'status' => 'pending',
            ]);
        }

        // Actualizar documento con el primer firmante
        $document->update([
            'total_signatures_required' => $totalSteps,
            'current_signer_id' => $signers[0]['user_id'],
        ]);

        // Disparar evento para notificar al primer firmante
        event(new \Modules\Document\Events\DocumentReadyForSignature(
            $document,
            $signers[0]['user_id']
        ));
    }

    /**
     * Verifica si el documento es de tipo convocatoria completa
     */
    private function isConvocatoriaDocument($document): bool
    {
        return $document->template
            && $document->template->category === DocumentCategoryEnum::CONVOCATORIA_COMPLETA->value;
    }
}
```

#### 3.3. PublishJobPostingAfterSignatures
**Archivo:** `Modules/JobPosting/app/Listeners/PublishJobPostingAfterSignatures.php`

**Responsabilidad:** Publica la convocatoria cuando todas las firmas están completas.

```php
<?php

declare(strict_types=1);

namespace Modules\JobPosting\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Document\Events\DocumentFullySigned;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Events\JobPostingPublished;

class PublishJobPostingAfterSignatures
{
    public function handle(DocumentFullySigned $event): void
    {
        $document = $event->document;

        // Verificar que el documento pertenece a un JobPosting
        if (!$document->documentable instanceof JobPosting) {
            return;
        }

        $jobPosting = $document->documentable;

        // Verificar que está en estado EN_FIRMA
        if ($jobPosting->status !== JobPostingStatusEnum::EN_FIRMA) {
            Log::warning('JobPosting no está en estado EN_FIRMA', [
                'job_posting_id' => $jobPosting->id,
                'current_status' => $jobPosting->status->value,
                'document_id' => $document->id,
            ]);
            return;
        }

        // Cambiar estado a PUBLICADA
        $jobPosting->status = JobPostingStatusEnum::PUBLICADA;
        $jobPosting->published_at = now();
        $jobPosting->save();

        // Disparar evento de publicación
        event(new JobPostingPublished($jobPosting));

        Log::info('Convocatoria publicada después de firmas completas', [
            'job_posting_id' => $jobPosting->id,
            'document_id' => $document->id,
            'published_at' => $jobPosting->published_at->format('d/m/Y H:i:s'),
            'signatures_completed' => $document->signatures_completed,
        ]);
    }
}
```

#### 3.4. ActivateJobProfiles
**Archivo:** `Modules/JobProfile/app/Listeners/ActivateJobProfiles.php`

**Responsabilidad:** Activa todos los perfiles aprobados cuando se publica la convocatoria.

```php
<?php

declare(strict_types=1);

namespace Modules\JobProfile\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\JobPosting\Events\JobPostingPublished;
use Modules\JobProfile\Enums\JobProfileStatusEnum;

class ActivateJobProfiles
{
    public function handle(JobPostingPublished $event): void
    {
        $jobPosting = $event->jobPosting;

        // Obtener perfiles aprobados
        $approvedProfiles = $jobPosting->jobProfiles()
            ->where('status', JobProfileStatusEnum::APPROVED->value)
            ->get();

        if ($approvedProfiles->isEmpty()) {
            return;
        }

        // Activar cada perfil
        $updated = 0;
        foreach ($approvedProfiles as $profile) {
            $profile->status = JobProfileStatusEnum::ACTIVE;
            $profile->save();
            $updated++;
        }

        Log::info('Perfiles activados después de publicación', [
            'job_posting_id' => $jobPosting->id,
            'profiles_activated' => $updated,
            'profile_codes' => $approvedProfiles->pluck('code')->toArray(),
        ]);
    }
}
```

---

### **FASE 4: Modificación de Servicios**

#### 4.1. JobPostingService::publish()
**Archivo:** `Modules/JobPosting/Services/JobPostingService.php`

**Modificar método existente (línea ~131):**

```php
/**
 * Publica una convocatoria (inicia flujo de firma)
 */
public function publish(int $id): JobPosting
{
    $jobPosting = $this->repository->findOrFail($id);

    // Validar estado actual
    if (!$jobPosting->status->canBePublished()) {
        throw new \Exception('La convocatoria no puede ser publicada en su estado actual');
    }

    // Validar que tenga al menos un perfil aprobado
    $approvedProfilesCount = $jobPosting->jobProfiles()
        ->where('status', 'approved')
        ->count();

    if ($approvedProfilesCount === 0) {
        throw new \Exception('La convocatoria debe tener al menos un perfil aprobado para ser publicada');
    }

    // CAMBIO: Ya no publicar directamente, pasar a EN_FIRMA
    $jobPosting->status = JobPostingStatusEnum::EN_FIRMA;
    $jobPosting->save();

    // Disparar evento para iniciar flujo de generación de documento y firma
    event(new JobPostingPublicationRequested($jobPosting));

    Log::info('Convocatoria enviada a proceso de firma', [
        'job_posting_id' => $jobPosting->id,
        'approved_profiles' => $approvedProfilesCount,
    ]);

    return $jobPosting->fresh();
}
```

#### 4.2. SignatureService::advanceWorkflow()
**Archivo:** `Modules/Document/app/Services/SignatureService.php`

**Agregar disparo de evento después de línea ~178 (dentro del bloque `isFullySigned()`):**

```php
protected function advanceWorkflow(GeneratedDocument $document): void
{
    $workflow = $document->signatureWorkflow()->first();

    if (!$workflow) {
        return;
    }

    // Si ya se completaron todas las firmas
    if ($document->isFullySigned()) {
        $workflow->markAsCompleted();

        $document->refresh();
        $lastSignedPath = $document->signatures
            ->where('status', 'signed')
            ->whereNotNull('signed_document_path')
            ->sortByDesc('signature_order')
            ->first()
            ?->signed_document_path;

        $document->update([
            'status' => 'signed',
            'signature_status' => 'completed',
            'current_signer_id' => null,
            'signed_pdf_path' => $lastSignedPath,
        ]);

        DocumentAudit::log(
            $document->id,
            'fully_signed',
            $document->current_signer_id ?? auth()->id(),
            'Documento completamente firmado'
        );

        // ← AGREGAR ESTE EVENTO NUEVO
        event(new \Modules\Document\Events\DocumentFullySigned($document));

        return;
    }

    // ... resto del código sin cambios
}
```

---

### **FASE 5: Registro de Eventos en EventServiceProviders**

#### 5.1. JobPosting EventServiceProvider
**Archivo:** `Modules/JobPosting/app/Providers/EventServiceProvider.php`

```php
<?php

namespace Modules\JobPosting\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Evento cuando se solicita publicación de convocatoria
        \Modules\JobPosting\Events\JobPostingPublicationRequested::class => [],

        // Cuando la convocatoria se publica finalmente (después de firmas)
        \Modules\JobPosting\Events\JobPostingPublished::class => [
            \Modules\JobProfile\Listeners\ActivateJobProfiles::class,
        ],

        // Cuando un documento se firma completamente
        \Modules\Document\Events\DocumentFullySigned::class => [
            \Modules\JobPosting\Listeners\PublishJobPostingAfterSignatures::class,
        ],
    ];

    protected bool $shouldDiscoverEvents = true;
}
```

#### 5.2. Document EventServiceProvider
**Archivo:** `Modules/Document/app/Providers/EventServiceProvider.php`

**Agregar a `$listen` existente:**

```php
protected $listen = [
    // ... eventos existentes ...

    // Cuando se solicita publicación de convocatoria → generar PDF
    \Modules\JobPosting\Events\JobPostingPublicationRequested::class => [
        \Modules\Document\Listeners\GenerateConvocatoriaPdf::class,
    ],

    // Cuando se genera un documento → asignar jurados si es convocatoria
    \Modules\Document\Events\DocumentGenerated::class => [
        \Modules\Jury\Listeners\AssignJuriesToSign::class,  // ← AGREGAR
    ],

    // Evento nuevo de documento completamente firmado
    \Modules\Document\Events\DocumentFullySigned::class => [],  // ← NUEVO

    // ... resto de eventos ...
];
```

#### 5.3. Jury EventServiceProvider
**Archivo:** `Modules/Jury/app/Providers/EventServiceProvider.php`

**Crear archivo si no existe:**

```php
<?php

namespace Modules\Jury\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Modules\Document\Events\DocumentGenerated::class => [
            \Modules\Jury\Listeners\AssignJuriesToSign::class,
        ],
    ];

    protected bool $shouldDiscoverEvents = false;
}
```

**Registrar en `Modules/Jury/app/Providers/JuryServiceProvider.php`:**

```php
public function register(): void
{
    $this->app->register(RouteServiceProvider::class);
    $this->app->register(EventServiceProvider::class); // ← AGREGAR
}
```

#### 5.4. JobProfile EventServiceProvider
**Archivo:** `Modules/JobProfile/app/Providers/EventServiceProvider.php`

**Agregar a `$listen` existente:**

```php
protected $listen = [
    // ... eventos existentes ...

    // Cuando se publica convocatoria → activar perfiles
    \Modules\JobPosting\Events\JobPostingPublished::class => [
        \Modules\JobProfile\Listeners\ActivateJobProfiles::class,  // ← AGREGAR
    ],
];
```

---

### **FASE 6: Vista PDF Consolidado**

#### 6.1. Blade Template
**Archivo:** `Modules/Document/resources/views/templates/convocatoria_completa.blade.php`

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Convocatoria Completa' }}</title>
    <style>
        @page { margin: 2cm 1.5cm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #222;
        }

        /* ENCABEZADO */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #2c3e50;
        }

        .header h1 {
            font-size: 13pt;
            color: #1a1a1a;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header .subtitle {
            font-size: 10pt;
            color: #444;
            margin-bottom: 3px;
        }

        .header .code {
            font-size: 9pt;
            color: #666;
            font-weight: bold;
        }

        /* RESUMEN */
        .summary {
            background: #ecf0f1;
            padding: 8px 10px;
            margin-bottom: 12px;
            border-left: 4px solid #3498db;
            font-size: 8.5pt;
        }

        .summary-item {
            display: inline-block;
            margin-right: 15px;
            font-weight: bold;
        }

        .summary-item span {
            color: #2c3e50;
            font-size: 9.5pt;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title {
            background: #2c3e50;
            color: white;
            padding: 6px 10px;
            margin: 12px 0 8px 0;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* TABLA DE PERFILES */
        .profiles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7.5pt;
        }

        .profiles-table thead {
            background: #34495e;
            color: white;
        }

        .profiles-table th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            font-size: 7.5pt;
            vertical-align: middle;
        }

        .profiles-table td {
            padding: 5px 4px;
            border: 1px solid #bdc3c7;
            vertical-align: top;
        }

        .profiles-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .profiles-table tbody tr:hover {
            background: #e8f4f8;
        }

        .profile-code {
            font-weight: bold;
            color: #2c3e50;
            font-size: 8pt;
        }

        .vacancies {
            text-align: center;
            font-weight: bold;
            color: #27ae60;
            font-size: 9pt;
        }

        .salary {
            font-weight: bold;
            color: #c0392b;
            white-space: nowrap;
            font-size: 8pt;
        }

        .list-compact {
            margin: 0;
            padding-left: 12px;
            font-size: 7pt;
            line-height: 1.2;
        }

        .list-compact li {
            margin-bottom: 2px;
        }

        /* PIE DE PÁGINA */
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 7.5pt;
            color: #777;
        }

        .page-break {
            page-break-after: always;
        }

        .detail-box {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 8px;
            page-break-inside: avoid;
        }

        .detail-box h3 {
            color: #2c3e50;
            font-size: 9pt;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        .detail-label {
            color: #34495e;
            font-weight: bold;
            font-size: 8pt;
            margin-top: 5px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="header">
        <h1>MUNICIPALIDAD DISTRITAL DE SAN JUAN DE MIRAFLORES</h1>
        <div class="subtitle">{{ $convocatoria_nombre }}</div>
        <div class="code">{{ $convocatoria_codigo }} | {{ $proceso_nombre }} - {{ $año }}</div>
    </div>

    <!-- RESUMEN EJECUTIVO -->
    <div class="summary">
        <div class="summary-item">
            TOTAL PERFILES: <span>{{ $total_perfiles }}</span>
        </div>
        <div class="summary-item">
            TOTAL VACANTES: <span>{{ $total_vacantes }}</span>
        </div>
        <div class="summary-item">
            FECHA: <span>{{ $fecha_generacion }}</span>
        </div>
    </div>

    <!-- TABLA DE PERFILES CONVOCADOS -->
    <div class="section-title">I. Perfiles Convocados</div>

    @if(count($perfiles) > 0)
        <table class="profiles-table">
            <thead>
                <tr>
                    <th style="width: 7%;">CÓD.</th>
                    <th style="width: 18%;">CARGO</th>
                    <th style="width: 15%;">UNIDAD ORGÁNICA</th>
                    <th style="width: 5%;">VAC.</th>
                    <th style="width: 12%;">CONTRATO</th>
                    <th style="width: 13%;">EDUCACIÓN</th>
                    <th style="width: 10%;">EXPERIENCIA</th>
                    <th style="width: 10%;">REMUN.</th>
                    <th style="width: 10%;">UBICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($perfiles as $perfil)
                <tr>
                    <td class="profile-code">{{ $perfil['codigo'] }}</td>
                    <td>
                        <strong>{{ $perfil['titulo'] }}</strong><br>
                        <small style="font-size: 6.5pt;">{{ $perfil['nombre_perfil'] }}</small>
                    </td>
                    <td style="font-size: 7pt;">{{ $perfil['unidad_organica'] }}</td>
                    <td class="vacancies">{{ $perfil['vacantes'] }}</td>
                    <td style="font-size: 7pt;">
                        {{ $perfil['tipo_contrato'] }}<br>
                        <small style="font-size: 6.5pt;">{{ $perfil['regimen_laboral'] }}</small>
                    </td>
                    <td style="font-size: 7pt;">{{ $perfil['nivel_educativo'] }}</td>
                    <td style="font-size: 7pt;">
                        <strong>Gral:</strong> {{ $perfil['experiencia_general'] }} años<br>
                        <strong>Esp:</strong> {{ $perfil['experiencia_especifica'] }} años
                    </td>
                    <td class="salary">{{ $perfil['remuneracion'] }}</td>
                    <td style="font-size: 7pt;">{{ $perfil['ubicacion'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- DETALLE DE FUNCIONES Y COMPETENCIAS -->
        <div class="page-break"></div>

        <div class="section-title">II. Funciones y Competencias por Perfil</div>

        @foreach($perfiles as $index => $perfil)
            <div class="detail-box">
                <h3>{{ $perfil['codigo'] }} - {{ $perfil['titulo'] }}</h3>

                @if(!empty($perfil['funciones_principales']))
                    <div class="detail-label">FUNCIONES PRINCIPALES:</div>
                    <ul class="list-compact">
                        @foreach($perfil['funciones_principales'] as $funcion)
                            <li>{{ $funcion }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($perfil['competencias_requeridas']))
                    <div class="detail-label">COMPETENCIAS REQUERIDAS:</div>
                    <ul class="list-compact">
                        @foreach($perfil['competencias_requeridas'] as $competencia)
                            <li>{{ $competencia }}</li>
                        @endforeach
                    </ul>
                @endif

                @if(!empty($perfil['conocimientos']))
                    <div class="detail-label">CONOCIMIENTOS:</div>
                    <ul class="list-compact">
                        @foreach($perfil['conocimientos'] as $conocimiento)
                            <li>{{ $conocimiento }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if(($index + 1) % 2 === 0 && ($index + 1) < count($perfiles))
                <div class="page-break"></div>
            @endif
        @endforeach
    @else
        <p style="text-align: center; padding: 30px; color: #999;">
            No hay perfiles aprobados para mostrar
        </p>
    @endif

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p><strong>Documento Oficial</strong> | Generado: {{ $fecha_generacion }}</p>
        <p>Municipalidad Distrital de San Juan de Miraflores | Sistema CAS</p>
    </div>
</body>
</html>
```

---

### **FASE 7: Template en Base de Datos**

#### 7.1. Seeder
**Archivo:** `Modules/Document/database/seeders/ConvocatoriaTemplateSeeder.php`

```php
<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentTemplate;

class ConvocatoriaTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTemplate::updateOrCreate(
            ['code' => 'TPL_CONVOCATORIA_COMPLETA'],
            [
                'name' => 'Convocatoria Completa - Bases Integradas',
                'category' => 'convocatoria_completa',
                'status' => 'active',
                'content' => null, // Se carga directamente la vista por convención
                'signature_required' => true,
                'signature_workflow_type' => 'sequential',
                'paper_size' => 'A4',
                'orientation' => 'portrait', // Vertical
                'margins' => json_encode([
                    'top' => 20,
                    'right' => 15,
                    'bottom' => 20,
                    'left' => 15,
                ]),
            ]
        );
    }
}
```

**Ejecutar:**
```bash
php artisan db:seed --class=Modules\\Document\\Database\\Seeders\\ConvocatoriaTemplateSeeder
```

**Actualizar TemplateRendererService para soportar vistas:**

**Archivo:** `Modules/Document/app/Services/TemplateRendererService.php`

```php
public function render(?string $templateContent, array $data): string
{
    // Si no hay content, buscar vista por convención
    if (empty($templateContent)) {
        $templateCode = $data['template_code'] ?? null;
        if ($templateCode) {
            $viewName = 'document::templates.' . strtolower(str_replace('TPL_', '', $templateCode));

            if (view()->exists($viewName)) {
                return view($viewName, $data)->render();
            }
        }
    }

    // Renderizar contenido del template existente
    return view(['template' => $templateContent], $data)->render();
}
```

---

### **FASE 8: Comando de Regeneración**

#### 8.1. Comando Artisan
**Archivo:** `Modules/Document/app/Console/Commands/RegenerateConvocatoriaDocument.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Document\Console\Commands;

use Illuminate\Console\Command;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Document\Entities\GeneratedDocument;
use Modules\Document\Services\DocumentService;
use Modules\JobPosting\Events\JobPostingPublicationRequested;

class RegenerateConvocatoriaDocument extends Command
{
    protected $signature = 'convocatoria:regenerate-document
                            {job-posting-id : ID de la convocatoria}
                            {--force : Forzar regeneración eliminando firmas existentes}';

    protected $description = 'Regenera el documento consolidado de una convocatoria';

    public function __construct(
        private readonly DocumentService $documentService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jobPostingId = $this->argument('job-posting-id');
        $force = $this->option('force');

        // Buscar convocatoria
        $jobPosting = JobPosting::find($jobPostingId);

        if (!$jobPosting) {
            $this->error("❌ Convocatoria #{$jobPostingId} no encontrada");
            return Command::FAILURE;
        }

        $this->info("📋 Convocatoria: {$jobPosting->code} - {$jobPosting->name}");

        // Buscar documento existente
        $document = GeneratedDocument::where('documentable_type', JobPosting::class)
            ->where('documentable_id', $jobPostingId)
            ->whereHas('template', fn($q) => $q->where('code', 'TPL_CONVOCATORIA_COMPLETA'))
            ->first();

        if (!$document) {
            $this->warn("⚠️  No existe documento previo. Generando nuevo...");
            return $this->generateNew($jobPosting);
        }

        $this->info("📄 Documento encontrado: {$document->code}");

        // Verificar firmas
        if ($document->hasAnySignature()) {
            if (!$force) {
                $this->error("❌ El documento tiene firmas realizadas");
                $this->warn("   Use --force para regenerar eliminando las firmas");
                return Command::FAILURE;
            }

            if (!$this->confirm('⚠️  Esto eliminará TODAS las firmas existentes. ¿Continuar?')) {
                $this->info('Operación cancelada');
                return Command::SUCCESS;
            }

            $this->deleteSignatures($document);
        }

        // Regenerar documento
        try {
            $this->info('🔄 Regenerando documento...');

            $this->documentService->regeneratePDF($document);

            $this->info('✅ Documento regenerado exitosamente');
            $this->line("   Ruta: {$document->fresh()->pdf_path}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Genera un documento nuevo disparando el evento
     */
    private function generateNew(JobPosting $jobPosting): int
    {
        try {
            event(new JobPostingPublicationRequested($jobPosting));
            $this->info('✅ Documento generado exitosamente');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Elimina workflow y firmas
     */
    private function deleteSignatures(GeneratedDocument $document): void
    {
        $this->warn('🗑️  Eliminando firmas...');

        if ($document->signatureWorkflow) {
            $document->signatureWorkflow->signatures()->delete();
            $document->signatureWorkflow->delete();
        }

        $document->update([
            'signed_pdf_path' => null,
            'signature_status' => 'pending',
            'current_signer_id' => null,
            'signatures_completed' => 0,
            'total_signatures_required' => 0,
        ]);

        $this->info('   Firmas eliminadas');
    }
}
```

#### 8.2. Controller para Admin
**Archivo:** `Modules/Document/app/Http/Controllers/DocumentRegenerationController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Document\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Modules\JobPosting\Entities\JobPosting;

class DocumentRegenerationController
{
    /**
     * Regenera documento de convocatoria desde admin
     */
    public function regenerateConvocatoria(Request $request, string $jobPostingId)
    {
        $this->authorize('regenerate-documents');

        $jobPosting = JobPosting::findOrFail($jobPostingId);
        $force = $request->boolean('force', false);

        try {
            // Ejecutar comando
            $exitCode = Artisan::call('convocatoria:regenerate-document', [
                'job-posting-id' => $jobPostingId,
                '--force' => $force,
            ]);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Documento regenerado exitosamente',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar documento',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

**Ruta:**
```php
// Modules/Document/routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('documents/regenerate-convocatoria/{jobPostingId}',
        [DocumentRegenerationController::class, 'regenerateConvocatoria']
    )->name('documents.regenerate-convocatoria');
});
```

---

## ✅ Checklist de Implementación

- [ ] **Fase 1:** Actualizar Enums
  - [ ] JobPostingStatusEnum - agregar `EN_FIRMA`
  - [ ] DocumentCategoryEnum - agregar `CONVOCATORIA_COMPLETA`

- [ ] **Fase 2:** Crear Eventos (3 archivos)
  - [ ] JobPostingPublicationRequested
  - [ ] JobPostingPublished
  - [ ] DocumentFullySigned

- [ ] **Fase 3:** Crear Listeners (4 archivos)
  - [ ] GenerateConvocatoriaPdf (Document)
  - [ ] AssignJuriesToSign (Jury)
  - [ ] PublishJobPostingAfterSignatures (JobPosting)
  - [ ] ActivateJobProfiles (JobProfile)

- [ ] **Fase 4:** Modificar Servicios
  - [ ] JobPostingService::publish()
  - [ ] SignatureService::advanceWorkflow() - agregar evento
  - [ ] TemplateRendererService - soporte para vistas

- [ ] **Fase 5:** Registrar Eventos en EventServiceProviders
  - [ ] JobPosting EventServiceProvider
  - [ ] Document EventServiceProvider
  - [ ] Jury EventServiceProvider (crear si no existe)
  - [ ] JobProfile EventServiceProvider

- [ ] **Fase 6:** Vista PDF
  - [ ] Crear convocatoria_completa.blade.php

- [ ] **Fase 7:** Template en BD
  - [ ] Crear/ejecutar ConvocatoriaTemplateSeeder

- [ ] **Fase 8:** Regeneración
  - [ ] Comando RegenerateConvocatoriaDocument
  - [ ] Controller DocumentRegenerationController
  - [ ] Ruta API

- [ ] **Testing:** Prueba completa del flujo

---

## 🧪 Testing Manual

```bash
# 1. Crear template en BD
php artisan db:seed --class=Modules\\Document\\Database\\Seeders\\ConvocatoriaTemplateSeeder

# 2. Asignar jurados titulares a una convocatoria (manualmente en BD o interfaz)

# 3. Publicar convocatoria
php artisan tinker
$jobPosting = \Modules\JobPosting\Entities\JobPosting::find(1);
app(\Modules\JobPosting\Services\JobPostingService::class)->publish($jobPosting->id);

# Verificar estado
$jobPosting->fresh()->status; // Debe ser 'EN_FIRMA'

# Verificar documento
$doc = \Modules\Document\Entities\GeneratedDocument::where('documentable_id', 1)
    ->where('documentable_type', \Modules\JobPosting\Entities\JobPosting::class)
    ->latest()->first();
$doc->signatureWorkflow->signatures->count(); // Debe ser > 0

# 4. Simular firmas (manual en interfaz con FirmaPerú)

# 5. Después de todas las firmas, verificar
$jobPosting->fresh()->status; // Debe ser 'PUBLICADA'
$jobPosting->published_at; // Debe tener fecha

# 6. Verificar perfiles activados
$jobPosting->jobProfiles()->where('status', 'active')->count(); // Debe ser > 0

# 7. Probar regeneración
php artisan convocatoria:regenerate-document 1 --force
```

---

## 📚 Notas Técnicas

### **Asignación de Jurados**
- Los jurados se asignan a través de `JuryAssignment`
- Solo se consideran jurados con `member_type = TITULAR` y `is_active = true`
- El orden de firma se respeta según el campo `order` en `jury_assignments`

### **Formato del PDF**
- Orientación: **Portrait** (vertical)
- Tamaño: **A4**
- Estilo: Tabla profesional estilo municipalidad peruana
- Todos los textos en **MAYÚSCULAS**

### **Firma Digital**
- Integración con **FirmaPeru** (servicio existente)
- Workflow **secuencial** (uno firma a la vez)
- No se puede regenerar documento si tiene firmas realizadas

### **Regeneración**
- Disponible via **comando** (`convocatoria:regenerate-document`)
- Disponible via **API/Admin** (endpoint)
- Requiere `--force` si hay firmas existentes

---

**Versión:** 2.0
**Última actualización:** 29/12/2025
