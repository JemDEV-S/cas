@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Crear Perfil de Puesto</h1>
                    <p class="mt-1 text-sm text-gray-600">Complete la información del perfil de puesto CAS</p>
                    @if(request('job_posting_id') && isset($jobPosting))
                    <div class="mt-3 px-4 py-2 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800 font-medium">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Este perfil se asociará a la convocatoria: <strong>{{ $jobPosting->code }} - {{ $jobPosting->title }}</strong>
                        </p>
                    </div>
                    @endif
                </div>
                <a href="{{ request('job_posting_id') && isset($jobPosting) ? route('jobposting.show', $jobPosting->id) : route('jobprofile.index') }}">
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
            @if(request('job_posting_id'))
                <input type="hidden" name="job_posting_id" value="{{ request('job_posting_id') }}">
            @endif

            <!-- Información General -->
            <x-card title="📋 Información General">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                El <strong>título del puesto</strong> se generará automáticamente concatenando el nombre del puesto con la unidad organizacional.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-form.input
                            type="text"
                            name="profile_name"
                            label="Denominacion del Puesto"
                            :value="old('profile_name')"
                            required
                            placeholder="Ej: ESPECIALISTA EN RECURSOS HUMANOS"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Este nombre se combinará con la unidad organizacional para formar el título completo del puesto
                        </p>
                    </div>

                    <!-- Código de Posición - Mejorado con autocompletado -->
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
                                <option value="{{ $id }}" {{ old('position_code_id') == $id ? 'selected' : '' }}>
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
                            :selected="old('organizational_unit_id', $userOrganizationalUnit ?? null)"
                            required
                            placeholder="Seleccione una unidad"
                        />
                        @if($isAreaUser ?? false)
                            <p class="mt-1 text-xs text-blue-600">
                                <i class="fas fa-info-circle"></i> Puede seleccionar su unidad organizacional o cualquier unidad dependiente de ella
                            </p>
                        @endif
                        @error('organizational_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(!request('job_posting_id'))
                    <!-- Campo para seleccionar convocatoria (solo si no viene pre-seleccionada) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Convocatoria (Opcional)
                        </label>
                        <select name="job_posting_id" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full">
                            <option value="">Sin convocatoria</option>
                            @foreach(\Modules\JobPosting\Entities\JobPosting::draft()->orderBy('created_at', 'desc')->get() as $posting)
                                <option value="{{ $posting->id }}" {{ old('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                    {{ $posting->code }} - {{ $posting->title }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle"></i> Asocie este perfil a una convocatoria en borrador
                        </p>
                    </div>
                    @endif

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
                        :value="old('total_vacancies', 1)"
                        required
                        min="1"
                    />
                </div>

                <div class="mt-6 space-y-4">
                    <!-- <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción del Puesto
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                            placeholder="Descripción general del puesto y sus objetivos">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div> -->

                    <!-- <div>
                        <label for="mission" class="block text-sm font-medium text-gray-700 mb-1">
                            Misión del Puesto
                        </label>
                        <textarea
                            name="mission"
                            id="mission"
                            rows="3"
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                            placeholder="Razón de ser del puesto dentro de la organización">{{ old('mission') }}</textarea>
                        @error('mission')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div> -->

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
                                <option value="{{ $value }}" {{ old('justification') == $value ? 'selected' : '' }}>
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
                            :value="old('work_location', 'MUNICIPALIDAD DISTRITAL DE SAN JERÓNIMO')"
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
                                        $oldLevels = old('education_levels', []);
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
                        @error('education_levels.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <x-form.input
                        type="text"
                        name="career_field"
                        label="Área de Estudios"
                        :value="old('career_field')"
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
                            value="{{ old('title_required') }}"
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
                            {{ old('colegiatura_required') ? 'checked' : '' }}
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
                                    value="{{ old('general_experience_years', 0) }}"
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
                                    value="{{ old('general_experience_months', 0) }}"
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
                                    value="{{ old('specific_experience_years', 0) }}"
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
                                    value="{{ old('specific_experience_months', 0) }}"
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
                        placeholder="Describa la experiencia específica requerida (sector, funciones, logros esperados)">{{ old('specific_experience_description') }}</textarea>
                    @error('specific_experience_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>

            <!-- Capacitación y Cursos -->
            <x-card title="📚 Capacitación y Cursos Requeridos">
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
                                <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                                placeholder="Ej: Curso de Gestión Pública, Administración del Estado">
                            <button type="button" onclick="removeCourse(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
            <x-card title="🧠 Conocimientos Técnicos y Áreas de Conocimiento">
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
                                <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                            <button type="button" onclick="removeKnowledge(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
            <x-card title="⭐ Competencias Requeridas">
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
                                <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                            <button type="button" onclick="removeCompetency(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
            <x-card title="📝 Funciones Principales del Puesto">
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
                                <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                                placeholder="Descripción de la función principal del puesto">
                            <button type="button" onclick="removeFunction(this)" class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
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
                        placeholder="Describa las condiciones especiales del trabajo (horario, ubicación, modalidad presencial/remota, etc.)">{{ old('working_conditions') }}</textarea>
                    @error('working_conditions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>

            <!-- Botones de Acción -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('jobprofile.index') }}">
                    <x-button type="button" variant="secondary">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </x-button>
                </a>
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save mr-2"></i> Guardar Perfil
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
        // Ocultar todos los indicadores si no hay selección
        hideAllIndicators();
        return;
    }

    const data = positionCodesData[positionCodeId];

    // Autocompletar Niveles Educativos (checkboxes)
    if (data.education_levels && Array.isArray(data.education_levels)) {
        // Primero desmarcar todos los checkboxes
        document.querySelectorAll('.education-level-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Marcar solo los niveles educativos del position code seleccionado
        data.education_levels.forEach(level => {
            const checkbox = document.querySelector(`.education-level-checkbox[value="${level}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        showIndicator('education_level_indicator');
    }

    // Autocompletar Título Requerido (checkbox → texto)
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

    if (data.general_experience_years !== null && data.general_experience_years !== undefined) {
        const { years, months } = decimalToYearsMonths(data.general_experience_years);
        
        document.getElementById('general_experience_years').value = years;
        document.getElementById('general_experience_months').value = months;
        
        updateExperiencePreview('general');
        showIndicator('general_experience_indicator');
    }

    // ✅ AUTOCOMPLETAR EXPERIENCIA ESPECÍFICA
    if (data.specific_experience_years !== null && data.specific_experience_years !== undefined) {
        const { years, months } = decimalToYearsMonths(data.specific_experience_years);
        
        document.getElementById('specific_experience_years').value = years;
        document.getElementById('specific_experience_months').value = months;
        
        updateExperiencePreview('specific');
        showIndicator('specific_experience_indicator');
    }

    // Mostrar notificación de éxito
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
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
    notification.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Campos autocompletados desde el Código de Posición';
    document.body.appendChild(notification);

    // Remover después de 3 segundos
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
            // Validar que no exceda 11 meses
            if (parseInt(monthsInput.value) > 11) {
                monthsInput.value = 11;
            }
            updateExperiencePreview(type);
        });
    }
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
