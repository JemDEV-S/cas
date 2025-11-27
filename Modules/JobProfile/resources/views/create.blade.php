@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Crear Perfil de Puesto</h1>
                <p class="mt-1 text-sm text-gray-600">Complete la información del perfil de puesto CAS</p>
            </div>
            <a href="{{ route('jobprofile.index') }}">
                <x-button variant="secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </x-button>
            </a>
        </div>
    </div>

    <form action="{{ route('jobprofile.profiles.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Hidden fields -->
        <input type="hidden" name="contract_type" value="cas">
        <input type="hidden" name="work_regime" value="cas">
        @if($isAreaUser ?? false)
            <input type="hidden" name="requesting_unit_id" value="{{ $userOrganizationalUnit }}">
        @endif

        <!-- Información General -->
        <x-card title="Información General">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    type="text"
                    name="title"
                    label="Título del Puesto"
                    :value="old('title')"
                    required
                    placeholder="Ej: Especialista en Recursos Humanos"
                />

                <x-form.input
                    type="text"
                    name="profile_name"
                    label="Nombre del Perfil"
                    :value="old('profile_name')"
                    placeholder="Nombre interno del perfil"
                />

                @if($isAreaUser ?? false)
                    <!-- Campo bloqueado para area-user -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Unidad Organizacional <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="organizational_unit_id"
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full bg-gray-100"
                            required
                            disabled>
                            @foreach($organizationalUnits ?? [] as $id => $name)
                                @if($id == $userOrganizationalUnit)
                                    <option value="{{ $id }}" selected>{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" name="organizational_unit_id" value="{{ $userOrganizationalUnit }}">
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle"></i> Este campo está bloqueado con su unidad organizacional
                        </p>
                        @error('organizational_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <x-form.select
                        name="organizational_unit_id"
                        label="Unidad Organizacional"
                        :options="$organizationalUnits ?? []"
                        :selected="old('organizational_unit_id')"
                        required
                        placeholder="Seleccione una unidad"
                    />
                @endif

                <x-form.select
                    name="position_code_id"
                    label="Código de Posición"
                    :options="$positionCodes ?? []"
                    :selected="old('position_code_id')"
                    placeholder="Seleccione un código"
                />

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
                    :selected="old('job_level')"
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
                    :value="old('total_vacancies', 1)"
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
                    placeholder="Descripción general del puesto">{{ old('description') }}</textarea>
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
                    placeholder="Razón de ser del puesto">{{ old('mission') }}</textarea>
                @error('mission')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="justification" class="block text-sm font-medium text-gray-700 mb-1">
                    Justificación <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="justification"
                    id="justification"
                    rows="3"
                    required
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Justifique la necesidad de este perfil de puesto">{{ old('justification') }}</textarea>
                @error('justification')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Requisitos Académicos -->
        <x-card title="Requisitos Académicos">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.select
                    name="education_level"
                    label="Nivel Educativo"
                    :options="[
                        'secondary' => 'Secundaria Completa',
                        'technical' => 'Técnico',
                        'bachelor' => 'Bachiller',
                        'graduate' => 'Titulado',
                        'master' => 'Maestría',
                        'doctorate' => 'Doctorado'
                    ]"
                    :selected="old('education_level')"
                    required
                />

                <x-form.input
                    type="text"
                    name="career_field"
                    label="Área de Estudios"
                    :value="old('career_field')"
                    placeholder="Ej: Administración, Ingeniería, Derecho"
                />

                <x-form.input
                    type="text"
                    name="title_required"
                    label="Título Requerido"
                    :value="old('title_required')"
                    placeholder="Ej: Licenciado en Administración"
                />

                <div class="flex items-center mt-6">
                    <input
                        type="checkbox"
                        name="colegiatura_required"
                        id="colegiatura_required"
                        value="1"
                        {{ old('colegiatura_required') ? 'checked' : '' }}
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
                    :value="old('general_experience_years', 0)"
                    step="0.5"
                    min="0"
                />

                <x-form.input
                    type="number"
                    name="specific_experience_years"
                    label="Experiencia Específica (años)"
                    :value="old('specific_experience_years', 0)"
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
                    placeholder="Describa la experiencia específica requerida">{{ old('specific_experience_description') }}</textarea>
                @error('specific_experience_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Capacitación y Cursos -->
        <x-card title="Capacitación y Cursos Requeridos">
            <div id="courses-container" class="space-y-3">
                @if(old('required_courses'))
                    @foreach(old('required_courses') as $index => $course)
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
                @else
                    <div class="flex gap-2 course-item">
                        <input
                            type="text"
                            name="required_courses[]"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Curso de Gestión Pública">
                        <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
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
                @if(old('knowledge_areas'))
                    @foreach(old('knowledge_areas') as $index => $knowledge)
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
                @else
                    <div class="flex gap-2 knowledge-item">
                        <input
                            type="text"
                            name="knowledge_areas[]"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Microsoft Office Avanzado, Conocimiento en legislación laboral">
                        <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
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
                @if(old('required_competencies'))
                    @foreach(old('required_competencies') as $index => $competency)
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
                @else
                    <div class="flex gap-2 competency-item">
                        <input
                            type="text"
                            name="required_competencies[]"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Ej: Trabajo en equipo, Liderazgo, Orientación a resultados">
                        <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
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
                @if(old('main_functions'))
                    @foreach(old('main_functions') as $index => $function)
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
                @else
                    <div class="flex gap-2 function-item">
                        <input
                            type="text"
                            name="main_functions[]"
                            class="flex-1 border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                            placeholder="Descripción de la función">
                        <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endif
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
                    placeholder="Describa las condiciones especiales del trabajo (horario, ubicación, etc.)">{{ old('working_conditions') }}</textarea>
                @error('working_conditions')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Botones de Acción -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('jobprofile.index') }}">
                <x-button type="button" variant="secondary">
                    Cancelar
                </x-button>
            </a>
            <x-button type="submit" variant="primary">
                <i class="fas fa-save mr-2"></i> Guardar Perfil
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
