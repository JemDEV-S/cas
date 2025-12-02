@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Editar Perfil de Puesto</h1>
                <p class="mt-1 text-sm text-gray-600">Código: <code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('jobprofile.profiles.show', $jobProfile->id) }}">
                    <x-button variant="secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </x-button>
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('jobprofile.profiles.update', $jobProfile->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Información General -->
        <x-card title="Información General">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    type="text"
                    name="title"
                    label="Título del Puesto"
                    :value="old('title', $jobProfile->title)"
                    required
                    placeholder="Ej: Especialista en Recursos Humanos"
                />

                <x-form.input
                    type="text"
                    name="profile_name"
                    label="Nombre del Perfil"
                    :value="old('profile_name', $jobProfile->profile_name)"
                    placeholder="Nombre interno del perfil"
                />

                <x-form.select
                    name="organizational_unit_id"
                    label="Unidad Organizacional"
                    :options="$organizationalUnits ?? []"
                    :selected="old('organizational_unit_id', $jobProfile->organizational_unit_id)"
                    required
                    placeholder="Seleccione una unidad"
                />

                <x-form.select
                    name="position_code_id"
                    label="Código de Posición"
                    :options="$positionCodes ?? []"
                    :selected="old('position_code_id', $jobProfile->position_code_id)"
                    placeholder="Seleccione un código"
                />

                <!-- Campo para seleccionar convocatoria -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Convocatoria (Opcional)
                    </label>
                    <select name="job_posting_id" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full">
                        <option value="">Sin convocatoria</option>
                        @foreach(\Modules\JobPosting\Entities\JobPosting::draft()->orderBy('created_at', 'desc')->get() as $posting)
                            <option value="{{ $posting->id }}" {{ old('job_posting_id', $jobProfile->job_posting_id) == $posting->id ? 'selected' : '' }}>
                                {{ $posting->code }} - {{ $posting->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> Asocie este perfil a una convocatoria en borrador
                    </p>
                    @if($jobProfile->jobPosting && !$jobProfile->jobPosting->isDraft())
                    <p class="mt-1 text-xs text-amber-600">
                        <i class="fas fa-exclamation-triangle"></i> La convocatoria actual no está en borrador, solo se pueden asociar perfiles a convocatorias en borrador
                    </p>
                    @endif
                </div>

                <x-form.select
                    name="job_level"
                    label="Nivel del Puesto"
                    :options="[
                        'junior' => 'Junior',
                        'mid' => 'Intermedio',
                        'senior' => 'Senior',
                        'specialist' => 'Especialista',
                        'coordinator' => 'Coordinador',
                        'head' => 'Jefe',
                        'manager' => 'Gerente'
                    ]"
                    :selected="old('job_level', $jobProfile->job_level)"
                    placeholder="Seleccione un nivel"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Régimen Laboral <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        value="CAS - Contrato Administrativo de Servicios (D.L. 1057)"
                        class="border-gray-300 bg-gray-100 rounded-md shadow-sm w-full"
                        disabled
                        readonly>
                    <p class="mt-1 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i> Solo se admiten contratos CAS
                    </p>
                </div>

                <x-form.input
                    type="number"
                    name="total_vacancies"
                    label="Total de Vacantes"
                    :value="old('total_vacancies', $jobProfile->total_vacancies)"
                    required
                    min="1"
                />
            </div>

            <div class="mt-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Descripción del Puesto
                </label>
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Descripción general del puesto">{{ old('description', $jobProfile->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="mission" class="block text-sm font-medium text-gray-700 mb-1">
                    Misión del Puesto
                </label>
                <textarea
                    name="mission"
                    id="mission"
                    rows="3"
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Razón de ser del puesto">{{ old('mission', $jobProfile->mission) }}</textarea>
                @error('mission')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="justification" class="block text-sm font-medium text-gray-700 mb-1">
                    Justificación <span class="text-red-500">*</span>
                </label>
                <select
                    name="justification"
                    id="justification"
                    required
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full">
                    <option value="">Seleccione una justificación</option>
                    @foreach(\Modules\JobProfile\Entities\JobProfile::getJustificationOptions() as $value => $label)
                        <option value="{{ $value }}" {{ old('justification', $jobProfile->justification) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    <i class="fas fa-info-circle"></i> Seleccione la justificación que corresponda a la necesidad del puesto
                </p>
                @error('justification')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Información del Contrato -->
        <x-card title="📅 Información del Contrato">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    type="date"
                    name="contract_start_date"
                    label="Fecha de Inicio del Contrato"
                    :value="old('contract_start_date', $jobProfile->contract_start_date?->format('Y-m-d'))"
                    placeholder="dd/mm/yyyy"
                />

                <x-form.input
                    type="date"
                    name="contract_end_date"
                    label="Fecha de Fin del Contrato"
                    :value="old('contract_end_date', $jobProfile->contract_end_date?->format('Y-m-d'))"
                    placeholder="dd/mm/yyyy"
                />

                <div class="md:col-span-2">
                    <x-form.input
                        type="text"
                        name="work_location"
                        label="Lugar de Prestación del Servicio"
                        :value="old('work_location', $jobProfile->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO')"
                        placeholder="Ej: MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-form.input
                        type="text"
                        name="selection_process_name"
                        label="Nombre del Proceso de Selección"
                        :value="old('selection_process_name', $jobProfile->selection_process_name)"
                        placeholder="Ej: PROCESO DE SELECCIÓN CAS VI-2025"
                    />
                </div>
            </div>
        </x-card>

        <!-- Requisitos Académicos -->
        <x-card title="Requisitos Académicos">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.select
                    name="education_level"
                    label="Nivel Educativo"
                    :options="$educationOptions"
                    :selected="old('education_level', $jobProfile->education_level)"
                    required
                />

                <x-form.input
                    type="text"
                    name="career_field"
                    label="Área de Estudios"
                    :value="old('career_field', $jobProfile->career_field)"
                    placeholder="Ej: Administración, Ingeniería, Derecho"
                />

                <x-form.input
                    type="text"
                    name="title_required"
                    label="Título Requerido"
                    :value="old('title_required', $jobProfile->title_required)"
                    placeholder="Ej: Licenciado en Administración"
                />

                <div class="flex items-center mt-6">
                    <input
                        type="checkbox"
                        name="colegiatura_required"
                        id="colegiatura_required"
                        value="1"
                        {{ old('colegiatura_required', $jobProfile->colegiatura_required) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="colegiatura_required" class="ml-2 block text-sm text-gray-900">
                        Colegiatura Requerida
                    </label>
                </div>
            </div>
        </x-card>

        <!-- Experiencia Laboral -->
        <x-card title="Experiencia Laboral">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    type="number"
                    name="general_experience_years"
                    label="Experiencia General (años)"
                    :value="old('general_experience_years', $jobProfile->general_experience_years)"
                    step="0.5"
                    min="0"
                />

                <x-form.input
                    type="number"
                    name="specific_experience_years"
                    label="Experiencia Específica (años)"
                    :value="old('specific_experience_years', $jobProfile->specific_experience_years)"
                    step="0.5"
                    min="0"
                />
            </div>

            <div class="mt-4">
                <label for="specific_experience_description" class="block text-sm font-medium text-gray-700 mb-1">
                    Detalle de Experiencia Específica
                </label>
                <textarea
                    name="specific_experience_description"
                    id="specific_experience_description"
                    rows="3"
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Describa la experiencia específica requerida">{{ old('specific_experience_description', $jobProfile->specific_experience_description) }}</textarea>
                @error('specific_experience_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Capacitación y Cursos -->
        <x-card title="Capacitación y Cursos Requeridos">
            <div id="courses-container" class="space-y-3">
                @php
                    $courses = old('required_courses', $jobProfile->required_courses ?? ['']);
                    $courses = is_array($courses) ? $courses : [''];
                    if (empty($courses)) $courses = [''];
                @endphp
                @foreach($courses as $index => $course)
                    <div class="flex gap-2 course-item">
                        <input
                            type="text"
                            name="required_courses[]"
                            value="{{ $course }}"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Curso de Gestión Pública">
                        <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <x-button type="button" variant="secondary" onclick="addCourse()">
                    <i class="fas fa-plus mr-2"></i> Agregar Curso
                </x-button>
            </div>
        </x-card>

        <!-- Conocimientos y Áreas de Conocimiento -->
        <x-card title="Conocimientos Técnicos y Áreas de Conocimiento">
            <div id="knowledge-container" class="space-y-3">
                @php
                    $knowledgeAreas = old('knowledge_areas', $jobProfile->knowledge_areas ?? ['']);
                    $knowledgeAreas = is_array($knowledgeAreas) ? $knowledgeAreas : [''];
                    if (empty($knowledgeAreas)) $knowledgeAreas = [''];
                @endphp
                @foreach($knowledgeAreas as $index => $knowledge)
                    <div class="flex gap-2 knowledge-item">
                        <input
                            type="text"
                            name="knowledge_areas[]"
                            value="{{ $knowledge }}"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Microsoft Office Avanzado, Conocimiento en legislación laboral">
                        <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <x-button type="button" variant="secondary" onclick="addKnowledge()">
                    <i class="fas fa-plus mr-2"></i> Agregar Conocimiento
                </x-button>
            </div>
        </x-card>

        <!-- Competencias Requeridas -->
        <x-card title="Competencias Requeridas">
            <div id="competencies-container" class="space-y-3">
                @php
                    $competencies = old('required_competencies', $jobProfile->required_competencies ?? ['']);
                    $competencies = is_array($competencies) ? $competencies : [''];
                    if (empty($competencies)) $competencies = [''];
                @endphp
                @foreach($competencies as $index => $competency)
                    <div class="flex gap-2 competency-item">
                        <input
                            type="text"
                            name="required_competencies[]"
                            value="{{ $competency }}"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Trabajo en equipo, Liderazgo, Orientación a resultados">
                        <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <x-button type="button" variant="secondary" onclick="addCompetency()">
                    <i class="fas fa-plus mr-2"></i> Agregar Competencia
                </x-button>
            </div>
        </x-card>

        <!-- Funciones Principales -->
        <x-card title="Funciones Principales del Puesto">
            <div id="functions-container" class="space-y-3">
                @php
                    $functions = old('main_functions', $jobProfile->main_functions ?? ['']);
                    $functions = is_array($functions) ? $functions : [''];
                    if (empty($functions)) $functions = [''];
                @endphp
                @foreach($functions as $index => $function)
                    <div class="flex gap-2 function-item">
                        <input
                            type="text"
                            name="main_functions[]"
                            value="{{ $function }}"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Descripción de la función">
                        <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <x-button type="button" variant="secondary" onclick="addFunction()">
                    <i class="fas fa-plus mr-2"></i> Agregar Función
                </x-button>
            </div>
        </x-card>

        <!-- Condiciones de Trabajo -->
        <x-card title="Condiciones de Trabajo">
            <div>
                <label for="working_conditions" class="block text-sm font-medium text-gray-700 mb-1">
                    Condiciones de Trabajo
                </label>
                <textarea
                    name="working_conditions"
                    id="working_conditions"
                    rows="3"
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Describa las condiciones especiales del trabajo (horario, ubicación, etc.)">{{ old('working_conditions', $jobProfile->working_conditions) }}</textarea>
                @error('working_conditions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Botones de Acción -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('jobprofile.profiles.show', $jobProfile->id) }}">
                <x-button type="button" variant="secondary">
                    Cancelar
                </x-button>
            </a>
            <x-button type="submit" variant="primary">
                <i class="fas fa-save mr-2"></i> Actualizar Perfil
            </x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Funciones Principales
function addFunction() {
    const container = document.getElementById('functions-container');
    const newFunction = document.createElement('div');
    newFunction.className = 'flex gap-2 function-item';
    newFunction.innerHTML = `
        <input
            type="text"
            name="main_functions[]"
            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
            placeholder="Descripción de la función">
        <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newFunction);
}

function removeFunction(button) {
    const container = document.getElementById('functions-container');
    const items = container.getElementsByClassName('function-item');
    if (items.length > 1) {
        button.closest('.function-item').remove();
    } else {
        alert('Debe mantener al menos una función');
    }
}

// Cursos
function addCourse() {
    const container = document.getElementById('courses-container');
    const newCourse = document.createElement('div');
    newCourse.className = 'flex gap-2 course-item';
    newCourse.innerHTML = `
        <input
            type="text"
            name="required_courses[]"
            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
            placeholder="Ej: Curso de Gestión Pública">
        <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newCourse);
}

function removeCourse(button) {
    const container = document.getElementById('courses-container');
    const items = container.getElementsByClassName('course-item');
    if (items.length > 1) {
        button.closest('.course-item').remove();
    }
}

// Conocimientos
function addKnowledge() {
    const container = document.getElementById('knowledge-container');
    const newKnowledge = document.createElement('div');
    newKnowledge.className = 'flex gap-2 knowledge-item';
    newKnowledge.innerHTML = `
        <input
            type="text"
            name="knowledge_areas[]"
            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
            placeholder="Ej: Microsoft Office Avanzado, Conocimiento en legislación laboral">
        <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newKnowledge);
}

function removeKnowledge(button) {
    const container = document.getElementById('knowledge-container');
    const items = container.getElementsByClassName('knowledge-item');
    if (items.length > 1) {
        button.closest('.knowledge-item').remove();
    }
}

// Competencias
function addCompetency() {
    const container = document.getElementById('competencies-container');
    const newCompetency = document.createElement('div');
    newCompetency.className = 'flex gap-2 competency-item';
    newCompetency.innerHTML = `
        <input
            type="text"
            name="required_competencies[]"
            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
            placeholder="Ej: Trabajo en equipo, Liderazgo, Orientación a resultados">
        <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(newCompetency);
}

function removeCompetency(button) {
    const container = document.getElementById('competencies-container');
    const items = container.getElementsByClassName('competency-item');
    if (items.length > 1) {
        button.closest('.competency-item').remove();
    }
}
</script>
@endpush
@endsection
