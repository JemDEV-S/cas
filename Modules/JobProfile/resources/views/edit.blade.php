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
                    name="work_regime"
                    label="Régimen Laboral"
                    :options="[
                        'cas' => 'CAS (Contrato Administrativo de Servicios)',
                        '276' => 'D.L. 276 (Nombrado)',
                        '728' => 'D.L. 728 (Contrato Indefinido)',
                        '1057' => 'D.L. 1057 (CAS Especial)'
                    ]"
                    :selected="old('work_regime', $jobProfile->work_regime)"
                    required
                />

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
                <textarea
                    name="justification"
                    id="justification"
                    rows="3"
                    required
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Justifique la necesidad de este perfil de puesto">{{ old('justification', $jobProfile->justification) }}</textarea>
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

        <!-- Funciones Principales -->
        <x-card title="Funciones Principales del Puesto">
            <div id="functions-container" class="space-y-3">
                @php
                    $functions = old('main_functions', $jobProfile->main_functions ?? ['']);
                    $functions = is_array($functions) ? $functions : [''];
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
</script>
@endpush
@endsection
