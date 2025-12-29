# 🚀 Próximos Pasos - Módulo ApplicantPortal

## 📋 Guía Rápida de Continuación

Este documento te guía sobre cómo continuar el desarrollo del módulo ApplicantPortal para llegar al 100% de funcionalidad.

---

## 🎯 Fase 1: Completar Vistas Principales (2-3 días)

### 1. Vista de Formulario de Postulación
**Archivo:** `resources/views/job-postings/apply.blade.php`

**Elementos necesarios:**
- Formulario con campos de postulación
- Selección de vacante específica
- Carga de documentos requeridos (drag & drop)
- Checkbox de condiciones especiales
- Checkbox de aceptación de términos
- Preview de documentos cargados
- Validación en frontend (JavaScript)

**Integración:**
- Ruta: `POST /portal/convocatorias/{postingId}/postular/{profileId}`
- Controlador: `JobPostingController@storeApplication`

---

### 2. Vista de Detalle de Postulación
**Archivo:** `resources/views/applications/show.blade.php`

**Elementos necesarios:**
- Timeline del proceso de postulación
- Información del puesto postulado
- Documentos presentados (con descarga)
- Puntajes obtenidos (si aplica)
- Comentarios de evaluadores
- Fechas importantes
- Botones de acción (Desistir, Descargar documentos)

**Secciones:**
```blade
- Header con código y estado
- Información del perfil
- Documentos presentados
- Timeline de evaluación
- Resultados (si están disponibles)
- Acciones disponibles
```

---

### 3. Vista de Perfil del Usuario
**Archivo:** `resources/views/profile/show.blade.php`

**Elementos necesarios:**
- Datos personales con foto
- Resumen de experiencia laboral
- Resumen de formación académica
- Cursos y certificaciones
- Documentos personales
- Botones para editar cada sección

**Estructura sugerida:**
```blade
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Columna izquierda: Foto y datos básicos -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl p-6">
            <img src="{{ $user->photo }}" class="w-32 h-32 rounded-full mx-auto mb-4">
            <h2>{{ $user->full_name }}</h2>
            <!-- Datos de contacto -->
        </div>
    </div>

    <!-- Columna derecha: Secciones del CV -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Experiencia laboral -->
        <!-- Formación académica -->
        <!-- Cursos -->
        <!-- Documentos -->
    </div>
</div>
```

---

## 🎯 Fase 2: Implementar Validaciones (1 día)

### FormRequests a Crear

#### 1. StoreApplicationRequest
**Archivo:** `app/Http/Requests/StoreApplicationRequest.php`

```php
<?php

namespace Modules\ApplicantPortal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('applicant');
    }

    public function rules(): array
    {
        return [
            'vacancy_id' => ['required', 'uuid', 'exists:job_profile_vacancies,id'],
            'terms_accepted' => ['required', 'accepted'],
            'special_conditions' => ['nullable', 'array'],
            'special_conditions.*' => ['string', 'in:DISABILITY,MILITARY,ATHLETE_NATIONAL,ATHLETE_INTL,TERRORISM'],
            'documents' => ['required', 'array', 'min:3'],
            'documents.*.type' => ['required', 'string'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'Debes aceptar los términos y condiciones para postular.',
            'documents.min' => 'Debes subir al menos 3 documentos requeridos.',
            'documents.*.file.max' => 'Cada documento no puede pesar más de 10MB.',
        ];
    }
}
```

**Uso en controlador:**
```php
public function storeApplication(StoreApplicationRequest $request, string $postingId, string $profileId)
{
    $validated = $request->validated();
    // ... lógica de creación
}
```

#### 2. UpdateProfileRequest
**Archivo:** `app/Http/Requests/UpdateProfileRequest.php`

```php
public function rules(): array
{
    return [
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
        'address' => ['nullable', 'string', 'max:500'],
        'birth_date' => ['nullable', 'date', 'before:today'],
        'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
    ];
}
```

#### 3. UpdatePasswordRequest
**Archivo:** `app/Http/Requests/UpdatePasswordRequest.php`

```php
public function rules(): array
{
    return [
        'current_password' => ['required', 'string', 'current_password'],
        'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
    ];
}
```

---

## 🎯 Fase 3: Crear Componentes Reutilizables (1 día)

### Blade Components

#### 1. Status Badge Component
**Archivo:** `resources/views/components/status-badge.blade.php`

```blade
@props(['status'])

@php
    $colors = [
        'PRESENTADA' => 'bg-blue-100 text-blue-800',
        'EN_REVISION' => 'bg-yellow-100 text-yellow-800',
        'APTO' => 'bg-green-100 text-green-800',
        'NO_APTO' => 'bg-red-100 text-red-800',
        'EN_EVALUACION' => 'bg-purple-100 text-purple-800',
        'APROBADA' => 'bg-emerald-100 text-emerald-800',
        'RECHAZADA' => 'bg-red-100 text-red-800',
        'DESISTIDA' => 'bg-gray-100 text-gray-800',
    ];

    $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border $color"]) }}>
    {{ str_replace('_', ' ', $status) }}
</span>
```

**Uso:**
```blade
<x-applicantportal::status-badge :status="$application->status" />
```

#### 2. Application Card Component
**Archivo:** `resources/views/components/application-card.blade.php`

```blade
@props(['application'])

<div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 hover:shadow-lg transition-all p-6">
    <!-- Contenido de la tarjeta -->
    <h3>{{ $application->jobProfile->position_code->name }}</h3>
    <x-applicantportal::status-badge :status="$application->status" />
    <!-- ... más contenido -->
</div>
```

#### 3. Document Upload Component
**Archivo:** `resources/views/components/document-upload.blade.php`

```blade
@props(['name', 'label', 'required' => false])

<div class="border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-municipal-blue transition-all">
    <input type="file" name="{{ $name }}" id="{{ $name }}" class="hidden" {{ $required ? 'required' : '' }}>
    <label for="{{ $name }}" class="cursor-pointer text-center block">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2"><!-- Upload icon --></svg>
        <p class="font-semibold text-gray-700">{{ $label }}</p>
        <p class="text-sm text-gray-500 mt-1">Click para seleccionar archivo</p>
    </label>
</div>
```

---

## 🎯 Fase 4: Testing (2 días)

### Unit Tests

#### Test de DashboardController
**Archivo:** `tests/Unit/DashboardControllerTest.php`

```php
public function test_dashboard_shows_user_statistics()
{
    $user = User::factory()->create();
    $applications = Application::factory()->count(5)->create(['applicant_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('applicant.dashboard'))
        ->assertStatus(200)
        ->assertViewHas('stats')
        ->assertViewHas('recentApplications');
}
```

### Feature Tests

```php
public function test_user_can_apply_to_job_posting()
{
    $user = User::factory()->create();
    $posting = JobPosting::factory()->create();
    $profile = JobProfile::factory()->create(['job_posting_id' => $posting->id]);

    $this->actingAs($user)
        ->post(route('applicant.job-postings.apply.store', [$posting->id, $profile->id]), [
            'vacancy_id' => $vacancy->id,
            'terms_accepted' => true,
            'documents' => [/* archivos */],
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('applications', [
        'applicant_id' => $user->id,
        'job_profile_id' => $profile->id,
    ]);
}
```

---

## 🎯 Fase 5: Optimización (1 día)

### Eager Loading
Optimizar queries en controladores:

```php
// Antes
$applications = $this->applicationService->getUserApplications($user->id);

// Después (en el servicio)
public function getUserApplications($userId)
{
    return Application::where('applicant_id', $userId)
        ->with([
            'jobProfile.position_code',
            'jobProfile.requesting_unit',
            'jobPosting',
        ])
        ->latest()
        ->get();
}
```

### Caching
Implementar caché para datos que no cambian frecuentemente:

```php
public function getActivePostings()
{
    return Cache::remember('active_postings', 3600, function() {
        return JobPosting::where('status', 'PUBLICADA')
            ->with(['jobProfiles', 'currentPhase'])
            ->get();
    });
}
```

---

## 📋 Checklist de Completitud

### Vistas
- [ ] `job-postings/apply.blade.php`
- [ ] `applications/show.blade.php`
- [ ] `profile/show.blade.php`
- [ ] `profile/edit.blade.php`
- [ ] `profile/edit-password.blade.php`
- [ ] `profile/education.blade.php`
- [ ] `profile/work-experience.blade.php`
- [ ] `profile/courses.blade.php`
- [ ] `profile/documents.blade.php`

### Validaciones
- [ ] `StoreApplicationRequest.php`
- [ ] `UpdateProfileRequest.php`
- [ ] `UpdatePasswordRequest.php`
- [ ] `UploadDocumentRequest.php`

### Componentes
- [ ] `status-badge.blade.php`
- [ ] `application-card.blade.php`
- [ ] `job-posting-card.blade.php`
- [ ] `document-upload.blade.php`

### Testing
- [ ] Unit tests para controladores
- [ ] Feature tests para flujos principales
- [ ] Coverage > 80%

### Optimización
- [ ] Eager loading implementado
- [ ] Caché estratégico
- [ ] Índices de BD optimizados

---

## 🚀 Comandos Útiles

### Generar componentes Blade
```bash
php artisan make:component StatusBadge --view
```

### Crear FormRequests
```bash
php artisan make:request Modules/ApplicantPortal/Http/Requests/StoreApplicationRequest
```

### Ejecutar tests
```bash
# Todos los tests
php artisan test

# Solo el módulo ApplicantPortal
php artisan test --filter ApplicantPortal

# Con coverage
php artisan test --coverage
```

### Limpiar caché
```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## 💡 Tips y Mejores Prácticas

1. **Consistencia en diseño:** Usa las mismas clases Tailwind y componentes en todas las vistas
2. **Reutilización:** Crea componentes para elementos repetitivos
3. **Validación dual:** Valida tanto en frontend (JS) como backend (FormRequest)
4. **Mensajes claros:** Usa mensajes de error/éxito descriptivos en español
5. **Responsive first:** Prueba siempre en móvil primero
6. **Accesibilidad:** Usa atributos ARIA y labels correctos
7. **SEO:** Añade meta tags apropiados en las vistas
8. **Performance:** Implementa lazy loading para imágenes grandes

---

## 📚 Recursos

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Blade Components](https://laravel.com/docs/blade#components)
- [Form Validation](https://laravel.com/docs/validation)
- [Testing](https://laravel.com/docs/testing)

---

**¡Éxito con el desarrollo!** 🚀
