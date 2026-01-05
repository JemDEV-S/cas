# Automatización de Calificación Automática - Fase 3 (Registro Virtual) v2

## 🎯 Objetivo

Mejorar el sistema de calificación automática del Portal del Postulante para que la evaluación de elegibilidad sea **100% precisa y automática**, eliminando ambigüedades en la comparación de datos declarados vs requisitos del perfil.

### ⚠️ Restricciones del Proyecto

**IMPORTANTE**: Los perfiles (JobProfile) ya están **APROBADOS** y en producción. **NO podemos modificar su estructura ni agregar campos**. La solución debe trabajar con los datos existentes.

### Problema Actual

1. **❌ Carreras profesionales en texto libre**: 50+ perfiles con alta variedad
2. **❌ Capacitaciones en texto libre**: `required_courses` sin normalización
3. **❌ Validaciones que referencian campos inexistentes**: requires_professional_registry, requires_osce_certification, requires_driver_license
4. **⚠️ Validación de `education_levels` incompleta**: AutoGrader solo compara contra `education_level` singular
5. **⚠️ Sin equivalencias de carreras**

---

## 🏗️ Arquitectura de Solución

### Enfoque: **Normalización Unilateral con Mapeo Inverso**

Como **NO podemos modificar los perfiles aprobados**, la estrategia es:

1. **Crear catálogos normalizados** solo para las postulaciones
2. **Mapear automáticamente** los datos de perfiles legacy al catálogo
3. **Validar en AutoGrader** usando el mapeo

```
PERFILES (SIN TOCAR) → COMANDO MAPEO → CATÁLOGO NORMALIZADO
                                             ↓
                                  POSTULANTE SELECCIONA
                                             ↓
                                       AUTOGRADER
                              (Compara usando mapeo)
```

---

## 📊 Cambios en Base de Datos

### 1. Nueva Tabla: `academic_careers` (Catálogo SUNEDU)

**Fuente**: Dataset de programas académicos de universidades peruanas (SUNEDU)

```sql
CREATE TABLE academic_careers (
    id CHAR(36) PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,           -- 'CAR_DERECHO', 'CAR_ING_SISTEMAS'
    name VARCHAR(200) NOT NULL,                 -- 'Derecho'
    category VARCHAR(100),                      -- 'Ciencias Jurídicas', 'Ingeniería'
    subcategory VARCHAR(100),                   -- Subcategoría SUNEDU
    requires_colegiatura BOOLEAN DEFAULT false, -- Profesiones colegiadas
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE INDEX idx_careers_code ON academic_careers(code);
CREATE INDEX idx_careers_category ON academic_careers(category);
CREATE INDEX idx_careers_name ON academic_careers(name);
```

**Datos**: Extraídos del dataset SUNEDU (2000+ carreras) mediante comando artisan.

---

### 2. Nueva Tabla: `job_profile_career_mappings` (Mapeo Automático)

**Propósito**: Mapear `career_field` de perfiles legacy a `academic_careers`

```sql
CREATE TABLE job_profile_career_mappings (
    id CHAR(36) PRIMARY KEY,
    job_profile_id CHAR(36) NOT NULL,
    career_id CHAR(36) NOT NULL,                 -- Relación con academic_careers
    original_career_text VARCHAR(255),            -- Valor original del career_field
    confidence_score DECIMAL(5,2),                -- 0.00 - 100.00 (algoritmo de similitud)
    mapping_method VARCHAR(50),                   -- 'EXACT_MATCH', 'FUZZY_MATCH', 'MANUAL'
    is_approved BOOLEAN DEFAULT false,            -- Requiere aprobación si confidence < 90%
    approved_by CHAR(36),                         -- Usuario que aprobó
    approved_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (job_profile_id) REFERENCES job_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES academic_careers(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id),

    UNIQUE(job_profile_id) -- Un perfil → una carrera principal
);

CREATE INDEX idx_mapping_profile ON job_profile_career_mappings(job_profile_id);
CREATE INDEX idx_mapping_career ON job_profile_career_mappings(career_id);
CREATE INDEX idx_mapping_approved ON job_profile_career_mappings(is_approved);
```

---

### 3. Nueva Tabla: `academic_career_equivalences` (Equivalencias)

**Propósito**: Carreras afines/equivalentes para validación flexible

```sql
CREATE TABLE academic_career_equivalences (
    id CHAR(36) PRIMARY KEY,
    career_id CHAR(36) NOT NULL,                 -- Carrera A
    equivalent_career_id CHAR(36) NOT NULL,      -- Carrera B (equivalente)
    equivalence_type VARCHAR(50),                -- 'SAME_CATEGORY', 'MANUAL', 'SUNEDU'
    category_group VARCHAR(100),                 -- 'Ingeniería', 'Ciencias Jurídicas'
    approved_by CHAR(36),
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (career_id) REFERENCES academic_careers(id) ON DELETE CASCADE,
    FOREIGN KEY (equivalent_career_id) REFERENCES academic_careers(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id),

    UNIQUE(career_id, equivalent_career_id)
);
```

**Auto-población**: Carreras con misma `category` se marcan como equivalentes automáticamente.

---

### 4. Modificación: Tabla `application_academics` (Normalizar)

```sql
ALTER TABLE application_academics ADD COLUMN career_id CHAR(36) AFTER career_field;
ALTER TABLE application_academics ADD CONSTRAINT fk_application_academics_career
    FOREIGN KEY (career_id) REFERENCES academic_careers(id) ON DELETE SET NULL;

CREATE INDEX idx_application_academics_career ON application_academics(career_id);

-- Mantener career_field para compatibilidad (puede ser NULL si selecciona del catálogo)
```

---

### 5. NO TOCAR: Tabla `job_profiles` (Campos Intactos)

**Campos existentes que se usarán**:
- `career_field` (texto libre - se mapea vía tabla job_profile_career_mappings)
- `education_levels` (array - soporte para múltiples niveles)
- `education_level` (string - legacy, mantener compatibilidad)
- `required_courses` (array de strings - se usa tal cual en checkboxes)
- `colegiatura_required` (boolean - única validación de registro profesional)

**NO se agregan**: requires_professional_registry, requires_osce_certification, requires_driver_license

---

## 🤖 Cambios en AutoGraderService

### Modificación 1: `validateAcademics()` - REESCRIBIR COMPLETO

```php
<?php

protected function validateAcademics(Application $application): array
{
    $jobProfile = $application->vacancy->jobProfile;
    $academics = $application->academics;

    $result = [
        'is_valid' => false,
        'reasons' => [],
        'details' => []
    ];

    // 1. Validar que tenga al menos un registro académico
    if ($academics->isEmpty()) {
        $result['reasons'][] = 'No ha registrado formación académica';
        return $result;
    }

    // 2. Validar nivel educativo con soporte para education_levels (array)
    $requiredLevels = !empty($jobProfile->education_levels)
        ? $jobProfile->education_levels
        : [$jobProfile->education_level]; // Fallback a campo legacy

    $applicantLevels = $academics->pluck('degree_type')->unique();

    $hasRequiredLevel = false;
    foreach ($requiredLevels as $requiredLevel) {
        foreach ($applicantLevels as $applicantLevel) {
            if ($this->meetsEducationLevel($applicantLevel, $requiredLevel)) {
                $hasRequiredLevel = true;
                break 2; // Lógica OR: con que cumpla UNO es suficiente
            }
        }
    }

    if (!$hasRequiredLevel) {
        $result['reasons'][] = sprintf(
            'Nivel educativo insuficiente. Requiere: %s. Tiene: %s',
            implode(' o ', $requiredLevels),
            $applicantLevels->implode(', ')
        );
        return $result;
    }

    // 3. Validar carrera específica (usando tabla de mapeo)
    if (!empty($jobProfile->career_field)) {
        // Obtener mapeo del perfil
        $mapping = DB::table('job_profile_career_mappings')
            ->where('job_profile_id', $jobProfile->id)
            ->where('is_approved', true)
            ->first();

        if (!$mapping) {
            // Si no hay mapeo aprobado, advertir pero no rechazar
            $result['warnings'][] = 'El perfil no tiene carrera mapeada. Requiere revisión manual.';
        } else {
            // Obtener carreras equivalentes
            $acceptedCareerIds = $this->getEquivalentCareerIds($mapping->career_id);

            // Verificar si el postulante tiene alguna carrera aceptada
            $applicantCareerIds = $academics->pluck('career_id')->filter()->unique();

            $hasRequiredCareer = $applicantCareerIds->intersect($acceptedCareerIds)->isNotEmpty();

            if (!$hasRequiredCareer) {
                $requiredCareerName = DB::table('academic_careers')
                    ->where('id', $mapping->career_id)
                    ->value('name');

                $applicantCareerNames = DB::table('academic_careers')
                    ->whereIn('id', $applicantCareerIds)
                    ->pluck('name');

                $result['reasons'][] = sprintf(
                    'Carrera profesional no cumple requisito. Requiere: %s. Tiene: %s',
                    $requiredCareerName ?? $jobProfile->career_field,
                    $applicantCareerNames->isNotEmpty() ? $applicantCareerNames->implode(', ') : 'No especificada'
                );
                return $result;
            }
        }
    }

    // 4. Validar colegiatura si es requerida
    if ($jobProfile->colegiatura_required) {
        $hasColegiatura = $application->professionalRegistrations()
            ->where('registration_type', 'COLEGIATURA')
            ->whereRaw('(expiry_date IS NULL OR expiry_date >= CURDATE())')
            ->exists();

        if (!$hasColegiatura) {
            $result['reasons'][] = 'Requiere colegiatura profesional vigente';
            return $result;
        }
    }

    // ✅ PASA todas las validaciones
    $result['is_valid'] = true;
    $result['details'] = [
        'education_level_met' => true,
        'career_met' => true,
        'colegiatura_met' => $jobProfile->colegiatura_required
    ];

    return $result;
}

/**
 * Obtiene IDs de carreras equivalentes (incluyendo la misma)
 */
protected function getEquivalentCareerIds(string $careerId): array
{
    $ids = [$careerId]; // La carrera misma siempre es aceptada

    // Buscar equivalencias bidireccionales
    $equivalences = DB::table('academic_career_equivalences')
        ->where(function($query) use ($careerId) {
            $query->where('career_id', $careerId)
                  ->orWhere('equivalent_career_id', $careerId);
        })
        ->get();

    foreach ($equivalences as $equiv) {
        $ids[] = $equiv->career_id;
        $ids[] = $equiv->equivalent_career_id;
    }

    return array_unique($ids);
}

/**
 * Verifica si un nivel educativo cumple con el requerido (jerarquía)
 */
protected function meetsEducationLevel(string $applicantLevel, string $requiredLevel): bool
{
    $hierarchy = [
        'SECUNDARIA' => 1,
        'TECNICO' => 2,
        'BACHILLER' => 3,
        'TITULO' => 4,
        'MAESTRIA' => 5,
        'DOCTORADO' => 6
    ];

    $applicantValue = $hierarchy[strtoupper($applicantLevel)] ?? 0;
    $requiredValue = $hierarchy[strtoupper($requiredLevel)] ?? 0;

    return $applicantValue >= $requiredValue;
}
```

---

### Modificación 2: ELIMINAR Validaciones de Campos Inexistentes

```php
/**
 * COMENTAR O ELIMINAR estos métodos del AutoGraderService:
 * - validateProfessionalRegistry() [usar solo colegiatura_required]
 * - validateOsceCertification() [campo no existe]
 * - validateDriverLicense() [campo no existe]
 */

// En evaluateEligibility(), REMOVER estas validaciones:
/*
if ($jobProfile->requires_osce_certification) {
    // ESTE CÓDIGO YA NO SE EJECUTA
}

if ($jobProfile->requires_driver_license) {
    // ESTE CÓDIGO YA NO SE EJECUTA
}
*/
```

---

## 🎨 Cambios en Interfaz - apply.blade.php

### Paso 2: Formación Académica

```html
<div class="step" x-show="currentStep === 2">
    <h3 class="text-xl font-semibold mb-4">Formación Académica</h3>

    <template x-for="(academic, index) in academics" :key="index">
        <div class="card mb-4 p-4">
            <div class="grid grid-cols-2 gap-4">
                <!-- Nivel educativo -->
                <div>
                    <label>Nivel Educativo <span class="text-red-500">*</span></label>
                    <select
                        :name="`academics[${index}][degree_type]`"
                        class="form-select"
                        required
                        x-model="academics[index].degree_type"
                    >
                        <option value="">Seleccione nivel</option>
                        <option value="SECUNDARIA">Secundaria Completa</option>
                        <option value="TECNICO">Título Técnico</option>
                        <option value="BACHILLER">Bachiller Universitario</option>
                        <option value="TITULO">Título Profesional</option>
                        <option value="MAESTRIA">Maestría</option>
                        <option value="DOCTORADO">Doctorado</option>
                    </select>
                </div>

                <!-- Carrera (SELECT desde catálogo normalizado) -->
                <div>
                    <label>Carrera Profesional <span class="text-red-500">*</span></label>
                    <select
                        :name="`academics[${index}][career_id]`"
                        class="form-select"
                        required
                        x-model="academics[index].career_id"
                        @change="checkCareerMatch(index)"
                    >
                        <option value="">Seleccione una carrera</option>
                        @foreach($academicCareers->groupBy('category') as $category => $careers)
                            <optgroup label="{{ $category }}">
                                @foreach($careers as $career)
                                    <option value="{{ $career->id }}" data-requires-colegiatura="{{ $career->requires_colegiatura }}">
                                        {{ $career->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <!-- Advertencia si no coincide con requisito del perfil -->
                    <div x-show="academics[index].career_id && !isCareerAccepted(academics[index].career_id)"
                         class="mt-2 p-2 bg-yellow-50 border border-yellow-300 rounded text-sm">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                        <span class="text-yellow-800">
                            La carrera seleccionada no coincide con el requisito del perfil:
                            <strong>{{ $jobProfile->career_field }}</strong>.
                            Puedes postular igual, pero es probable que seas declarado NO APTO.
                        </span>
                    </div>
                </div>

                <!-- Institución -->
                <div class="col-span-2">
                    <label>Institución Educativa <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        :name="`academics[${index}][institution_name]`"
                        class="form-control"
                        placeholder="Ej: Universidad Nacional de San Antonio Abad del Cusco"
                        required
                    >
                </div>

                <!-- Título obtenido -->
                <div class="col-span-2">
                    <label>Título/Grado Obtenido</label>
                    <input
                        type="text"
                        :name="`academics[${index}][degree_title]`"
                        class="form-control"
                        placeholder="Ej: Ingeniero de Sistemas"
                    >
                </div>

                <!-- Fecha de expedición -->
                <div>
                    <label>Fecha de Expedición</label>
                    <input
                        type="date"
                        :name="`academics[${index}][issue_date]`"
                        class="form-control"
                        x-model="academics[index].issue_date"
                    >
                </div>
            </div>

            <!-- Botón eliminar (si hay más de 1) -->
            <button
                type="button"
                x-show="academics.length > 1"
                @click="removeAcademic(index)"
                class="btn btn-sm btn-outline-danger mt-3"
            >
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </template>

    <button type="button" @click="addAcademic()" class="btn btn-outline-primary">
        <i class="fas fa-plus"></i> Agregar otro título/grado
    </button>
</div>
```

**Alpine.js**:

```javascript
academics: [
    { degree_type: '', career_id: '', institution_name: '', degree_title: '', issue_date: '' }
],

// IDs de carreras aceptadas para el perfil (incluye equivalentes)
acceptedCareerIds: @json($acceptedCareerIds ?? []),

isCareerAccepted(careerId) {
    return this.acceptedCareerIds.includes(careerId);
},

addAcademic() {
    this.academics.push({
        degree_type: '',
        career_id: '',
        institution_name: '',
        degree_title: '',
        issue_date: ''
    });
}
```

---

### Paso 4: Capacitaciones (CHECKBOXES DINÁMICOS)

```html
<div class="step" x-show="currentStep === 4">
    <h3 class="text-xl font-semibold mb-4">Capacitaciones y Cursos</h3>

    <!-- CAPACITACIONES REQUERIDAS del perfil -->
    @if(!empty($jobProfile->required_courses) && count($jobProfile->required_courses) > 0)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
            <h4 class="font-semibold text-blue-900 mb-3">
                <i class="fas fa-star text-blue-600"></i>
                Capacitaciones Requeridas (Obligatorias)
            </h4>
            <p class="text-sm text-gray-600 mb-4">
                Debes marcar las capacitaciones que tienes de la siguiente lista.
                Si no tienes alguna, puedes postular igual pero serás declarado NO APTO.
            </p>

            @foreach($jobProfile->required_courses as $index => $courseName)
                <div class="form-check mb-3 p-3 bg-white border rounded">
                    <input
                        type="checkbox"
                        name="trainings[required][{{ $index }}][has_course]"
                        id="required_course_{{ $index }}"
                        class="form-check-input"
                        value="1"
                        x-model="requiredTrainings[{{ $index }}].has"
                    >
                    <label for="required_course_{{ $index }}" class="form-check-label font-medium">
                        {{ $courseName }}
                    </label>

                    <!-- Campos adicionales si marca el checkbox -->
                    <div x-show="requiredTrainings[{{ $index }}].has" class="mt-3 ml-6 grid grid-cols-3 gap-3">
                        <input type="hidden" name="trainings[required][{{ $index }}][course_name]" value="{{ $courseName }}">

                        <div>
                            <label class="text-sm">Institución</label>
                            <input
                                type="text"
                                name="trainings[required][{{ $index }}][institution]"
                                class="form-control form-control-sm"
                                placeholder="Nombre de institución"
                            >
                        </div>

                        <div>
                            <label class="text-sm">Horas Académicas</label>
                            <input
                                type="number"
                                name="trainings[required][{{ $index }}][academic_hours]"
                                class="form-control form-control-sm"
                                min="1"
                                placeholder="Ej: 40"
                            >
                        </div>

                        <div>
                            <label class="text-sm">Fecha de Certificación</label>
                            <input
                                type="date"
                                name="trainings[required][{{ $index }}][end_date]"
                                class="form-control form-control-sm"
                            >
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Contador de capacitaciones marcadas -->
            <div class="mt-4 p-3 rounded" :class="allRequiredTrainingsChecked() ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300'">
                <span x-text="`${countRequiredTrainings()} de {{ count($jobProfile->required_courses) }} capacitaciones requeridas`"></span>
                <i :class="allRequiredTrainingsChecked() ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600'"></i>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Este perfil no requiere capacitaciones específicas.
        </div>
    @endif

    <!-- Otras capacitaciones (opcional) -->
    <div class="mt-4">
        <h4 class="font-semibold mb-3">Otras Capacitaciones (Opcional)</h4>

        <template x-for="(training, index) in otherTrainings" :key="index">
            <div class="card mb-3 p-3">
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="text-sm">Nombre del Curso/Capacitación</label>
                        <input
                            type="text"
                            :name="`trainings[other][${index}][course_name]`"
                            class="form-control"
                            placeholder="Ej: Gestión de Proyectos con PMBOK"
                        >
                    </div>

                    <div>
                        <label class="text-sm">Horas</label>
                        <input
                            type="number"
                            :name="`trainings[other][${index}][academic_hours]`"
                            class="form-control"
                            min="1"
                        >
                    </div>

                    <div>
                        <label class="text-sm">Institución</label>
                        <input
                            type="text"
                            :name="`trainings[other][${index}][institution]`"
                            class="form-control"
                        >
                    </div>

                    <div>
                        <label class="text-sm">Fecha</label>
                        <input
                            type="date"
                            :name="`trainings[other][${index}][end_date]`"
                            class="form-control"
                        >
                    </div>
                </div>

                <button type="button" @click="removeOtherTraining(index)" class="btn btn-sm btn-outline-danger mt-2">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </template>

        <button type="button" @click="addOtherTraining()" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Agregar otra capacitación
        </button>
    </div>
</div>
```

**Alpine.js**:

```javascript
requiredTrainings: @json(
    array_fill(0, count($jobProfile->required_courses ?? []), ['has' => false])
),

otherTrainings: [],

countRequiredTrainings() {
    return this.requiredTrainings.filter(t => t.has).length;
},

allRequiredTrainingsChecked() {
    return this.requiredTrainings.every(t => t.has);
},

addOtherTraining() {
    this.otherTrainings.push({
        course_name: '',
        institution: '',
        academic_hours: '',
        end_date: ''
    });
}
```

---

## 🛠️ Comandos Artisan

### 1. Importar Carreras desde Dataset SUNEDU

```php
// Modules/Catalog/app/Console/ImportAcademicCareersCommand.php

php artisan catalog:import-careers {file}

/**
 * Proceso:
 * 1. Lee CSV/JSON del dataset SUNEDU
 * 2. Extrae: nombre_carrera, categoria, subcategoria
 * 3. Genera código único: slugify(nombre)
 * 4. Inserta en academic_careers
 * 5. Agrupa por categoría para crear equivalencias automáticas
 */
```

---

### 2. Mapear Carreras de Perfiles a Catálogo

```php
// Modules/JobProfile/app/Console/NormalizeJobProfileCareersCommand.php

php artisan job-profiles:normalize-careers

/**
 * Proceso:
 * 1. Extrae career_field únicos de job_profiles
 * 2. Para cada uno:
 *    a. Busca match exacto en academic_careers.name (case-insensitive)
 *       → confidence_score = 100.00, mapping_method = 'EXACT_MATCH'
 *
 *    b. Si no hay match exacto, usa similitud de Levenshtein:
 *       $similarity = similar_text(strtolower($profileCareer), strtolower($catalogCareer))
 *       → confidence_score = $similarity, mapping_method = 'FUZZY_MATCH'
 *
 *    c. Crea registro en job_profile_career_mappings:
 *       - is_approved = true si confidence >= 90%
 *       - is_approved = false si confidence < 90% (requiere revisión manual)
 *
 * 3. Genera reporte:
 *    - Total de perfiles procesados
 *    - Mapeos automáticos (confidence >= 90%)
 *    - Mapeos que requieren revisión manual
 *    - Carreras sin match (crear manualmente)
 */

// Ejemplo de output:
// ✓ 45 perfiles mapeados automáticamente
// ⚠ 8 perfiles requieren revisión manual
// ✗ 3 perfiles sin match (crear entrada manual)
```

---

### 3. Crear Equivalencias Automáticas por Categoría

```php
php artisan catalog:generate-equivalences

/**
 * Proceso:
 * 1. Agrupa academic_careers por 'category'
 * 2. Para cada grupo:
 *    - Crea relaciones de equivalencia entre todas las carreras del grupo
 *    - equivalence_type = 'SAME_CATEGORY'
 *
 * Ejemplo:
 * Categoría: "Ingeniería"
 *   - Ingeniería de Sistemas ≡ Ingeniería Informática
 *   - Ingeniería Civil ≡ Ingeniería de Construcción
 *   - etc.
 */
```

---

## ✅ Checklist de Implementación

### Fase 1: Base de Datos y Catálogos (4-5 horas)

- [ ] Crear migración: `academic_careers` table
- [ ] Crear migración: `job_profile_career_mappings` table
- [ ] Crear migración: `academic_career_equivalences` table
- [ ] Crear migración: Agregar `career_id` a `application_academics`
- [ ] Ejecutar migraciones
- [ ] Preparar dataset SUNEDU (CSV/JSON de carreras)

### Fase 2: Comandos de Importación (3-4 horas)

- [ ] Crear comando `ImportAcademicCareersCommand`
- [ ] Ejecutar: `php artisan catalog:import-careers sunedu_careers.csv`
- [ ] Validar 2000+ carreras importadas
- [ ] Crear comando `GenerateEquivalencesCommand`
- [ ] Ejecutar: `php artisan catalog:generate-equivalences`

### Fase 3: Mapeo de Perfiles Legacy (2-3 horas)

- [ ] Crear comando `NormalizeJobProfileCareersCommand`
- [ ] Implementar algoritmo de similitud (Levenshtein)
- [ ] Ejecutar: `php artisan job-profiles:normalize-careers`
- [ ] Revisar reporte de mapeos
- [ ] Aprobar manualmente mapeos con confidence < 90%

### Fase 4: Entities/Models (2 horas)

- [ ] Crear Entity `AcademicCareer.php`
- [ ] Crear Entity `JobProfileCareerMapping.php`
- [ ] Crear Entity `AcademicCareerEquivalence.php`
- [ ] Actualizar `ApplicationAcademic.php` (relación career_id)

### Fase 5: AutoGrader Mejorado (3-4 horas)

- [ ] Reescribir `validateAcademics()` con:
  - Soporte para `education_levels` (array) con lógica OR
  - Validación de carrera usando tabla de mapeo
  - Búsqueda de carreras equivalentes
  - Validación de `colegiatura_required`
- [ ] Implementar `getEquivalentCareerIds()` helper
- [ ] Implementar `meetsEducationLevel()` con jerarquía
- [ ] ELIMINAR validaciones de campos inexistentes:
  - Comentar `validateOsceCertification()`
  - Comentar `validateDriverLicense()`
  - Comentar `validateProfessionalRegistry()` (usar solo colegiatura_required)
- [ ] Probar con casos de prueba

### Fase 6: Controlador - JobPostingController (2-3 horas)

- [ ] Método `apply()`: Cargar catálogo de carreras agrupadas por categoría
- [ ] Calcular `$acceptedCareerIds` (mapeo + equivalencias) para pasar a vista
- [ ] Método `storeApplication()`: Validar que `career_id` exista en catálogo
- [ ] Procesar checkboxes de capacitaciones requeridas
- [ ] Procesar capacitaciones "otras" (texto libre)

### Fase 7: Interfaz - apply.blade.php (4-5 horas)

- [ ] Paso 2 (Formación Académica):
  - SELECT de carreras desde `$academicCareers` agrupadas por categoría
  - Alpine.js para validación en tiempo real vs `$acceptedCareerIds`
  - Advertencia visual si carrera no coincide
- [ ] Paso 4 (Capacitaciones):
  - CHECKBOXES dinámicos generados desde `$jobProfile->required_courses`
  - Campos adicionales (institución, horas, fecha) si marca checkbox
  - Contador de capacitaciones marcadas
  - Sección "Otras capacitaciones" con inputs libres
- [ ] Alpine.js:
  - `isCareerAccepted(careerId)` función
  - `countRequiredTrainings()` función
  - `allRequiredTrainingsChecked()` función

### Fase 8: Interfaz Admin - Revisión de Mapeos (3-4 horas)

- [ ] Vista: `job-profile-career-mappings/index.blade.php`
  - Lista de mapeos con confidence < 90%
  - Botón "Aprobar" / "Rechazar"
  - Editar carrera mapeada si es incorrecta
- [ ] Controlador: `JobProfileCareerMappingController`
  - `index()`: Lista mapeos pendientes
  - `approve()`: Marcar is_approved = true
  - `update()`: Cambiar career_id del mapeo

### Fase 9: Testing (3-4 horas)

- [ ] Test unitario: AutoGraderService::validateAcademics()
  - Caso 1: Nivel insuficiente → NO_APTO
  - Caso 2: Carrera no coincide → NO_APTO
  - Caso 3: Carrera equivalente → APTO
  - Caso 4: Sin colegiatura cuando se requiere → NO_APTO
  - Caso 5: Múltiples niveles educativos (lógica OR) → APTO
- [ ] Test integración: Flujo completo
  - Crear perfil con career_field="Ingeniería de Sistemas"
  - Ejecutar comando normalize-careers
  - Postulante selecciona carrera equivalente
  - AutoGrader debe marcar APTO
- [ ] Probar con datos reales de producción

### Fase 10: Documentación (2 horas)

- [ ] Actualizar README del módulo Catalog
- [ ] Manual de usuario: Cómo postular con nuevo formulario
- [ ] Manual de admin: Cómo revisar y aprobar mapeos
- [ ] Guía de comandos artisan

---

## 📊 Estimación Total

**Total: 30-38 horas** (aproximadamente 4-5 días laborales)

---

## 🎯 Resultado Final

Al completar esta implementación:

✅ **Calificación automática 100% precisa** en carreras profesionales
✅ **No se modifican perfiles aprobados** (solo se mapean)
✅ **Dataset SUNEDU** con 2000+ carreras oficiales
✅ **Equivalencias automáticas** por categoría (Ingeniería, Derecho, etc.)
✅ **Capacitaciones como checkboxes** (match exacto de strings)
✅ **Soporte para `education_levels` array** con lógica OR
✅ **Validación solo de `colegiatura_required`** (elimina referencias a campos inexistentes)
✅ **Interfaz intuitiva** para postulantes
✅ **Panel de revisión** para admin en mapeos dudosos

---

## 🚨 Consideraciones Finales

### Dataset SUNEDU

- **Fuente**: Portal de transparencia SUNEDU
- **Formato**: CSV o JSON
- **Campos**: nombre_programa, categoria, subcategoria, universidad, departamento
- **Procesamiento**: Extraer programas únicos, agrupar por categoría

### Algoritmo de Similitud

```php
function calculateSimilarity($str1, $str2) {
    $str1 = strtolower(trim($str1));
    $str2 = strtolower(trim($str2));

    // Match exacto
    if ($str1 === $str2) {
        return 100.00;
    }

    // Similitud de Levenshtein
    $levenshtein = levenshtein($str1, $str2);
    $maxLen = max(strlen($str1), strlen($str2));
    $similarity = (1 - ($levenshtein / $maxLen)) * 100;

    // Similar_text (alternativa)
    similar_text($str1, $str2, $percent);

    return max($similarity, $percent);
}
```

### Seguridad

- Validar que `career_id` exista antes de guardar
- Sanitizar inputs de capacitaciones "otras"
- Auditar cambios en mapeos (quién aprobó/rechazó)

### Performance

- Eager loading: `->with(['careerMapping.career.equivalentCareers'])`
- Cachear catálogo de carreras (no cambia frecuentemente)
- Índices en columnas de búsqueda

---

**Documento generado:** 2026-01-04
**Sistema:** CAS - Municipalidad de San Jerónimo
**Módulo:** Application - AutoGrader + Catalog
**Versión:** 2.0 - Normalización sin Modificar Perfiles Aprobados
