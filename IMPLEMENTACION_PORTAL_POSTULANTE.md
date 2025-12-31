# Implementación Portal del Postulante - Sistema CAS

## 🎯 Objetivo
Implementar el **flujo de inscripción declarativa** para el Portal del Postulante, donde los postulantes **declaran** que cumplen con los requisitos del perfil (sin adjuntar documentos en esta etapa inicial), se genera automáticamente su ficha de postulación en PDF, y se ejecuta un proceso de **filtrado automático** comparando los datos declarados vs requisitos del perfil.

### **Concepto Clave: Etapa de Pre-filtro**
Esta es la **Fase 3 (Registro Virtual de Postulantes)** del proceso de 12 fases:
- El postulante completa un formulario **declarativo** (NO adjunta documentos aún)
- El sistema compara automáticamente los datos declarados vs requisitos del perfil
- Se genera una **ficha de postulación PDF** con comprobante y datos declarados
- Solo los postulantes **APTOS** pasan a la **Fase 5 (Presentación de CV documentado)**
- Los jurados validarán documentos solo de postulantes ya pre-filtrados en **Fase 6**

---

## 📋 Componentes del Sistema

### **Módulos Involucrados**
- `ApplicantPortal` - Portal del postulante (frontend)
- `JobPosting` - Gestión de convocatorias (12 fases del proceso CAS)
- `JobProfile` - Perfiles de puestos con requisitos
- `Application` - Postulaciones, estados y evaluaciones
- `Document` - Generación de PDFs (ficha de postulación)
- `Auth` - Permisos y roles

---

## 🔄 Las 12 Fases del Proceso CAS

```
1. ✅ Aprobación de la Convocatoria (interno)
2. ✅ Publicación de la Convocatoria (portal público)
3. 🎯 Registro Virtual de Postulantes ← ESTA IMPLEMENTACIÓN
4. 📊 Publicación de postulantes APTOS (filtro inicial)
5. 📄 Presentación de CV documentado (subir documentos)
6. 👨‍⚖️ Evaluación Curricular (jurados califican)
7. 📊 Publicación de resultados curriculares
8. 🎤 Entrevista Personal
9. 🏆 Publicación de resultados finales
10. 📝 Suscripción de contrato
11. 🎓 Charla de Inducción
12. 🚀 Inicio de labores
```

---

## 🔄 Flujo Completo de Postulación

### **1. Vista de Convocatorias (index.blade.php)**
**Ruta:** `Modules/ApplicantPortal/resources/views/job-postings/index.blade.php`

**Cambios:**
- ✅ Mostrar solo convocatorias con `status = PUBLICADA`
- ✅ Diseño en **cards modernas** (no tabla)
- ✅ Filtro de búsqueda por título/código
- ✅ Mostrar **fase actual** del cronograma
- ✅ Badge de estado activo
- ✅ Contador de vacantes totales por convocatoria

**Controlador:** `JobPostingController::index()`
```php
$postings = JobPosting::where('status', JobPostingStatusEnum::PUBLICADA)
    ->with(['jobProfiles' => fn($q) => $q->where('status', 'active')])
    ->with('schedules.processPhase') // Cargar fase actual
    ->withCount('jobProfiles')
    ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                              ->orWhere('code', 'like', "%{$search}%"))
    ->paginate(10);
```

---

### **2. Vista de Perfiles de Convocatoria (show.blade.php)**
**Ruta:** `Modules/ApplicantPortal/resources/views/job-postings/show.blade.php`

**Cambios:**
- ✅ Header con información de convocatoria y fase actual
- ✅ **Filtros dinámicos con Alpine.js:**
  - Búsqueda por cargo/código/unidad
  - Nivel educativo (select múltiple)
  - Experiencia requerida (slider años)
  - Rango salarial (slider)
- ✅ Cards de perfiles (solo `status = 'active'`)
- ✅ Indicador visual de requisitos principales
- ✅ Botón "Postular" **solo si fase actual = PHASE_03_REGISTRATION**
- ✅ Badge "Ya postulaste" si aplicó (verificar por perfil)
- ✅ Advertencia si no cumple requisitos (pero puede postular igual)

**Controlador:** `JobPostingController::show($id)`
```php
$posting = JobPosting::with(['schedules.processPhase'])->findOrFail($id);
$currentPhase = $posting->getCurrentPhase();

$jobProfiles = JobProfile::where('job_posting_id', $id)
    ->where('status', 'active')
    ->with(['positionCode', 'requestingUnit', 'vacancies'])
    ->get();

// Verificar postulaciones del usuario por PERFIL
$userApplications = Application::where('applicant_id', auth()->id())
    ->whereHas('vacancy.jobProfile', fn($q) => $q->where('job_posting_id', $id))
    ->with('vacancy.jobProfile')
    ->get();

$appliedProfileIds = $userApplications->pluck('vacancy.jobProfile.id')->toArray();
```

---

### **3. Vista de Formulario de Postulación (apply.blade.php)** ⚠️ CREAR
**Ruta:** `Modules/ApplicantPortal/resources/views/job-postings/apply.blade.php`

**Características:**
- ✅ Wizard multi-paso con Alpine.js
- ✅ **Auto-guardado automático** en localStorage mientras completa
- ✅ Progress bar visual
- ✅ Validación en tiempo real vs requisitos del perfil
- ✅ Advertencias (no bloqueantes) si no cumple requisitos
- ✅ **Dos botones finales:**
  - "Guardar Borrador" → Estado DRAFT (puede editar después)
  - "Guardar y Enviar" → Estado PRESENTADA (ya no puede editar)

**Estructura del Wizard:**

#### **Paso 1: Datos Personales**
- Nombre completo (pre-cargar desde user)
- DNI (pre-cargar, no editable)
- Fecha de nacimiento (input date)
- Dirección completa
- Teléfono fijo (opcional)
- Celular (requerido)
- Email (pre-cargar, no editable)

#### **Paso 2: Formación Académica** (Declarativa)
- Grado académico (select: SECUNDARIA, TECNICO, BACHILLER, TITULO, MAESTRIA, DOCTORADO)
- Institución educativa
- Carrera/Especialidad
- Año de graduación/obtención
- **SIN adjuntar documentos**
- Botón "+" para agregar más títulos
- **Advertencia visual:** Si no cumple nivel mínimo del perfil

#### **Paso 3: Experiencia Laboral** (Declarativa)
Inputs para cada experiencia:
- Empresa/Organización
- Cargo/Puesto
- Fecha inicio (mes/año picker)
- Fecha fin (mes/año picker) - Checkbox "Trabajo actual"
- Checkbox: "¿Es experiencia en el sector público?"
- Checkbox: "¿Es experiencia específica relacionada al puesto?"
- Descripción breve de funciones (textarea)

**Cálculo automático:**
- Sistema calcula duración en años, meses, días (usar `ExperienceDuration`)
- Muestra total acumulado de experiencia general
- Muestra total acumulado de experiencia específica
- **Advertencia visual:** Si no cumple años mínimos requeridos

Botón "+" para agregar más experiencias

#### **Paso 4: Capacitaciones y Cursos** (Declarativa)
- Nombre del curso/capacitación
- Institución que dictó
- Número de horas (input number)
- Mes/Año de certificación
- **SIN adjuntar certificados**

Botón "+" para agregar más cursos

#### **Paso 5: Conocimientos Técnicos** (Declarativa)
- Mostrar conocimientos requeridos del perfil (read-only list)
- Para cada conocimiento: Select nivel (Básico, Intermedio, Avanzado)
- Campo "Otros conocimientos" (opcional, textarea)

#### **Paso 6: Registros Profesionales** (Si aplica)
Mostrar solo si el perfil lo requiere:
- **Colegiatura:** Número de colegiatura, colegio profesional
- **Certificación OSCE:** Número de certificación
- **Licencia de conducir:** Número, categoría, fecha vigencia

#### **Paso 7: Condiciones Especiales** (Bonificaciones)
Checkboxes:
- ☐ Persona con discapacidad (15% bonificación)
- ☐ Licenciado de las FFAA (10% bonificación)
- ☐ Deportista destacado (5% bonificación)
- ☐ Deportista calificado (3% bonificación)

**SIN adjuntar documentos** (se suben en Fase 5)

#### **Paso 8: Revisión y Confirmación**
- Resumen completo de todos los datos ingresados
- **Semáforo de cumplimiento de requisitos:**
  - 🟢 Verde: Cumple todos los requisitos
  - 🟡 Amarillo: Cumple parcialmente (advertencias)
  - 🔴 Rojo: No cumple requisitos mínimos (puede postular igual)
- Checkbox: "Declaro bajo juramento que la información proporcionada es verdadera"
- Checkbox: "Acepto términos y condiciones del proceso"

**Dos botones:**
```html
<button type="submit" name="action" value="draft">
    💾 Guardar Borrador
</button>
<button type="submit" name="action" value="submit">
    ✅ Guardar y Enviar Postulación
</button>
```

**Tecnología:** Alpine.js + localStorage para auto-guardado

---

### **4. Procesamiento de Postulación**
**Controlador:** `JobPostingController::storeApplication()`

**Lógica:**
```php
public function storeApplication(Request $request, $postingId, $profileId)
{
    // 1. Validar fase actual
    $posting = JobPosting::findOrFail($postingId);
    $currentPhase = $posting->getCurrentPhase();

    if ($currentPhase->code !== 'PHASE_03_REGISTRATION') {
        return back()->with('error', 'No está en fase de registro');
    }

    // 2. Validar que no haya postulado a este perfil
    $profile = JobProfile::findOrFail($profileId);
    $existingApp = Application::where('applicant_id', auth()->id())
        ->whereHas('vacancy', fn($q) => $q->where('job_profile_id', $profileId))
        ->first();

    if ($existingApp) {
        return back()->with('error', 'Ya postulaste a este perfil');
    }

    // 3. Obtener vacante disponible
    $vacancy = $profile->vacancies()->available()->first();
    if (!$vacancy) {
        return back()->with('error', 'No hay vacantes disponibles');
    }

    // 4. Determinar estado según acción
    $status = $request->action === 'submit'
        ? ApplicationStatus::SUBMITTED
        : ApplicationStatus::DRAFT; // Nuevo estado

    // 5. Crear ApplicationDTO
    $dto = new ApplicationDTO(
        applicantId: auth()->id(),
        jobProfileVacancyId: $vacancy->id,
        personalData: new PersonalDataDTO(...$request->personal),
        academics: $request->academics ?? [],
        experiences: $request->experiences ?? [],
        trainings: $request->trainings ?? [],
        knowledge: $request->knowledge ?? [],
        professionalRegistrations: $request->registrations ?? [],
        specialConditions: $request->special_conditions ?? [],
        termsAccepted: $request->terms_accepted,
        ipAddress: $request->ip(),
    );

    // 6. Crear postulación
    $application = app(ApplicationService::class)->create($dto);

    // 7. Generar ficha de postulación PDF (solo si se envió, no si es borrador)
    if ($status === ApplicationStatus::SUBMITTED) {
        app(DocumentService::class)->generateFromTemplate(
            template: DocumentTemplate::where('code', 'TPL_APPLICATION_FORM')->first(),
            documentable: $application,
            data: [
                'application' => $application->load(['academics', 'experiences', 'trainings']),
                'applicant' => $application->applicant,
                'profile' => $profile,
                'posting' => $posting,
            ]
        );
    }

    // 8. Mensaje según acción
    $message = $status === ApplicationStatus::SUBMITTED
        ? '¡Postulación enviada exitosamente!'
        : 'Borrador guardado. Puedes completar y enviar después.';

    return redirect()
        ->route('applicant.applications.show', $application->id)
        ->with('success', $message);
}
```

**Nuevo Estado en ApplicationStatus:**
```php
case DRAFT = 'BORRADOR'; // Guardado pero no enviado
```

---

### **5. Generación de Ficha de Postulación (PDF)**

**Template:** Crear `Modules/Document/resources/views/templates/application_form.blade.php`

**Contenido del PDF:**
```
┌─────────────────────────────────────────┐
│   FICHA DE POSTULACIÓN - CAS 2025       │
│   Municipalidad Distrital San Jerónimo  │
└─────────────────────────────────────────┘

CÓDIGO DE POSTULACIÓN: {{ $application->code }}
FECHA: {{ $application->application_date->format('d/m/Y H:i') }}

═══════════════════════════════════════════
I. DATOS DE LA CONVOCATORIA
═══════════════════════════════════════════
Convocatoria: {{ $posting->code }} - {{ $posting->title }}
Perfil: {{ $profile->profile_name }}
Código de Perfil: {{ $profile->code }}
Unidad Solicitante: {{ $profile->requestingUnit->name }}
Remuneración: S/ {{ $profile->positionCode->base_salary }}

═══════════════════════════════════════════
II. DATOS PERSONALES
═══════════════════════════════════════════
Nombres y Apellidos: {{ $application->full_name }}
DNI: {{ $application->dni }}
Fecha Nacimiento: {{ $application->birth_date }}
Email: {{ $application->email }}
Celular: {{ $application->mobile_phone }}
Dirección: {{ $application->address }}

═══════════════════════════════════════════
III. FORMACIÓN ACADÉMICA DECLARADA
═══════════════════════════════════════════
@foreach($application->academics as $academic)
{{ $loop->iteration }}. {{ $academic->degree_type }}
   Institución: {{ $academic->institution }}
   Carrera: {{ $academic->career_field }}
   Año: {{ $academic->year }}
@endforeach

═══════════════════════════════════════════
IV. EXPERIENCIA LABORAL DECLARADA
═══════════════════════════════════════════
Experiencia General: {{ calcular total }} años, {{ calcular }} meses
Experiencia Específica: {{ calcular total }} años, {{ calcular }} meses

@foreach($application->experiences as $exp)
{{ $loop->iteration }}. {{ $exp->organization }} - {{ $exp->position }}
   Periodo: {{ $exp->start_date }} - {{ $exp->end_date }}
   Duración: {{ $exp->formatted_duration }}
   Sector: {{ $exp->is_public_sector ? 'Público' : 'Privado' }}
   Específica: {{ $exp->is_specific ? 'Sí' : 'No' }}
@endforeach

═══════════════════════════════════════════
V. CAPACITACIONES DECLARADAS
═══════════════════════════════════════════
@foreach($application->trainings as $training)
{{ $loop->iteration }}. {{ $training->course_name }}
   Institución: {{ $training->institution }}
   Horas: {{ $training->hours }}
@endforeach

═══════════════════════════════════════════
DECLARACIÓN JURADA
═══════════════════════════════════════════
Declaro bajo juramento que toda la información
proporcionada es verdadera y puede ser verificada
mediante documentos en la siguiente fase del proceso.

IMPORTANTE: Esta ficha es solo un comprobante de
inscripción. Los documentos sustentatorios serán
solicitados en la Fase 5 (Presentación de CV documentado)
únicamente a los postulantes declarados APTOS.

───────────────────────────────────────────
Hash de verificación: {{ md5($application->id) }}
Código QR: [Generar QR con código de postulación]
```

**Seed para DocumentTemplate:**
```php
DocumentTemplate::create([
    'code' => 'TPL_APPLICATION_FORM',
    'name' => 'Ficha de Postulación CAS',
    'category' => 'APPLICATION',
    'paper_size' => 'A4',
    'orientation' => 'portrait',
    'content' => '...' // Contenido del blade
]);
```

---

### **6. Vista de Postulación (applications/show.blade.php)**
**Ruta:** `Modules/ApplicantPortal/resources/views/applications/show.blade.php`

**Mostrar según estado:**

#### **Si estado = DRAFT (Borrador)**
- Badge amarillo "BORRADOR"
- Mensaje: "Tu postulación está guardada pero NO enviada"
- Botón: "Editar y Completar"
- Botón: "Enviar Ahora" (convierte a PRESENTADA)
- NO muestra ficha PDF (solo se genera al enviar)

#### **Si estado = PRESENTADA**
- Badge azul "PRESENTADA"
- Mensaje: "Postulación enviada correctamente. Espera la publicación de resultados."
- Código de postulación destacado
- Botón: "Descargar Ficha de Postulación" (PDF)
- Cronología de fases

#### **Si estado = APTO / NO_APTO**
Verificar si resultados fueron publicados:
```php
@if($posting->results_published)
    @if($application->status === 'APTO')
        <div class="bg-green-100 border-green-500">
            <h3>✅ DECLARADO APTO</h3>
            <p>Cumples los requisitos del perfil.</p>
            <p>Próximo paso: Subir documentos sustentatorios en Fase 5</p>
        </div>
    @else
        <div class="bg-red-100 border-red-500">
            <h3>❌ DECLARADO NO APTO</h3>
            <p>Razón: {{ $application->ineligibility_reason }}</p>
            <p>Puedes presentar reclamo si consideras que hay un error.</p>
            <button>Presentar Reclamo</button>
        </div>
    @endif
@else
    <div class="bg-yellow-100">
        <p>⏳ Resultados en proceso. Serán publicados próximamente.</p>
    </div>
@endif
```

**Estados de Application:**
- `BORRADOR` - Guardado pero no enviado (puede editar)
- `PRESENTADA` - Enviada, esperando evaluación automática
- `EN_REVISION` - En revisión manual (casos especiales)
- `APTO` - Cumple requisitos (AutoGrader)
- `NO_APTO` - No cumple requisitos
- `SUBSANACION` - Requiere correcciones (Fase 5)
- `EN_EVALUACION` - En evaluación curricular (Fase 6)
- `APROBADA` - Ganador final (Fase 9)
- `RECHAZADA` - No pasó evaluación
- `DESISTIDA` - Postulante desistió

---

## 🔐 Sistema de Publicación de Resultados

### **Flujo:**
1. Admin ejecuta evaluación automática de todas las postulaciones
2. Sistema marca APTO/NO_APTO según AutoGrader
3. Admin revisa resultados en dashboard
4. Admin hace clic en "Publicar Resultados de Fase 4"
5. Sistema actualiza `job_postings.results_published = true`
6. Postulantes pueden ver sus resultados

### **Nuevo Permiso:**
```php
// Agregar a PermissionsTableSeeder.php
['name' => 'Publicar Resultados de Elegibilidad',
 'slug' => 'application.publish.results',
 'module' => 'application',
 'description' => 'Publicar resultados de la Fase 4 (APTOS)']
```

### **Migración:**
```php
// Crear archivo: xxx_add_results_published_to_job_postings.php
Schema::table('job_postings', function (Blueprint $table) {
    $table->boolean('results_published')->default(false)->after('status');
    $table->timestamp('results_published_at')->nullable()->after('results_published');
    $table->foreignUuid('results_published_by')->nullable()->after('results_published_at')
          ->constrained('users');
});

// Actualizar ApplicationStatus enum
enum ApplicationStatus: string
{
    case DRAFT = 'BORRADOR'; // NUEVO
    case SUBMITTED = 'PRESENTADA';
    case IN_REVIEW = 'EN_REVISION';
    case ELIGIBLE = 'APTO';
    case NOT_ELIGIBLE = 'NO_APTO';
    case AMENDMENT_REQUIRED = 'SUBSANACION';
    case IN_EVALUATION = 'EN_EVALUACION';
    case APPROVED = 'APROBADA';
    case REJECTED = 'RECHAZADA';
    case WITHDRAWN = 'DESISTIDA';
}
```

---

## 🤖 Evaluación Automática

### **Cuándo Ejecutar:**
- Después que cierra la Fase 3 (Registro)
- Admin hace clic en "Evaluar Elegibilidad Automática"
- Se ejecuta para todas las postulaciones con estado PRESENTADA

### **Comando Artisan:**
```php
// Modules/Application/Console/EvaluateApplicationsCommand.php
php artisan applications:evaluate {posting_id}
```

### **Proceso:**
```php
$applications = Application::where('status', ApplicationStatus::SUBMITTED)
    ->whereHas('vacancy.jobProfile.jobPosting', fn($q) => $q->where('id', $postingId))
    ->get();

foreach ($applications as $application) {
    $result = app(AutoGraderService::class)->evaluateEligibility($application);

    $application->update([
        'is_eligible' => $result['is_eligible'],
        'status' => $result['is_eligible']
            ? ApplicationStatus::ELIGIBLE
            : ApplicationStatus::NOT_ELIGIBLE,
        'ineligibility_reason' => implode("\n", $result['reasons'] ?? []),
        'eligibility_checked_at' => now(),
        'eligibility_checked_by' => auth()->id(),
    ]);
}

// IMPORTANTE: NO publicar resultados aún
// Solo marcar APTO/NO_APTO internamente
```

---

## 🎨 Diseño UI/UX

### **Paleta de Colores:**
- **Primary:** `#1E40AF` (azul municipal)
- **Success:** `#10B981` - APTO
- **Danger:** `#EF4444` - NO_APTO
- **Warning:** `#F59E0B` - BORRADOR / PENDIENTE
- **Info:** `#3B82F6`

### **Componentes:**
- Cards con hover effects y sombras
- Progress bar circular para wizard (ej: 3/8)
- Badges con colores según estado
- Tooltips con requisitos del perfil
- Modals para confirmaciones importantes
- Skeleton loaders mientras carga

### **Inputs para Tiempo (años/meses/días):**
```html
<!-- Componente reutilizable: experience-duration-input.blade.php -->
<div class="flex gap-2">
    <div class="flex-1">
        <label>Años</label>
        <input type="number" min="0" max="50" name="years">
    </div>
    <div class="flex-1">
        <label>Meses</label>
        <input type="number" min="0" max="11" name="months">
    </div>
</div>
<p class="text-sm text-gray-500">
    Sistema calculará automáticamente desde fecha inicio/fin
</p>
```

**Usar `ExperienceDuration` en backend:**
```php
$duration = ExperienceDuration::fromParts($years, $months);
$experience->duration = $duration->toDecimal(); // Guarda como decimal en BD
```

---

## 📂 Estructura de Archivos

### **Nuevos Archivos a Crear:**
```
Modules/ApplicantPortal/resources/views/job-postings/
└── apply.blade.php                    ✨ CREAR (wizard completo)

Modules/Document/resources/views/templates/
└── application_form.blade.php         ✨ CREAR (PDF ficha)

Modules/Application/DTOs/
├── ApplicationDTO.php                 ✨ CREAR
└── PersonalDataDTO.php                ✨ CREAR

Modules/Application/Console/
└── EvaluateApplicationsCommand.php    ✨ CREAR

database/migrations/
└── xxx_add_results_published_to_job_postings.php    ✨ CREAR
└── xxx_add_draft_status_to_applications.php         ✨ CREAR (si no existe)
```

### **Archivos a Modificar:**
```
Modules/ApplicantPortal/resources/views/job-postings/
├── index.blade.php                    ✏️ Modificar (cards)
└── show.blade.php                     ✏️ Modificar (filtros)

Modules/ApplicantPortal/resources/views/applications/
└── show.blade.php                     ✏️ Modificar (estados + PDF)

Modules/ApplicantPortal/app/Http/Controllers/
└── JobPostingController.php           🔧 Actualizar métodos

Modules/Application/app/Enums/
└── ApplicationStatus.php              🔧 Agregar DRAFT

Modules/Auth/database/seeders/
└── PermissionsTableSeeder.php         🔧 Agregar permiso
```

---

## ✅ Checklist de Implementación

### **Fase 1: Preparación Backend (2-3 horas)**
- [ ] Crear migración `results_published` en job_postings
- [ ] Agregar estado `DRAFT` a ApplicationStatus enum
- [ ] Crear ApplicationDTO y PersonalDataDTO
- [ ] Agregar permiso `application.publish.results`
- [ ] Ejecutar migraciones

### **Fase 2: Vistas Básicas (3-4 horas)**
- [ ] Modificar `index.blade.php` - Cards de convocatorias
- [ ] Actualizar `JobPostingController::index()`
- [ ] Modificar `show.blade.php` - Filtros dinámicos Alpine.js
- [ ] Actualizar `JobPostingController::show()`
- [ ] Agregar verificación de fase REGISTRATION

### **Fase 3: Formulario Wizard (5-6 horas)**
- [ ] Crear `apply.blade.php` - Estructura base wizard
- [ ] Paso 1: Datos personales (pre-cargar)
- [ ] Paso 2: Formación académica (sin adjuntos)
- [ ] Paso 3: Experiencia laboral (con cálculo automático)
- [ ] Paso 4: Capacitaciones
- [ ] Paso 5: Conocimientos
- [ ] Paso 6: Registros profesionales
- [ ] Paso 7: Condiciones especiales
- [ ] Paso 8: Revisión y confirmación
- [ ] Implementar auto-guardado en localStorage
- [ ] Validaciones frontend con Alpine.js

### **Fase 4: Backend de Postulación (3-4 horas)**
- [ ] Actualizar `JobPostingController::apply()`
- [ ] Implementar `JobPostingController::storeApplication()`
- [ ] Manejar estados DRAFT vs PRESENTADA
- [ ] Validaciones backend
- [ ] Integrar con ApplicationService::create()
- [ ] Cálculo de experiencia con ExperienceDuration

### **Fase 5: Generación de PDFs (2-3 horas)**
- [ ] Crear template `application_form.blade.php`
- [ ] Agregar seed para DocumentTemplate
- [ ] Integrar generación al enviar postulación
- [ ] Implementar descarga de ficha PDF
- [ ] Probar con datos reales

### **Fase 6: Vista de Postulación (2-3 horas)**
- [ ] Modificar `applications/show.blade.php`
- [ ] Lógica para estado BORRADOR (editar/enviar)
- [ ] Lógica para estado PRESENTADA (descarga PDF)
- [ ] Lógica para APTO/NO_APTO con flag published
- [ ] Timeline de fases del proceso
- [ ] Botón de reclamo (placeholder)

### **Fase 7: Evaluación Automática (2-3 horas)**
- [ ] Crear comando `EvaluateApplicationsCommand`
- [ ] Vista admin para ejecutar evaluación
- [ ] Dashboard de resultados pre-publicación
- [ ] Botón "Publicar Resultados de Fase 4"
- [ ] Evento y notificación al publicar

### **Fase 8: Testing y Refinamiento (3-4 horas)**
- [ ] Probar flujo completo: ver convocatoria → postular → ver resultado
- [ ] Probar guardado de borrador y edición
- [ ] Probar filtros en show.blade.php
- [ ] Verificar cálculo automático de experiencia
- [ ] Validar permisos de publicación
- [ ] Responsive design (mobile)
- [ ] Optimizar queries (N+1)

---

## 🚀 Orden de Desarrollo Recomendado

```
Día 1 (4-5 horas):
1. Migraciones y enums ← PRIMERO
2. DTOs y estructuras de datos
3. Modificar index.blade.php (cards)
4. Modificar show.blade.php (filtros)

Día 2 (5-6 horas):
5. Crear wizard apply.blade.php (pasos 1-4)
6. Auto-guardado en localStorage
7. Validaciones frontend

Día 3 (5-6 horas):
8. Completar wizard (pasos 5-8)
9. Backend storeApplication()
10. Integración con ApplicationService

Día 4 (4-5 horas):
11. Template PDF + generación
12. Vista applications/show.blade.php
13. Evaluación automática + comando

Día 5 (3-4 horas):
14. Vista admin publicar resultados
15. Testing completo
16. Ajustes finales + responsive
```

**Total estimado: 20-25 horas**

---

## 📌 Notas Importantes

### **Seguridad:**
- Validar que usuario solo pueda postular **una vez por perfil**
- Validar que la fase actual sea REGISTRATION
- Sanitizar inputs (XSS protection)
- Registrar IP y timestamp de postulación

### **Performance:**
- Usar eager loading: `->with(['jobProfiles', 'schedules.processPhase'])`
- Cachear lista de fases del proceso
- Optimizar queries de filtros (índices en BD)

### **UX:**
- Auto-guardado cada 30 segundos en localStorage
- Indicador visual de guardado ("Guardando...", "Guardado ✓")
- Confirmación antes de salir si hay cambios sin guardar
- Progress bar clara en wizard (3/8)

### **Validaciones:**
- Frontend: Advertencias (no bloqueantes)
- Backend: Validaciones estrictas
- AutoGrader: Comparación precisa con requisitos del perfil

### **Notificaciones (Fase futura):**
- Email al enviar postulación exitosamente
- Email al publicar resultados de Fase 4
- Email cuando pase a Fase 5 (subir documentos)

---

## 🎯 Resultado Final

Al completar esta implementación, el sistema tendrá:

✅ Portal moderno y profesional para postulantes
✅ Filtros dinámicos para encontrar perfiles fácilmente
✅ Formulario wizard intuitivo y declarativo (sin adjuntar documentos)
✅ Sistema de borradores editables
✅ Auto-guardado automático para mejor UX
✅ Generación automática de ficha de postulación PDF
✅ Sistema de evaluación automática con AutoGrader
✅ Control de publicación de resultados con permisos
✅ Visibilidad controlada según estado del proceso
✅ Advertencias visuales de cumplimiento de requisitos
✅ Experiencia fluida y sin fricción para el postulante

---

**Documento generado:** 2025-01-01
**Sistema:** CAS - Municipalidad de San Jerónimo
**Módulo:** ApplicantPortal (Portal del Postulante)
**Fase:** Fase 3 - Registro Virtual de Postulantes
