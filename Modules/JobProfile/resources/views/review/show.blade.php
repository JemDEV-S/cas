@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Revisar y Editar Perfil de Puesto</h1>
                    <p class="mt-1 text-sm text-gray-600">Código: <code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code></p>
                    <p class="mt-1 text-sm text-blue-600"><i class="fas fa-info-circle"></i> Puede corregir directamente el perfil antes de aprobarlo</p>
                </div>
                <a href="{{ route('jobprofile.review.index') }}">
                    <x-button variant="secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </x-button>
                </a>
            </div>
        </div>

        <form action="{{ route('jobprofile.review.update', $jobProfile->id) }}" method="POST" class="space-y-6" id="reviewForm">
            @csrf
            @method('PUT')

            <!-- Hidden fields -->
            <input type="hidden" name="contract_type" value="cas">
            <input type="hidden" name="work_regime" value="cas">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Contenido Principal - Formulario Editable -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Información General -->
                    <x-card title="📋 Información General">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        El <strong>título actual</strong> es: <span class="font-semibold">{{ $jobProfile->title }}</span>
                                    </p>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Se regenerará automáticamente si cambias el nombre del puesto o la unidad organizacional.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-form.input
                                    type="text"
                                    name="profile_name"
                                    label="Denominación del Puesto"
                                    :value="old('profile_name', $jobProfile->profile_name)"
                                    required
                                    placeholder="Ej: ESPECIALISTA EN RECURSOS HUMANOS"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Este nombre se combinará con la unidad organizacional para formar el título completo del puesto
                                </p>
                            </div>

                            <!-- Código de Posición -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Código de Posición <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="position_code_id"
                                    name="position_code_id"
                                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                    onchange="autoFillFromPositionCode()">
                                    <option value="">Seleccione un código</option>
                                    @foreach($positionCodes ?? [] as $id => $name)
                                        <option value="{{ $id }}" {{ old('position_code_id', $jobProfile->position_code_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-magic"></i> Al seleccionar un código, se autocompletarán algunos requisitos
                                </p>
                                @error('position_code_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-form.select
                                    name="organizational_unit_id"
                                    label="Unidad Organizacional"
                                    :options="$organizationalUnits ?? []"
                                    :selected="old('organizational_unit_id', $jobProfile->organizational_unit_id)"
                                    required
                                    placeholder="Seleccione una unidad"
                                />
                                @error('organizational_unit_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Campo para seleccionar convocatoria -->
                            <div class="md:col-span-2">
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
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Régimen Laboral <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value="CAS - Contrato Administrativo de Servicios (D.L. 1057)"
                                    class="border-gray-300 bg-gray-100 rounded-md shadow-sm w-full cursor-not-allowed"
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

                        <div class="mt-6 space-y-4">
                            <div>
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
                        </div>
                    </x-card>

                    <!-- Información del Contrato -->
                    <x-card title="📅 Información del Contrato">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-form.input
                                    type="text"
                                    name="work_location"
                                    label="Lugar de Prestación del Servicio"
                                    :value="old('work_location', $jobProfile->work_location ?? 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO')"
                                    placeholder="Ej: MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO"
                                />
                            </div>
                        </div>
                    </x-card>

                    <!-- Requisitos Académicos -->
                    <x-card title="🎓 Requisitos Académicos">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Estos campos se autocompletarán al seleccionar un Código de Posición
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Niveles Educativos Aceptados <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-gray-500 mb-3">
                                    Seleccione uno o más niveles educativos que aceptará este puesto
                                </p>

                                <div id="education_levels_container" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <!-- Educación Técnica -->
                                    <div class="border-b border-gray-200 pb-3">
                                        <p class="text-xs font-semibold text-gray-600 mb-2">NIVEL TÉCNICO</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @php
                                                $technicalLevels = ['estudios_tecnicos', 'egresado_tecnico', 'titulo_tecnico'];
                                                $oldLevels = old('education_levels', $jobProfile->education_levels ?? []);
                                            @endphp
                                            @foreach($technicalLevels as $level)
                                                @if(isset($educationOptions[$level]))
                                                <label class="flex items-start space-x-2 cursor-pointer hover:bg-white p-2 rounded">
                                                    <input
                                                        type="checkbox"
                                                        name="education_levels[]"
                                                        value="{{ $level }}"
                                                        class="education-level-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ in_array($level, $oldLevels) ? 'checked' : '' }}>
                                                    <span class="text-sm text-gray-700">{{ $educationOptions[$level] }}</span>
                                                </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Educación Universitaria -->
                                    <div class="border-b border-gray-200 pb-3">
                                        <p class="text-xs font-semibold text-gray-600 mb-2">NIVEL UNIVERSITARIO</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @php
                                                $universityLevels = ['estudios_universitarios', 'egresado_universitario', 'bachiller', 'titulo_profesional'];
                                            @endphp
                                            @foreach($universityLevels as $level)
                                                @if(isset($educationOptions[$level]))
                                                <label class="flex items-start space-x-2 cursor-pointer hover:bg-white p-2 rounded">
                                                    <input
                                                        type="checkbox"
                                                        name="education_levels[]"
                                                        value="{{ $level }}"
                                                        class="education-level-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ in_array($level, $oldLevels) ? 'checked' : '' }}>
                                                    <span class="text-sm text-gray-700">{{ $educationOptions[$level] }}</span>
                                                </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Postgrado y Otros -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 mb-2">OTROS NIVELES</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @php
                                                $otherLevels = ['secundaria', 'postgrado'];
                                            @endphp
                                            @foreach($otherLevels as $level)
                                                @if(isset($educationOptions[$level]))
                                                <label class="flex items-start space-x-2 cursor-pointer hover:bg-white p-2 rounded">
                                                    <input
                                                        type="checkbox"
                                                        name="education_levels[]"
                                                        value="{{ $level }}"
                                                        class="education-level-checkbox mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        {{ in_array($level, $oldLevels) ? 'checked' : '' }}>
                                                    <span class="text-sm text-gray-700">{{ $educationOptions[$level] }}</span>
                                                </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <span id="education_level_indicator" class="hidden text-xs text-green-600 mt-2 block">
                                    <i class="fas fa-check-circle"></i> Autocompletado desde Código de Posición
                                </span>
                                @error('education_levels')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <x-form.input
                                type="text"
                                name="career_field"
                                label="Área de Estudios"
                                :value="old('career_field', $jobProfile->career_field)"
                                placeholder="Ej: Administración, Ingeniería, Derecho"
                            />

                            <div>
                                <label for="title_required" class="block text-sm font-medium text-gray-700 mb-1">
                                    Título Requerido
                                </label>
                                <input
                                    type="text"
                                    id="title_required"
                                    name="title_required"
                                    value="{{ old('title_required', $jobProfile->title_required) }}"
                                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                    placeholder="Ej: Licenciado en Administración">
                                <span id="title_required_indicator" class="hidden text-xs text-green-600 mt-1">
                                    <i class="fas fa-check-circle"></i> Autocompletado desde Código de Posición
                                </span>
                                @error('title_required')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center mt-6">
                                <input
                                    type="checkbox"
                                    id="colegiatura_required"
                                    name="colegiatura_required"
                                    value="1"
                                    {{ old('colegiatura_required', $jobProfile->colegiatura_required) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="colegiatura_required" class="ml-2 block text-sm text-gray-900">
                                    Colegiatura Requerida
                                </label>
                                <span id="colegiatura_indicator" class="hidden text-xs text-green-600 ml-2">
                                    <i class="fas fa-check-circle"></i> Autocompletado
                                </span>
                            </div>
                        </div>
                    </x-card>

                    <!-- Experiencia Laboral -->
                    <x-card title="💼 Experiencia Laboral">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Los años de experiencia se autocompletarán según el Código de Posición
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Experiencia General -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Experiencia General <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="general_experience_years" class="block text-xs text-gray-600 mb-1">Años</label>
                                        <input
                                            type="number"
                                            id="general_experience_years"
                                            name="general_experience_years"
                                            value="{{ old('general_experience_years', $jobProfile->general_experience_years ? floor($jobProfile->general_experience_years->toDecimal()) : 0) }}"
                                            min="0"
                                            max="50"
                                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                            placeholder="0">
                                    </div>
                                    <div>
                                        <label for="general_experience_months" class="block text-xs text-gray-600 mb-1">Meses</label>
                                        <input
                                            type="number"
                                            id="general_experience_months"
                                            name="general_experience_months"
                                            value="{{ old('general_experience_months', $jobProfile->general_experience_years ? round(($jobProfile->general_experience_years->toDecimal() - floor($jobProfile->general_experience_years->toDecimal())) * 12) : 0) }}"
                                            min="0"
                                            max="11"
                                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                            placeholder="0">
                                    </div>
                                </div>
                                <span id="general_experience_indicator" class="hidden text-xs text-green-600 mt-1 block">
                                    <i class="fas fa-check-circle"></i> Autocompletado desde Código de Posición
                                </span>
                                <p id="general_experience_preview" class="mt-1 text-xs text-gray-500 italic"></p>
                                @error('general_experience_years')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Experiencia Específica -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Experiencia Específica <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="specific_experience_years" class="block text-xs text-gray-600 mb-1">Años</label>
                                        <input
                                            type="number"
                                            id="specific_experience_years"
                                            name="specific_experience_years"
                                            value="{{ old('specific_experience_years', $jobProfile->specific_experience_years ? floor($jobProfile->specific_experience_years->toDecimal()) : 0) }}"
                                            min="0"
                                            max="50"
                                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                            placeholder="0">
                                    </div>
                                    <div>
                                        <label for="specific_experience_months" class="block text-xs text-gray-600 mb-1">Meses</label>
                                        <input
                                            type="number"
                                            id="specific_experience_months"
                                            name="specific_experience_months"
                                            value="{{ old('specific_experience_months', $jobProfile->specific_experience_years ? round(($jobProfile->specific_experience_years->toDecimal() - floor($jobProfile->specific_experience_years->toDecimal())) * 12) : 0) }}"
                                            min="0"
                                            max="11"
                                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                            placeholder="0">
                                    </div>
                                </div>
                                <span id="specific_experience_indicator" class="hidden text-xs text-green-600 mt-1 block">
                                    <i class="fas fa-check-circle"></i> Autocompletado desde Código de Posición
                                </span>
                                <p id="specific_experience_preview" class="mt-1 text-xs text-gray-500 italic"></p>
                                @error('specific_experience_years')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
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
                                placeholder="Describa la experiencia específica requerida (sector, funciones, logros esperados)">{{ old('specific_experience_description', $jobProfile->specific_experience_description) }}</textarea>
                            @error('specific_experience_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-card>

                    <!-- Capacitación y Cursos -->
                    <x-card title="📚 Capacitación y Cursos Requeridos">
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
                                        placeholder="Ej: Curso de Gestión Pública, Administración del Estado">
                                    <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                    <x-card title="🧠 Conocimientos Técnicos y Áreas de Conocimiento">
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
                                    <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                    <x-card title="⭐ Competencias Requeridas">
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
                                    <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                    <x-card title="📝 Funciones Principales del Puesto">
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
                                        placeholder="Descripción de la función principal del puesto">
                                    <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                    <x-card title="🏢 Condiciones de Trabajo">
                        <div>
                            <label for="working_conditions" class="block text-sm font-medium text-gray-700 mb-1">
                                Condiciones de Trabajo
                            </label>
                            <textarea
                                name="working_conditions"
                                id="working_conditions"
                                rows="3"
                                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                                placeholder="Describa las condiciones especiales del trabajo (horario, ubicación, modalidad presencial/remota, etc.)">{{ old('working_conditions', $jobProfile->working_conditions) }}</textarea>
                            @error('working_conditions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-card>
                </div>

                <!-- Panel de Acciones de Revisión -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Información del Solicitante -->
                    <x-card title="Información del Solicitante">
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Solicitado por</p>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ $jobProfile->requestedBy->getFullNameAttribute() ?? 'N/A' }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $jobProfile->requestedBy->email ?? '' }}</p>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de Solicitud</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $jobProfile->requested_at?->format('d/m/Y H:i') ?? $jobProfile->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </x-card>

                    <!-- Acciones de Revisión -->
                    <x-card title="Acciones de Revisión" class="border-2 border-blue-200">
                        <div class="space-y-4">
                            <!-- Guardar Cambios -->
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                                <p class="text-xs text-blue-700">
                                    <i class="fas fa-info-circle"></i> Primero guarde los cambios que haya realizado, luego puede aprobar el perfil.
                                </p>
                            </div>

                            <x-button type="submit" variant="primary" class="w-full">
                                <i class="fas fa-save mr-2"></i> Guardar Cambios
                            </x-button>

                            <!-- Separador -->
                            <div class="border-t border-gray-200 pt-4"></div>

                            <!-- Aprobar -->
                            <div>
                                <button
                                    type="button"
                                    onclick="showApproveForm()"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-green-300 shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-check-circle mr-2"></i> Aprobar Perfil
                                </button>
                            </div>

                            <!-- Solicitar Modificación -->
                            <div class="border-t border-gray-200 pt-4">
                                <button
                                    type="button"
                                    onclick="showModificationForm()"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <i class="fas fa-edit mr-2"></i> Solicitar Modificación
                                </button>
                            </div>

                            <!-- Rechazar -->
                            <div class="border-t border-gray-200 pt-4">
                                <button
                                    type="button"
                                    onclick="showRejectionForm()"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-times-circle mr-2"></i> Rechazar Perfil
                                </button>
                            </div>
                        </div>
                    </x-card>

                    <!-- Historial -->
                    @if($jobProfile->history->count() > 0)
                    <x-card title="Historial">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($jobProfile->history->take(3) as $history)
                                    <li>
                                        <div class="relative pb-6">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-2">
                                                <div>
                                                    <span class="h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white">
                                                        <i class="fas fa-clock text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 flex-col">
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-900">{{ $history->action_label }}</p>
                                                        <p class="mt-0.5 text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </x-card>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Aprobar -->
<div id="approveModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Aprobar Perfil</h3>
        <form action="{{ route('jobprofile.review.approve', $jobProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="approve_comments" class="block text-sm font-medium text-gray-700 mb-1">
                    Comentarios (opcional)
                </label>
                <textarea
                    name="comments"
                    id="approve_comments"
                    rows="3"
                    class="border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full text-sm"
                    placeholder="Comentarios adicionales..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeApproveForm()">
                    Cancelar
                </x-button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    <i class="fas fa-check-circle mr-2"></i> Aprobar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Solicitar Modificación -->
<div id="modificationModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitar Modificación</h3>
        <form action="{{ route('jobprofile.review.request-modification', $jobProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="modification_comments" class="block text-sm font-medium text-gray-700 mb-1">
                    Comentarios <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="comments"
                    id="modification_comments"
                    rows="4"
                    required
                    class="border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm w-full"
                    placeholder="Explique qué modificaciones se requieren..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeModificationForm()">
                    Cancelar
                </x-button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Rechazar -->
<div id="rejectionModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rechazar Perfil</h3>
        <form action="{{ route('jobprofile.review.reject', $jobProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Razón del Rechazo <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="reason"
                    id="rejection_reason"
                    rows="4"
                    required
                    class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm w-full"
                    placeholder="Explique por qué se rechaza este perfil..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeRejectionForm()">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="danger">
                    Confirmar Rechazo
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Datos de position codes para autocompletado
const positionCodesData = @json($positionCodesData ?? []);

// Función para autocompletar campos desde Position Code
function autoFillFromPositionCode() {
    const positionCodeId = document.getElementById('position_code_id').value;

    if (!positionCodeId || !positionCodesData[positionCodeId]) {
        hideAllIndicators();
        return;
    }

    const data = positionCodesData[positionCodeId];

    // Autocompletar Niveles Educativos
    if (data.education_levels && Array.isArray(data.education_levels)) {
        document.querySelectorAll('.education-level-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        data.education_levels.forEach(level => {
            const checkbox = document.querySelector(`.education-level-checkbox[value="${level}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        showIndicator('education_level_indicator');
    }

    // Autocompletar Título Requerido
    if (data.title_required !== null && data.title_required !== undefined) {
        const titleField = document.getElementById('title_required');
        if (data.title_required) {
            titleField.placeholder = 'Título profesional requerido (especifique)';
            showIndicator('title_required_indicator');
        }
    }

    // Autocompletar Colegiatura Requerida
    if (data.colegiatura_required !== null && data.colegiatura_required !== undefined) {
        document.getElementById('colegiatura_required').checked = data.colegiatura_required;
        if (data.colegiatura_required) {
            showIndicator('colegiatura_indicator');
        }
    }

    // Autocompletar Experiencia General
    if (data.general_experience_years !== null && data.general_experience_years !== undefined) {
        const { years, months } = decimalToYearsMonths(data.general_experience_years);
        document.getElementById('general_experience_years').value = years;
        document.getElementById('general_experience_months').value = months;
        updateExperiencePreview('general');
        showIndicator('general_experience_indicator');
    }

    // Autocompletar Experiencia Específica
    if (data.specific_experience_years !== null && data.specific_experience_years !== undefined) {
        const { years, months } = decimalToYearsMonths(data.specific_experience_years);
        document.getElementById('specific_experience_years').value = years;
        document.getElementById('specific_experience_months').value = months;
        updateExperiencePreview('specific');
        showIndicator('specific_experience_indicator');
    }

    showSuccessNotification();
}

function showIndicator(indicatorId) {
    const indicator = document.getElementById(indicatorId);
    if (indicator) {
        indicator.classList.remove('hidden');
        indicator.classList.add('inline-block');
    }
}

function hideAllIndicators() {
    const indicators = [
        'education_level_indicator',
        'title_required_indicator',
        'colegiatura_indicator',
        'general_experience_indicator',
        'specific_experience_indicator'
    ];

    indicators.forEach(id => {
        const indicator = document.getElementById(id);
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('inline-block');
        }
    });
}

function showSuccessNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Campos autocompletados desde el Código de Posición';
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

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
            placeholder="Descripción de la función principal del puesto">
        <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
            placeholder="Ej: Curso de Gestión Pública, Administración del Estado">
        <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
        <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
        <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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

// Función para convertir decimal a años y meses
function decimalToYearsMonths(decimal) {
    if (!decimal || isNaN(decimal)) {
        return { years: 0, months: 0 };
    }

    const years = Math.floor(decimal);
    const months = Math.round((decimal - years) * 12);

    return { years, months };
}

// Función para generar texto legible
function experienceToHuman(years, months) {
    const parts = [];

    if (years > 0) {
        parts.push(`${years} ${years === 1 ? 'año' : 'años'}`);
    }

    if (months > 0) {
        parts.push(`${months} ${months === 1 ? 'mes' : 'meses'}`);
    }

    if (parts.length === 0) {
        return 'Sin experiencia requerida';
    }

    return parts.join(' y ');
}

function updateExperiencePreview(type) {
    const yearsInput = document.getElementById(`${type}_experience_years`);
    const monthsInput = document.getElementById(`${type}_experience_months`);
    const preview = document.getElementById(`${type}_experience_preview`);

    const years = parseInt(yearsInput.value) || 0;
    const months = parseInt(monthsInput.value) || 0;

    preview.textContent = `Vista previa: ${experienceToHuman(years, months)}`;
}

// Event listeners para actualizar preview
['general', 'specific'].forEach(type => {
    const yearsInput = document.getElementById(`${type}_experience_years`);
    const monthsInput = document.getElementById(`${type}_experience_months`);

    if (yearsInput) {
        yearsInput.addEventListener('input', () => updateExperiencePreview(type));
    }

    if (monthsInput) {
        monthsInput.addEventListener('input', () => {
            if (parseInt(monthsInput.value) > 11) {
                monthsInput.value = 11;
            }
            updateExperiencePreview(type);
        });
    }
});

// Modal functions
function showApproveForm() {
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveForm() {
    document.getElementById('approveModal').classList.add('hidden');
}

function showModificationForm() {
    document.getElementById('modificationModal').classList.remove('hidden');
}

function closeModificationForm() {
    document.getElementById('modificationModal').classList.add('hidden');
}

function showRejectionForm() {
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeRejectionForm() {
    document.getElementById('rejectionModal').classList.add('hidden');
}

// Inicializar previews al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateExperiencePreview('general');
    updateExperiencePreview('specific');
});
</script>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.3s ease-in-out;
}
</style>
@endpush
@endsection
