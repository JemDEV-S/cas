@extends('applicantportal::components.layouts.master')

@section('title', 'Postular a ' . $jobProfile->profile_name)

@push('styles')
<style>
    /* Estilos personalizados para el wizard */
    .step-indicator {
        transition: all 0.3s ease;
    }
    .step-indicator.active {
        transform: scale(1.1);
    }
    .step-indicator.completed {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Ocultar elementos con x-cloak hasta que Alpine.js esté listo */
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto" x-data="applicationWizard()">

    <!-- Header con información de la convocatoria -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 gradient-municipal rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $jobProfile->profile_name }}</h1>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Convocatoria: <strong>{{ $posting->code }}</strong>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Unidad: {{ $jobProfile->requestingUnit->name ?? 'N/A' }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Remuneración: S/ {{ number_format($jobProfile->positionCode->base_salary ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Advertencia importante -->
        <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>IMPORTANTE:</strong> Este formulario es declarativo. Solo debes ingresar información, <strong>NO se requiere adjuntar documentos en esta fase</strong>. Los documentos sustentatorios serán solicitados únicamente a los postulantes declarados APTOS en la Fase 5.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de progreso -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Paso <span x-text="currentStep"></span> de 8</h3>
                <p class="text-sm text-gray-600" x-text="stepTitle"></p>
            </div>
            <div class="text-sm text-gray-500" x-show="lastSaved">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Guardado automático: <span x-text="lastSaved"></span>
                </span>
            </div>
        </div>

        <!-- Barra de progreso -->
        <div class="relative">
            <div class="overflow-hidden h-2 text-xs flex rounded-full bg-gray-200">
                <div class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center gradient-municipal transition-all duration-500"
                     :style="`width: ${(currentStep / 8) * 100}%`"></div>
            </div>
        </div>

        <!-- Steps indicators -->
        <div class="flex justify-between mt-4">
            <template x-for="step in 8" :key="step">
                <div class="flex flex-col items-center">
                    <div class="step-indicator w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                         :class="{
                             'bg-gradient-to-r from-blue-500 to-blue-600 text-white active': currentStep === step,
                             'completed text-white': currentStep > step,
                             'bg-gray-200 text-gray-500': currentStep < step
                         }">
                        <span x-show="currentStep >= step" x-text="step"></span>
                        <svg x-show="currentStep > step" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="text-xs mt-1 text-gray-500 hidden md:block" x-text="getStepName(step)"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST"
          action="{{ route('applicant.job-postings.apply.store', [$posting->id, $jobProfile->id]) }}"
          @submit.prevent="submitApplication"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        @csrf

        <!-- Paso 1: Datos Personales -->
        <div x-show="currentStep === 1" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Datos Personales</h2>

            @if(empty($user->birth_date) || empty($user->address) || empty($user->phone))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>¡Atención!</strong> Algunos de tus datos personales están incompletos.
                            Debes actualizar tu perfil de usuario antes de poder postular. Campos faltantes:
                            @if(empty($user->birth_date)) Fecha de Nacimiento @endif
                            @if(empty($user->address)){{ empty($user->birth_date) ? ', ' : '' }}Dirección @endif
                            @if(empty($user->phone)){{ (empty($user->birth_date) || empty($user->address)) ? ', ' : '' }}Teléfono @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Información:</strong> Los datos personales se cargan automáticamente de tu perfil de usuario. Los campos bloqueados no pueden ser modificados en este formulario.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo *</label>
                    <input type="text"
                           x-model="formData.personal.fullName"
                           readonly
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">DNI *</label>
                    <input type="text"
                           x-model="formData.personal.dni"
                           required
                           maxlength="8"
                           pattern="[0-9]{8}"
                           readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Nacimiento *</label>
                    <input type="date"
                           x-model="formData.personal.birthDate"
                           readonly
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección Completa *</label>
                    <input type="text"
                           x-model="formData.personal.address"
                           readonly
                           required
                           placeholder="Ej: Av. Los Incas 123, San Jerónimo, Cusco"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono / Celular *</label>
                    <input type="tel"
                           x-model="formData.personal.phone"
                           readonly
                           required
                           placeholder="Ej: 987654321 o 084-123456"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email"
                           x-model="formData.personal.email"
                           required
                           readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>
            </div>
        </div>

        <!-- Paso 2: Formación Académica -->
        <div x-show="currentStep === 2" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Formación Académica</h2>
            <p class="text-gray-600 mb-6">Declara tu formación académica. Los documentos sustentatorios serán solicitados en la Fase 5.</p>

            {{-- Información de requisitos académicos --}}
            <div class="alert bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <h5 class="font-bold text-blue-900 mb-3"><i class="fas fa-graduation-cap"></i> Requisitos Académicos del Puesto</h5>

                {{-- Nivel educativo requerido --}}
                @if(!empty($jobProfile->education_levels))
                    <div class="mb-3">
                        <p class="text-sm text-blue-800 font-semibold mb-1">Nivel Educativo Requerido:</p>
                        <p class="text-sm text-blue-700">
                            <strong>{{ \Modules\JobProfile\Enums\EducationLevelEnum::formatMultiple($jobProfile->education_levels) }}</strong>
                        </p>
                    </div>
                @elseif($jobProfile->education_level)
                    <div class="mb-3">
                        <p class="text-sm text-blue-800 font-semibold mb-1">Nivel Educativo Requerido:</p>
                        <p class="text-sm text-blue-700">
                            <strong>{{ $jobProfile->education_level }}</strong>
                        </p>
                    </div>
                @endif

                {{-- Carreras profesionales requeridas --}}
                @if(!empty($acceptedCareerNames))
                    <div>
                        <p class="text-sm text-blue-800 font-semibold mb-1">Carreras Profesionales Requeridas:</p>
                        <p class="text-sm text-blue-700">
                            <strong>{{ implode(' • ', $acceptedCareerNames) }}</strong>
                        </p>
                        @if(count($acceptedCareerNames) > 1)
                            <small class="text-xs text-blue-600 mt-1 block">
                                ✓ Se aceptan carreras equivalentes según normativa vigente
                            </small>
                        @endif
                        <small class="text-xs text-blue-600 mt-1 block">
                            ℹ️ Si tu carrera es afín pero no aparece en la lista, puedes indicarlo marcando la opción correspondiente
                        </small>
                    </div>
                @elseif($jobProfile->career_field)
                    <div>
                        <p class="text-sm text-blue-800 font-semibold mb-1">Campo de Carrera:</p>
                        <p class="text-sm text-blue-700">
                            <strong>{{ $jobProfile->career_field }}</strong>
                        </p>
                    </div>
                @endif
            </div>

            <template x-for="(academic, index) in formData.academics" :key="index">
                <div class="border border-gray-200 rounded-xl p-6 mb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-gray-900">Título/Grado <span x-text="index + 1"></span></h3>
                        <button type="button"
                                @click="removeAcademic(index)"
                                x-show="formData.academics.length > 1"
                                class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Grado Académico *
                                <button type="button"
                                    class="ml-1 text-blue-500 hover:text-blue-700"
                                    title="Información sobre niveles educativos"
                                    @click="showEducationHelp = !showEducationHelp">
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </label>

                            {{-- Ayuda contextual --}}
                            <div x-show="showEducationHelp"
                                x-cloak
                                class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                                <p class="font-semibold mb-1">💡 Guía de Niveles Educativos:</p>
                                <ul class="space-y-1 ml-3">
                                    <li><strong>Egresado:</strong> Has completado los estudios pero no tienes título</li>
                                    <li><strong>Bachiller:</strong> Grado académico universitario previo al título</li>
                                    <li><strong>Título:</strong> Has obtenido el título profesional o técnico</li>
                                    <li><strong>Postgrado:</strong> Maestría, Doctorado o Especialización</li>
                                </ul>
                                <p class="mt-2 text-blue-700">
                                    <i class="fas fa-file-alt"></i> Los documentos sustentatorios serán solicitados en la Fase 5
                                </p>
                            </div>

                            <select x-model="academic.degreeType"
                                    @change="checkEducationLevel(index); autoSave()"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccione...</option>
                                @foreach($educationLevels as $level)
                                    <option value="{{ $level['value'] }}"
                                        data-level="{{ $level['level'] }}"
                                        title="{{ $level['description'] }}">
                                        {{ $level['label'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Validación de nivel educativo mínimo --}}
                            @if($minimumEducationLevel)
                                <div x-show="academic.degreeType && !meetsEducationRequirement(academic.degreeType)"
                                    x-cloak
                                    class="mt-2 p-2 bg-yellow-50 border border-yellow-300 rounded">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                    <span class="text-yellow-800 text-sm">
                                        El nivel seleccionado no cumple con el requisito mínimo: <strong>{{ $minimumEducationLevel->label() }}</strong>
                                    </span>
                                </div>

                                <div x-show="academic.degreeType && meetsEducationRequirement(academic.degreeType)"
                                    x-cloak
                                    class="mt-2 p-2 bg-green-50 border border-green-300 rounded">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                    <span class="text-green-800 text-sm">
                                        ✓ Cumple con el requisito de nivel educativo
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Institución Educativa *</label>
                            <input type="text"
                                   x-model="academic.institution"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Carrera Profesional *</label>
                            <select
                                :name="`academics[${index}][careerId]`"
                                x-model="academic.careerId"
                                @change="checkCareerMatch(index); autoSave()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="">Seleccione una carrera</option>
                                @foreach($academicCareers as $categoryGroup => $careers)
                                    <optgroup label="{{ $categoryGroup }}">
                                        @foreach($careers as $career)
                                            <option
                                                value="{{ $career->id }}"
                                                @if(in_array($career->id, $acceptedCareerIds)) data-accepted="true" @endif
                                            >
                                                {{ $career->name }}
                                                @if(in_array($career->id, $acceptedCareerIds))
                                                    ✓
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>

                            {{-- Advertencia si no coincide con requisito --}}
                            <div
                                x-show="academic.careerId && !isCareerAccepted(academic.careerId)"
                                class="mt-2 p-2 bg-yellow-50 border border-yellow-300 rounded"
                                style="display: none;"
                            >
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                <span class="text-yellow-800 text-sm">
                                    La carrera seleccionada no coincide con el requisito del perfil.
                                    Puedes postular, pero es probable que seas declarado NO APTO.
                                </span>
                            </div>

                            {{-- Indicador de match --}}
                            <div
                                x-show="academic.careerId && isCareerAccepted(academic.careerId)"
                                class="mt-2 p-2 bg-green-50 border border-green-300 rounded"
                                style="display: none;"
                            >
                                <i class="fas fa-check-circle text-green-600"></i>
                                <span class="text-green-800 text-sm">
                                    ✓ Cumple con el requisito de carrera profesional
                                </span>
                            </div>

                            {{-- Opción destacada: Carrera no está en la lista --}}
                            <div class="mt-3 p-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 transition-colors">
                                <label class="flex items-start cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        x-model="academic.isRelatedCareer"
                                        @change="autoSave"
                                        class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="ml-3 flex-1">
                                        <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-600">
                                            ¿Tu carrera no está en la lista?
                                        </span>
                                        <span class="block text-xs text-gray-600 mt-1">
                                            Si tu carrera profesional es afín al puesto pero no aparece en el catálogo, márcala aquí e indica su nombre.
                                        </span>
                                    </span>
                                </label>

                                {{-- Input de carrera afín (se muestra solo si marca el checkbox) --}}
                                <div x-show="academic.isRelatedCareer"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    class="mt-3 pl-7">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nombre de tu Carrera *
                                    </label>
                                    <input
                                        type="text"
                                        x-model="academic.relatedCareerName"
                                        @input="autoSave"
                                        :required="academic.isRelatedCareer"
                                        placeholder="Ej: Ingeniería de Software, Administración de Empresas, etc."
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    >
                                    <div class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded text-xs text-amber-700">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Nota:</strong> El comité evaluará si tu carrera es afín al puesto. Los documentos sustentatorios serán solicitados en la Fase 5.
                                    </div>
                                </div>
                            </div>

                            {{-- Campo legacy (mantener por compatibilidad) --}}
                            <input type="hidden" x-model="academic.careerField">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Año de Graduación *</label>
                            <input type="number"
                                   x-model="academic.year"
                                   @input="autoSave"
                                   required
                                   min="1950"
                                   :max="new Date().getFullYear()"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addAcademic"
                    class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar otro título/grado
            </button>
        </div>

        <!-- Paso 3: Experiencia Laboral -->
        <div x-show="currentStep === 3" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Experiencia Laboral</h2>
            <p class="text-gray-600 mb-6">Declara tu experiencia laboral. El sistema calculará automáticamente la duración exacta en años, meses y días.</p>

            <!-- Explicación importante -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-800">
                            <strong>¡Importante!</strong> Marca correctamente cada experiencia:
                        </p>
                        <ul class="mt-2 text-sm text-amber-700 list-disc list-inside space-y-1">
                            <li><strong>Experiencia General:</strong> Toda tu experiencia laboral cuenta automáticamente</li>
                            <li><strong>Experiencia Específica:</strong> Solo marca el checkbox si las funciones están <strong>directamente relacionadas</strong> con el puesto al que postulas</li>
                            <li><strong>Sector Público:</strong> Marca si trabajaste en instituciones del Estado</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Requisitos del perfil con semáforo -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl p-5 mb-6 shadow-sm">
                <h3 class="font-bold text-blue-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                    Requisitos del Perfil
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex items-start">
                        <span class="text-2xl mr-2">📋</span>
                        <div>
                            <p class="font-semibold text-blue-900">Experiencia General Requerida:</p>
                            <p class="text-lg font-bold text-blue-700">
                                @if($jobProfile->general_experience_years)
                                    {{ is_object($jobProfile->general_experience_years) ? $jobProfile->general_experience_years->toHuman() : $jobProfile->general_experience_years . ' años' }}
                                @else
                                    No requerida
                                @endif
                                (mínimo)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="text-2xl mr-2">🎯</span>
                        <div>
                            <p class="font-semibold text-purple-900">Experiencia Específica Requerida:</p>
                            <p class="text-lg font-bold text-purple-700">
                                @if($jobProfile->specific_experience_years)
                                    {{ is_object($jobProfile->specific_experience_years) ? $jobProfile->specific_experience_years->toHuman() : $jobProfile->specific_experience_years . ' años' }}
                                @else
                                    No requerida
                                @endif
                                (mínimo)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totales calculados con semáforo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="relative overflow-hidden bg-gradient-to-br from-green-50 to-green-100 border-2 rounded-xl p-5 shadow-sm"
                     :class="{
                         'border-green-500': checkExperienceRequirement('general'),
                         'border-yellow-500': !checkExperienceRequirement('general')
                     }">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-green-700 font-semibold mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                                Experiencia General Total
                            </p>
                            <p class="text-3xl font-bold text-green-900" x-text="calculateTotalExperience('general')"></p>
                        </div>
                        <div class="ml-3">
                            <span x-show="checkExperienceRequirement('general')" class="text-3xl">✅</span>
                            <span x-show="!checkExperienceRequirement('general')" class="text-3xl">⚠️</span>
                        </div>
                    </div>
                    <p class="text-xs mt-2"
                       :class="checkExperienceRequirement('general') ? 'text-green-600' : 'text-yellow-600'">
                        <span x-show="checkExperienceRequirement('general')">✓ Cumples el requisito</span>
                        <span x-show="!checkExperienceRequirement('general')">⚠ No cumples el requisito mínimo</span>
                    </p>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100 border-2 rounded-xl p-5 shadow-sm"
                     :class="{
                         'border-purple-500': checkExperienceRequirement('specific'),
                         'border-yellow-500': !checkExperienceRequirement('specific')
                     }">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm text-purple-700 font-semibold mb-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Experiencia Específica Total
                            </p>
                            <p class="text-3xl font-bold text-purple-900" x-text="calculateTotalExperience('specific')"></p>
                        </div>
                        <div class="ml-3">
                            <span x-show="checkExperienceRequirement('specific')" class="text-3xl">✅</span>
                            <span x-show="!checkExperienceRequirement('specific')" class="text-3xl">⚠️</span>
                        </div>
                    </div>
                    <p class="text-xs mt-2"
                       :class="checkExperienceRequirement('specific') ? 'text-purple-600' : 'text-yellow-600'">
                        <span x-show="checkExperienceRequirement('specific')">✓ Cumples el requisito</span>
                        <span x-show="!checkExperienceRequirement('specific')">⚠ No cumples el requisito mínimo</span>
                    </p>
                </div>
            </div>

            <template x-for="(experience, index) in formData.experiences" :key="index">
                <div class="border border-gray-200 rounded-xl p-6 mb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-gray-900">Experiencia <span x-text="index + 1"></span></h3>
                        <button type="button"
                                @click="removeExperience(index)"
                                x-show="formData.experiences.length > 1"
                                class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Empresa/Organización *</label>
                            <input type="text"
                                   x-model="experience.organization"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cargo/Puesto *</label>
                            <input type="text"
                                   x-model="experience.position"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Inicio *</label>
                            <input type="date"
                                   x-model="experience.startDate"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Fin</label>
                            <input type="date"
                                   x-model="experience.endDate"
                                   @input="autoSave"
                                   :disabled="experience.isCurrent"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <label class="flex items-center mt-2">
                                <input type="checkbox"
                                       x-model="experience.isCurrent"
                                       @change="autoSave"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Trabajo actual</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Tipo de Experiencia (marca lo que corresponda):</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="experience.isPublicSector ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'">
                                    <input type="checkbox"
                                           x-model="experience.isPublicSector"
                                           @change="autoSave"
                                           class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <span class="text-sm font-semibold text-gray-900 flex items-center">
                                            🏛️ Sector Público
                                        </span>
                                        <span class="text-xs text-gray-600">Experiencia en instituciones del Estado</span>
                                    </div>
                                </label>

                                <label class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="experience.isSpecific ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300'">
                                    <input type="checkbox"
                                           x-model="experience.isSpecific"
                                           @change="autoSave"
                                           class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <div class="ml-3">
                                        <span class="text-sm font-semibold text-gray-900 flex items-center">
                                            🎯 Experiencia Específica
                                        </span>
                                        <span class="text-xs text-gray-600">Funciones relacionadas al puesto</span>
                                    </div>
                                </label>
                            </div>
                            <p class="text-xs text-amber-600 mt-2" x-show="experience.isSpecific">
                                ⚠️ Solo marca como específica si las funciones que realizaste están <strong>directamente relacionadas</strong> con el puesto al que postulas
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción de Funciones</label>
                            <textarea x-model="experience.description"
                                      @input="autoSave"
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <!-- Duración calculada -->
                        <div class="md:col-span-2 bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">
                                <strong>Duración calculada:</strong>
                                <span x-text="calculateDuration(experience.startDate, experience.endDate || (experience.isCurrent ? new Date().toISOString().slice(0,7) : ''))"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addExperience"
                    class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar otra experiencia
            </button>
        </div>

        <!-- Paso 4: Capacitaciones y Cursos -->
        <div x-show="currentStep === 4" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Capacitaciones y Cursos</h2>
            <p class="text-gray-600 mb-6">Marca las capacitaciones que cumples y declara información adicional. Los certificados serán solicitados en la Fase 5.</p>

            {{-- Listado de capacitaciones requeridas --}}
            @if(!empty($jobProfile->required_courses) && count($jobProfile->required_courses) > 0)
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-300 p-5 mb-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="font-bold text-blue-900 flex items-center">
                            <i class="fas fa-certificate mr-2"></i> Capacitaciones Requeridas
                        </h5>
                        <div class="text-sm font-semibold text-blue-700 bg-white px-3 py-1 rounded-full">
                            <span x-text="getRequiredCoursesCompliance().met"></span> de {{ count($jobProfile->required_courses) }} cumplidas
                        </div>
                    </div>

                    <p class="text-sm text-blue-800 mb-4">
                        <i class="fas fa-info-circle"></i>
                        Indica si posees cada capacitación requerida. Si tienes una similar con otro nombre, márcala como "afín".
                    </p>

                    <div class="space-y-4">
                        @foreach($jobProfile->required_courses as $index => $course)
                            <div class="bg-white p-5 rounded-xl border-2 border-gray-200 shadow-sm">
                                <div class="mb-3">
                                    <span class="font-semibold text-gray-900 text-base">📋 {{ $course }}</span>
                                </div>

                                {{-- Radio buttons: 3 opciones mutuamente excluyentes --}}
                                <div class="space-y-3">
                                    {{-- Opción 1: Tengo esta capacitación exacta --}}
                                    <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-green-50 transition-colors"
                                        :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact' ? 'bg-green-50 border-2 border-green-300' : 'border-2 border-transparent'">
                                        <input
                                            type="radio"
                                            name="course_status_{{ $index }}"
                                            value="exact"
                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                            @change="autoSave"
                                            class="mt-0.5 text-green-600 focus:ring-green-500"
                                        >
                                        <div class="ml-3 flex-1">
                                            <span class="font-medium text-gray-800">✅ Tengo esta capacitación exacta</span>

                                            {{-- Campos para capacitación exacta --}}
                                            <div x-show="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform scale-95"
                                                x-transition:enter-end="opacity-100 transform scale-100"
                                                class="mt-3 space-y-3 pl-2 border-l-2 border-green-300">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Institución *</label>
                                                        <input
                                                            type="text"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].institution"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                            placeholder="Nombre de la institución"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Año *</label>
                                                        <input
                                                            type="number"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].year"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                            placeholder="2020"
                                                            min="1990"
                                                            :max="new Date().getFullYear()"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Horas *</label>
                                                        <input
                                                            type="number"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].hours"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'exact'"
                                                            placeholder="40"
                                                            min="1"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- Opción 2: Tengo una capacitación afín --}}
                                    <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-amber-50 transition-colors"
                                        :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'related' ? 'bg-amber-50 border-2 border-amber-300' : 'border-2 border-transparent'">
                                        <input
                                            type="radio"
                                            name="course_status_{{ $index }}"
                                            value="related"
                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                            @change="autoSave"
                                            class="mt-0.5 text-amber-600 focus:ring-amber-500"
                                        >
                                        <div class="ml-3 flex-1">
                                            <div>
                                                <span class="font-medium text-gray-800">⚠️ Tengo una capacitación similar con otro nombre</span>
                                                <button type="button"
                                                    class="ml-1 text-amber-600 hover:text-amber-800"
                                                    title="Una capacitación es afín cuando el contenido es similar aunque el nombre sea diferente. Ejemplo: Si requieren 'Excel Avanzado' y tienes 'Hojas de Cálculo Avanzadas'"
                                                    @click="$event.stopPropagation()">
                                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Campos para capacitación afín --}}
                                            <div x-show="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                x-cloak
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform scale-95"
                                                x-transition:enter-end="opacity-100 transform scale-100"
                                                class="mt-3 space-y-3 pl-2 border-l-2 border-amber-300">
                                                <div>
                                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre de tu capacitación *</label>
                                                    <input
                                                        type="text"
                                                        x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedCourseName"
                                                        @input="autoSave"
                                                        :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                        placeholder="Ej: Hojas de Cálculo Avanzadas"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                    >
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Institución *</label>
                                                        <input
                                                            type="text"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedInstitution"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                            placeholder="Nombre de la institución"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Año *</label>
                                                        <input
                                                            type="number"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedYear"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                            placeholder="2020"
                                                            min="1990"
                                                            :max="new Date().getFullYear()"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                        >
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Horas *</label>
                                                        <input
                                                            type="number"
                                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].relatedHours"
                                                            @input="autoSave"
                                                            :required="formData.requiredCoursesCompliance[{{ $index }}].status === 'related'"
                                                            placeholder="40"
                                                            min="1"
                                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="bg-amber-100 border border-amber-300 rounded-lg p-2">
                                                    <p class="text-xs text-amber-800">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Nota:</strong> El comité evaluará si tu capacitación es afín a la requerida. Deberás presentar el certificado en la Fase 5.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    {{-- Opción 3: No tengo esta capacitación --}}
                                    <label class="flex items-start cursor-pointer p-3 rounded-lg hover:bg-gray-50 transition-colors"
                                        :class="formData.requiredCoursesCompliance[{{ $index }}].status === 'none' ? 'bg-gray-50 border-2 border-gray-300' : 'border-2 border-transparent'">
                                        <input
                                            type="radio"
                                            name="course_status_{{ $index }}"
                                            value="none"
                                            x-model="formData.requiredCoursesCompliance[{{ $index }}].status"
                                            @change="autoSave"
                                            class="mt-0.5 text-gray-600 focus:ring-gray-500"
                                        >
                                        <div class="ml-3">
                                            <span class="font-medium text-gray-700">❌ No tengo esta capacitación</span>
                                            <p class="text-xs text-gray-500 mt-1">Puedes postular igual, pero es probable que seas declarado NO APTO en esta evaluación.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Separador visual --}}
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t-2 border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-medium">Capacitaciones Adicionales</span>
                </div>
            </div>

            {{-- Capacitaciones adicionales (campo libre) --}}
            <div class="mb-6 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-5">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 mr-3 mt-0.5">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h5 class="font-semibold text-gray-900 mb-1">
                            Capacitaciones Adicionales <span class="text-sm font-normal text-gray-500">(Opcional)</span>
                        </h5>
                        <p class="text-sm text-gray-600">
                            Si tienes otras capacitaciones que consideras relevantes para el puesto y que no están listadas arriba, agrégalas aquí.
                        </p>
                    </div>
                </div>
            </div>

            <template x-for="(training, index) in formData.additionalTrainings" :key="index">
                <div class="border border-gray-300 rounded-xl p-6 mb-4 bg-white">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-gray-900">Capacitación <span x-text="index + 1"></span></h3>
                        <button type="button"
                                @click="removeAdditionalTraining(index)"
                                class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nombre del Curso/Capacitación
                                <span class="text-red-500" x-show="training.institution || training.hours || training.certificationDate">*</span>
                            </label>
                            <input type="text"
                                   x-model="training.courseName"
                                   @input="autoSave"
                                   placeholder="Ej: Gestión de Proyectos con MS Project"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Institución que dictó
                                <span class="text-red-500" x-show="training.courseName || training.hours || training.certificationDate">*</span>
                            </label>
                            <input type="text"
                                   x-model="training.institution"
                                   @input="autoSave"
                                   placeholder="Ej: Universidad Nacional, SENATI, etc."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Número de Horas
                                <span class="text-red-500" x-show="training.courseName || training.institution || training.certificationDate">*</span>
                            </label>
                            <input type="number"
                                   x-model="training.hours"
                                   @input="autoSave"
                                   placeholder="Ej: 40"
                                   min="1"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Mes/Año de Certificación
                                <span class="text-red-500" x-show="training.courseName || training.institution || training.hours">*</span>
                            </label>
                            <input type="month"
                                   x-model="training.certificationDate"
                                   @input="autoSave"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Advertencia si los campos están incompletos -->
                    <div x-show="(training.courseName || training.institution || training.hours || training.certificationDate) &&
                                  (!training.courseName || !training.institution || !training.hours || !training.certificationDate)"
                         class="mt-3 p-2 bg-yellow-50 border border-yellow-300 rounded text-xs text-yellow-800">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Si completas algún campo, debes completar todos los campos de esta capacitación.
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addAdditionalTraining"
                    class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar otra capacitación adicional
            </button>
        </div>

        <!-- Paso 5: Conocimientos Técnicos -->
        <div x-show="currentStep === 5" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Conocimientos Técnicos</h2>
            <p class="text-gray-600 mb-6">Marca los conocimientos técnicos que posees.</p>

            @if($jobProfile->knowledge_areas && count($jobProfile->knowledge_areas) > 0)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                    <h5 class="font-bold text-blue-900 mb-3"><i class="fas fa-laptop-code"></i> Conocimientos Técnicos Requeridos</h5>
                    <div class="space-y-3">
                        @foreach($jobProfile->knowledge_areas as $index => $area)
                            <div class="bg-white p-4 rounded-lg border border-blue-200">
                                <label class="flex items-start cursor-pointer">
                                    <input
                                        type="checkbox"
                                        x-model="formData.knowledgeCompliance[{{ $index }}].hasIt"
                                        @change="autoSave"
                                        class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    <span class="ml-3 font-semibold text-gray-900">{{ $area }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                    <p class="text-gray-600">No se especificaron conocimientos técnicos para este perfil.</p>
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Otros Conocimientos (opcional)</label>
                <textarea x-model="formData.otherKnowledge"
                          @input="autoSave"
                          rows="4"
                          placeholder="Describe otros conocimientos relevantes que posees..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
        </div>

        <!-- Paso 6: Registros Profesionales -->
        <div x-show="currentStep === 6" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Registros Profesionales</h2>
            <p class="text-gray-600 mb-6">Completa según aplique a tu profesión y al perfil solicitado.</p>

            <div class="space-y-6">
                <!-- Colegiatura y Habilitación Profesional -->
                @if($jobProfile->colegiatura_required)
                    <div class="border border-gray-200 rounded-xl p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Colegiatura y Habilitación Profesional *</h3>

                        <!-- Checkbox de Habilitación -->
                        <div class="mb-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox"
                                       x-model="formData.registrations.colegiatura.habilitado"
                                       @change="autoSave"
                                       class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="font-semibold text-gray-900">Cuento con habilitación profesional vigente</span>
                                    <p class="text-sm text-gray-600 mt-1">Declaro que me encuentro colegiado(a) y habilitado(a) para ejercer la profesión</p>
                                </div>
                            </label>
                        </div>

                        <!-- Campos de Colegiatura (se habilitan con el checkbox) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
                             :class="{ 'opacity-50': !formData.registrations.colegiatura.habilitado }">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Colegio Profesional</label>
                                <input type="text"
                                       x-model="formData.registrations.colegiatura.college"
                                       @input="autoSave"
                                       :disabled="!formData.registrations.colegiatura.habilitado"
                                       :required="formData.registrations.colegiatura.habilitado"
                                       placeholder="Ej: Colegio de Ingenieros del Perú"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Colegiatura</label>
                                <input type="text"
                                       x-model="formData.registrations.colegiatura.number"
                                       @input="autoSave"
                                       :disabled="!formData.registrations.colegiatura.habilitado"
                                       :required="formData.registrations.colegiatura.habilitado"
                                       placeholder="Ej: 123456"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            </div>
                        </div>

                        <!-- Mensaje informativo cuando no está habilitado -->
                        <div x-show="!formData.registrations.colegiatura.habilitado" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-700">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Este perfil requiere contar con colegiatura y habilitación profesional vigente. Si no la tienes, tu postulación podría no ser considerada.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- OSCE -->
                <div class="border border-gray-200 rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Certificación OSCE</h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Certificación</label>
                        <input type="text"
                               x-model="formData.registrations.osce"
                               @input="autoSave"
                               placeholder="Si aplica"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Licencia de Conducir -->
                <div class="border border-gray-200 rounded-xl p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Licencia de Conducir</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Licencia</label>
                            <input type="text"
                                   x-model="formData.registrations.license.number"
                                   @input="autoSave"
                                   placeholder="Si posees"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
                            <select x-model="formData.registrations.license.category"
                                    @change="autoSave"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccione...</option>
                                <option value="A-I">A-I (Moto menor 150cc)</option>
                                <option value="A-IIa">A-IIa (Moto 150-250cc)</option>
                                <option value="A-IIb">A-IIb (Moto mayor 250cc)</option>
                                <option value="A-IIIa">A-IIIa (Auto menor 2000kg)</option>
                                <option value="A-IIIb">A-IIIb (Auto mayor 2000kg)</option>
                                <option value="A-IIIc">A-IIIc (Transporte personal)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Vigencia</label>
                            <input type="date"
                                   x-model="formData.registrations.license.expiryDate"
                                   @input="autoSave"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 7: Condiciones Especiales (Bonificaciones) -->
        <div x-show="currentStep === 7" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Condiciones Especiales</h2>
            <p class="text-gray-600 mb-6">Marca si cumples con alguna de estas condiciones. Los documentos sustentatorios serán solicitados en la Fase 5.</p>

            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded">
                <p class="text-sm text-yellow-700">
                    <strong>Nota:</strong> Las bonificaciones se aplicarán según el Decreto Legislativo vigente. Solo marca las condiciones que puedas sustentar con documentos oficiales.
                </p>
            </div>

            <div class="space-y-4">
                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
                    <input type="checkbox"
                           x-model="formData.specialConditions.disability"
                           @change="autoSave"
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="font-semibold text-gray-900">Persona con Discapacidad</span>
                        <p class="text-sm text-gray-600 mt-1">Bonificación del 15% sobre el puntaje final (Ley N° 29973)</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
                    <input type="checkbox"
                           x-model="formData.specialConditions.veteran"
                           @change="autoSave"
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="font-semibold text-gray-900">Licenciado de las Fuerzas Armadas</span>
                        <p class="text-sm text-gray-600 mt-1">Bonificación del 10% sobre el puntaje final (Ley N° 29248)</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
                    <input type="checkbox"
                           x-model="formData.specialConditions.athlete"
                           @change="autoSave"
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="font-semibold text-gray-900">Deportista Destacado</span>
                        <p class="text-sm text-gray-600 mt-1">Bonificación del 5% sobre el puntaje final (Ley N° 28036)</p>
                    </div>
                </label>

                <label class="flex items-start p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 cursor-pointer transition-colors">
                    <input type="checkbox"
                           x-model="formData.specialConditions.qualifiedAthlete"
                           @change="autoSave"
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div class="ml-3">
                        <span class="font-semibold text-gray-900">Deportista Calificado</span>
                        <p class="text-sm text-gray-600 mt-1">Bonificación del 3% sobre el puntaje final (Ley N° 28036)</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Paso 8: Revisión y Confirmación -->
        <div x-show="currentStep === 8" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Revisión y Confirmación</h2>
            <p class="text-gray-600 mb-6">Revisa cuidadosamente toda la información antes de enviar tu postulación.</p>

            <!-- Semáforo de cumplimiento -->
            <div class="mb-6 p-6 rounded-xl"
                 :class="{
                     'bg-green-50 border-2 border-green-500': complianceStatus === 'full',
                     'bg-yellow-50 border-2 border-yellow-500': complianceStatus === 'partial',
                     'bg-red-50 border-2 border-red-500': complianceStatus === 'none'
                 }">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg x-show="complianceStatus === 'full'" class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="complianceStatus === 'partial'" class="w-12 h-12 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="complianceStatus === 'none'" class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2"
                            :class="{
                                'text-green-900': complianceStatus === 'full',
                                'text-yellow-900': complianceStatus === 'partial',
                                'text-red-900': complianceStatus === 'none'
                            }">
                            <span x-show="complianceStatus === 'full'">✓ Cumples todos los requisitos del perfil</span>
                            <span x-show="complianceStatus === 'partial'">⚠ Cumples parcialmente los requisitos</span>
                            <span x-show="complianceStatus === 'none'">✗ No cumples los requisitos mínimos</span>
                        </h3>
                        <p class="text-sm"
                           :class="{
                               'text-green-700': complianceStatus === 'full',
                               'text-yellow-700': complianceStatus === 'partial',
                               'text-red-700': complianceStatus === 'none'
                           }">
                            <span x-show="complianceStatus === 'full'">Excelente. Tu perfil se ajusta a los requisitos solicitados.</span>
                            <span x-show="complianceStatus === 'partial'">Puedes postular, pero hay requisitos que podrían no cumplirse completamente.</span>
                            <span x-show="complianceStatus === 'none'">Puedes postular de todos modos, pero es probable que seas declarado NO APTO en la evaluación automática.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Resumen de datos -->
            <div class="space-y-4 mb-6">
                <!-- Datos Personales -->
                <div class="border-2 border-gray-200 rounded-xl p-5 bg-white hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Datos Personales
                        </h4>
                        <button type="button" @click="currentStep = 1" class="text-blue-600 text-sm hover:underline">Editar</button>
                    </div>
                    <p class="text-sm text-gray-600" x-text="`${formData.personal.fullName} - DNI: ${formData.personal.dni}`"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="`Email: ${formData.personal.email} | Tel: ${formData.personal.phone}`"></p>
                </div>

                <!-- Formación Académica -->
                <div class="border-2 border-gray-200 rounded-xl p-5 bg-white hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                            Formación Académica
                        </h4>
                        <button type="button" @click="currentStep = 2" class="text-blue-600 text-sm hover:underline">Editar</button>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p x-text="`${formData.academics.length} título(s)/grado(s) declarado(s)`"></p>
                        <template x-if="formData.academics.some(a => a.isRelatedCareer)">
                            <p class="text-xs text-amber-600 mt-1">
                                <i class="fas fa-exclamation-triangle"></i> Incluye carrera(s) afín(es) sujeta(s) a evaluación
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Experiencia Laboral -->
                <div class="border-2 border-gray-200 rounded-xl p-5 bg-white hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                            </svg>
                            Experiencia Laboral
                        </h4>
                        <button type="button" @click="currentStep = 3" class="text-blue-600 text-sm hover:underline">Editar</button>
                    </div>
                    <div class="text-sm text-gray-600">
                        <p x-text="`${formData.experiences.length} experiencia(s) declarada(s)`"></p>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="font-semibold">General:</span>
                                <span x-text="calculateTotalExperience('general')"></span>
                            </div>
                            <div class="bg-gray-50 p-2 rounded">
                                <span class="font-semibold">Específica:</span>
                                <span x-text="calculateTotalExperience('specific')"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacitaciones -->
                <div class="border-2 border-gray-200 rounded-xl p-5 bg-white hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-bold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Capacitaciones
                        </h4>
                        <button type="button" @click="currentStep = 4" class="text-blue-600 text-sm hover:underline">Editar</button>
                    </div>
                    <div class="text-sm text-gray-600">
                        <div class="flex items-center gap-2 mb-1">
                            <span>Requeridas:</span>
                            <span class="font-semibold"
                                  :class="{
                                      'text-green-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact').length === formData.requiredCoursesCompliance.length,
                                      'text-amber-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact').length < formData.requiredCoursesCompliance.length && formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length > 0,
                                      'text-red-600': formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length === 0
                                  }"
                                  x-text="`${formData.requiredCoursesCompliance.filter(c => c.status === 'exact' || c.status === 'related').length} de ${formData.requiredCoursesCompliance.length}`">
                            </span>
                        </div>
                        <p class="text-xs" x-text="`Adicionales: ${formData.additionalTrainings.length}`"></p>
                        <template x-if="formData.requiredCoursesCompliance.filter(c => c.status === 'related').length > 0">
                            <p class="text-xs text-amber-600 mt-1">
                                <i class="fas fa-info-circle"></i> Incluye capacitación(es) afín(es)
                            </p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Declaración jurada -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                <h4 class="font-bold text-gray-900 mb-4">Declaración Jurada</h4>

                <label class="flex items-start mb-4">
                    <input type="checkbox"
                           x-model="formData.declarationAccepted"
                           required
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-3 text-sm text-gray-700">
                        Declaro bajo juramento que toda la información proporcionada es verdadera y puede ser verificada mediante documentos sustentatorios en la siguiente fase del proceso. Soy consciente de que cualquier información falsa puede resultar en la descalificación inmediata del proceso de selección.

                        Asimismo, si lo declarado no se ajusta a la verdad, me sujeto a lo establecido en el artículo 438 del Código Penal, así como a las responsabilidades administrativas, civiles y/o penales que correspondan, de conformidad con el marco legal vigente.
                    </span>
                </label>

                <label class="flex items-start">
                    <input type="checkbox"
                           x-model="formData.termsAccepted"
                           required
                           class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-3 text-sm text-gray-700">
                        Acepto los términos y condiciones del proceso de selección CAS y autorizo el tratamiento de mis datos personales conforme a la Ley N° 29733 - Ley de Protección de Datos Personales.
                    </span>
                </label>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Recuerda:</strong> Esta ficha es solo un comprobante de inscripción. Los documentos sustentatorios serán solicitados en la Fase 5 (Presentación de CV documentado) únicamente a los postulantes declarados APTOS.
                </p>
            </div>
        </div>

        <!-- Botones de navegación -->
        <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
            <button type="button"
                    @click="previousStep"
                    x-show="currentStep > 1"
                    class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Anterior
            </button>

            <div class="flex gap-3 ml-auto">
                <!-- Botón Guardar Borrador (siempre visible) -->
                <button type="button"
                        @click="saveDraft"
                        x-show="currentStep === 8"
                        class="px-6 py-3 bg-yellow-500 text-white font-semibold rounded-xl hover:bg-yellow-600 transition-all">
                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"/>
                    </svg>
                    Guardar Borrador
                </button>

                <!-- Botón Siguiente / Enviar -->
                <button type="button"
                        @click="currentStep < 8 ? nextStep() : submitApplication()"
                        x-show="currentStep < 8"
                        class="px-6 py-3 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                    Siguiente
                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <button type="submit"
                        x-show="currentStep === 8"
                        :disabled="!formData.termsAccepted || !formData.declarationAccepted"
                        class="px-6 py-3 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Enviar Postulación
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function applicationWizard() {
    return {
        currentStep: 1,
        lastSaved: null,
        showEducationHelp: false,

        acceptedCareerIds: @json($acceptedCareerIds),
        educationLevels: @json($educationLevels),
        minimumEducationLevel: @json($minimumEducationLevel ? $minimumEducationLevel->value : null),
        minimumEducationLevelValue: @json($minimumEducationLevel ? $minimumEducationLevel->level() : 0),

        formData: {
            personal: {
                fullName: '{{ $user->first_name ?? "" }} {{ $user->last_name ?? "" }}'.trim(),
                dni: '{{ $user->dni ?? "" }}',
                birthDate: '{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format("Y-m-d") : "" }}',
                address: '{{ $user->address ?? "" }}',
                phone: '{{ $user->phone ?? "" }}',
                email: '{{ $user->email ?? "" }}'
            },
            academics: [{
                degreeType: '',
                institution: '',
                careerId: '', // 💎 ID de carrera del catálogo
                careerField: '', // Mantener por compatibilidad
                isRelatedCareer: false, // 💎 NUEVO: Checkbox de carrera afín
                relatedCareerName: '', // 💎 NUEVO: Nombre de la carrera afín
                year: ''
            }],
            experiences: [{
                organization: '',
                position: '',
                startDate: '',
                endDate: '',
                isCurrent: false,
                isPublicSector: false,
                isSpecific: false,
                description: ''
            }],
            // 💎 NUEVO: Cumplimiento de capacitaciones requeridas
            requiredCoursesCompliance: @json($requiredCoursesComplianceInitial),
            // Capacitaciones adicionales (las que no están en required_courses)
            additionalTrainings: [],
            // 💎 NUEVO: Cumplimiento de conocimientos técnicos (simple checklist)
            knowledgeCompliance: @json($knowledgeComplianceInitial),
            otherKnowledge: '',
            registrations: {
                colegiatura: {
                    habilitado: false,
                    college: '',
                    number: ''
                },
                osce: '',
                license: {
                    number: '',
                    category: '',
                    expiryDate: ''
                }
            },
            specialConditions: {
                disability: false,
                veteran: false,
                athlete: false,
                qualifiedAthlete: false
            },
            declarationAccepted: false,
            termsAccepted: false
        },

        init() {
            // Debug: Mostrar datos cargados del usuario (ANTES de localStorage)
            console.log('1. Datos del usuario cargados (inicial):', {
                fullName: this.formData.personal.fullName,
                dni: this.formData.personal.dni,
                birthDate: this.formData.personal.birthDate,
                address: this.formData.personal.address,
                phone: this.formData.personal.phone,
                email: this.formData.personal.email
            });

            // Cargar datos desde localStorage si existen
            this.loadFromLocalStorage();

            // Debug: Mostrar datos después de cargar localStorage
            console.log('2. Datos después de localStorage:', {
                fullName: this.formData.personal.fullName,
                dni: this.formData.personal.dni,
                birthDate: this.formData.personal.birthDate,
                address: this.formData.personal.address,
                phone: this.formData.personal.phone,
                email: this.formData.personal.email
            });

            // Función helper para limpiar localStorage (usar en consola si es necesario)
            window.clearApplicationDraft = () => {
                localStorage.removeItem('applicationDraft_{{ $jobProfile->id }}');
                console.log('✅ Borrador eliminado. Recarga la página para ver datos frescos.');
            };
            console.log('💡 Tip: Si los datos no se muestran, ejecuta clearApplicationDraft() en la consola y recarga.');

            // Auto-save cada 30 segundos
            setInterval(() => {
                this.autoSave();
            }, 30000);
        },

        getStepName(step) {
            const names = {
                1: 'Personal',
                2: 'Académica',
                3: 'Experiencia',
                4: 'Capacitación',
                5: 'Conocimientos',
                6: 'Registros',
                7: 'Bonificaciones',
                8: 'Confirmación'
            };
            return names[step] || '';
        },

        get stepTitle() {
            const titles = {
                1: 'Información personal básica',
                2: 'Títulos y grados académicos',
                3: 'Historial laboral',
                4: 'Cursos y certificaciones',
                5: 'Conocimientos técnicos',
                6: 'Colegiatura, OSCE y licencias',
                7: 'Condiciones especiales',
                8: 'Revisión final'
            };
            return titles[this.currentStep] || '';
        },

        // 💎 NUEVO: Calcula el estado de cumplimiento de requisitos dinámicamente
        get complianceStatus() {
            let criticalIssues = 0;
            let warnings = 0;

            // 1. Verificar nivel educativo
            if (this.minimumEducationLevel) {
                const hasValidDegree = this.formData.academics.some(academic => {
                    if (!academic.degreeType) return false;
                    return this.meetsEducationRequirement(academic.degreeType);
                });
                if (!hasValidDegree) criticalIssues++;
            }

            // 2. Verificar carreras profesionales
            if (this.acceptedCareerIds && this.acceptedCareerIds.length > 0) {
                const hasAcceptedCareer = this.formData.academics.some(academic => {
                    if (academic.careerId && this.isCareerAccepted(academic.careerId)) {
                        return true;
                    }
                    return false;
                });

                const hasRelatedCareer = this.formData.academics.some(academic => {
                    return academic.isRelatedCareer && academic.relatedCareerName;
                });

                if (!hasAcceptedCareer && !hasRelatedCareer) {
                    criticalIssues++;
                } else if (!hasAcceptedCareer && hasRelatedCareer) {
                    warnings++;
                }
            }

            // 3. Verificar capacitaciones requeridas
            if (this.formData.requiredCoursesCompliance && this.formData.requiredCoursesCompliance.length > 0) {
                const coursesCompliance = this.getRequiredCoursesCompliance();
                const totalRequired = coursesCompliance.total;
                const met = coursesCompliance.met;
                const partial = coursesCompliance.partial;

                if (met === 0 && partial === 0) {
                    // No cumple ninguna capacitación
                    criticalIssues++;
                } else if (met < totalRequired) {
                    // Cumple algunas pero no todas (o tiene afines)
                    warnings++;
                }
            }

            // 4. Verificar conocimientos técnicos
            if (this.formData.knowledgeCompliance && this.formData.knowledgeCompliance.length > 0) {
                const metKnowledge = this.formData.knowledgeCompliance.filter(k => k.hasIt).length;
                const totalKnowledge = this.formData.knowledgeCompliance.length;

                if (metKnowledge === 0) {
                    criticalIssues++;
                } else if (metKnowledge < totalKnowledge) {
                    warnings++;
                }
            }

            // Determinar estado final
            if (criticalIssues > 0) {
                return 'none'; // No cumple requisitos mínimos
            } else if (warnings > 0) {
                return 'partial'; // Cumple parcialmente
            } else {
                return 'full'; // Cumple todos los requisitos
            }
        },

        nextStep() {
            if (this.currentStep < 8) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        isCareerAccepted(careerId) {
            if (!careerId) return false;
            // Convertir a string para comparar (los IDs son UUIDs)
            const careerIdStr = String(careerId);
            return this.acceptedCareerIds.includes(careerIdStr);
        },

        checkCareerMatch(index) {
            const careerId = this.formData.academics[index].careerId;
            // Simplemente validar sin console.log
            if (careerId && this.isCareerAccepted(careerId)) {
                // Carrera cumple con requisito
            }
        },

        // 💎 NUEVO: Validar si el nivel educativo cumple con el requisito mínimo
        meetsEducationRequirement(degreeType) {
            if (!this.minimumEducationLevel || !degreeType) return true;

            // Buscar el nivel del degreeType seleccionado
            const selectedLevel = this.educationLevels.find(level => level.value === degreeType);

            if (!selectedLevel) return true;

            // Comparar niveles (mayor o igual que el mínimo)
            return selectedLevel.level >= this.minimumEducationLevelValue;
        },

        // 💎 NUEVO: Función llamada al cambiar el nivel educativo
        checkEducationLevel(index) {
            const degreeType = this.formData.academics[index].degreeType;
            // La validación visual se maneja automáticamente con x-show
        },

        addAcademic() {
            this.formData.academics.push({
                degreeType: '',
                institution: '',
                careerId: '',
                careerField: '',
                isRelatedCareer: false,
                relatedCareerName: '',
                year: ''
            });
        },

        removeAcademic(index) {
            this.formData.academics.splice(index, 1);
        },

        addExperience() {
            this.formData.experiences.push({
                organization: '',
                position: '',
                startDate: '',
                endDate: '',
                isCurrent: false,
                isPublicSector: false,
                isSpecific: false,
                description: ''
            });
        },

        removeExperience(index) {
            this.formData.experiences.splice(index, 1);
        },

        addAdditionalTraining() {
            this.formData.additionalTrainings.push({
                courseName: '',
                institution: '',
                hours: '',
                certificationDate: ''
            });
        },

        removeAdditionalTraining(index) {
            this.formData.additionalTrainings.splice(index, 1);
        },

        // 💎 NUEVO: Calcular cumplimiento de capacitaciones requeridas
        getRequiredCoursesCompliance() {
            if (!this.formData.requiredCoursesCompliance) {
                return { met: 0, total: 0, partial: 0 };
            }

            const total = this.formData.requiredCoursesCompliance.length;
            let met = 0;
            let partial = 0;

            this.formData.requiredCoursesCompliance.forEach(course => {
                if (course.status === 'exact') {
                    met++;
                } else if (course.status === 'related') {
                    partial++;
                }
            });

            return { met, total, partial };
        },

        calculateDuration(start, end) {
            if (!start) return '0 años, 0 meses, 0 días';

            // Parsear fechas (ahora en formato YYYY-MM-DD completo)
            const startDate = new Date(start);

            // Si hay fecha de fin, usar esa, sino usar la fecha actual
            const endDate = end ? new Date(end) : new Date();

            // Calcular años, meses y días
            let years = endDate.getFullYear() - startDate.getFullYear();
            let months = endDate.getMonth() - startDate.getMonth();
            let days = endDate.getDate() - startDate.getDate();

            // Ajustar si los días son negativos
            if (days < 0) {
                months--;
                // Obtener el número de días del mes anterior
                const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
                days += prevMonth.getDate();
            }

            // Ajustar si los meses son negativos
            if (months < 0) {
                years--;
                months += 12;
            }

            return `${years} año(s), ${months} mes(es), ${days} día(s)`;
        },

        calculateTotalExperience(type) {
            let totalDays = 0;

            this.formData.experiences.forEach(exp => {
                if (type === 'specific' && !exp.isSpecific) return;

                if (exp.startDate) {
                    const startDate = new Date(exp.startDate);
                    const endDate = exp.endDate ? new Date(exp.endDate) :
                                   (exp.isCurrent ? new Date() : null);

                    if (endDate) {
                        // Calcular diferencia en días
                        const diffTime = Math.abs(endDate - startDate);
                        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        totalDays += days;
                    }
                }
            });

            // Convertir días totales a años, meses y días de manera más precisa
            const years = Math.floor(totalDays / 365);
            const remainingDaysAfterYears = totalDays % 365;
            const months = Math.floor(remainingDaysAfterYears / 30);
            const days = remainingDaysAfterYears % 30;

            return `${years} año(s), ${months} mes(es), ${days} día(s)`;
        },

        // Función para verificar si cumple con los requisitos de experiencia
        checkExperienceRequirement(type) {
            const requiredYears = type === 'general'
                ? parseFloat('{{ $jobProfile->general_experience_years ? (is_object($jobProfile->general_experience_years) ? $jobProfile->general_experience_years->toDecimal() : $jobProfile->general_experience_years) : 0 }}')
                : parseFloat('{{ $jobProfile->specific_experience_years ? (is_object($jobProfile->specific_experience_years) ? $jobProfile->specific_experience_years->toDecimal() : $jobProfile->specific_experience_years) : 0 }}');

            // Si no hay requisito, siempre cumple
            if (!requiredYears || requiredYears === 0) {
                return true;
            }

            let totalDays = 0;

            this.formData.experiences.forEach(exp => {
                if (type === 'specific' && !exp.isSpecific) return;

                if (exp.startDate) {
                    // Usar el mismo formato que calculateTotalExperience (sin agregar '-01')
                    const startDate = new Date(exp.startDate);
                    const endDate = exp.endDate ? new Date(exp.endDate) :
                                   (exp.isCurrent ? new Date() : null);

                    if (endDate) {
                        const diffTime = Math.abs(endDate - startDate);
                        const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                        totalDays += days;
                    }
                }
            });

            const totalYears = totalDays / 365;
            return totalYears >= requiredYears;
        },

        autoSave() {
            localStorage.setItem('applicationDraft_{{ $jobProfile->id }}', JSON.stringify(this.formData));
            this.lastSaved = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
        },

        loadFromLocalStorage() {
            const saved = localStorage.getItem('applicationDraft_{{ $jobProfile->id }}');
            if (saved) {
                try {
                    const data = JSON.parse(saved);

                    // Preservar datos personales del usuario (no sobrescribirlos con localStorage)
                    const personalDataBackup = { ...this.formData.personal };

                    // Cargar datos del localStorage
                    this.formData = { ...this.formData, ...data };

                    // Restaurar datos personales del usuario (siempre vienen del backend)
                    this.formData.personal = personalDataBackup;

                    console.log('Borrador cargado desde localStorage (datos personales preservados)');
                } catch (e) {
                    console.error('Error loading draft:', e);
                }
            }
        },

        saveDraft() {
            // Guardar como borrador (no enviar)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("applicant.job-postings.apply.store", [$posting->id, $jobProfile->id]) }}';

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_token';
            csrfField.value = '{{ csrf_token() }}';
            form.appendChild(csrfField);

            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'draft';
            form.appendChild(actionField);

            const dataField = document.createElement('input');
            dataField.type = 'hidden';
            dataField.name = 'formData';
            dataField.value = JSON.stringify(this.formData);
            form.appendChild(dataField);

            document.body.appendChild(form);
            form.submit();
        },

        submitApplication() {
            // 1. Validar que se aceptaron los términos
            if (!this.formData.termsAccepted || !this.formData.declarationAccepted) {
                alert('Debes aceptar la declaración jurada y los términos y condiciones');
                return;
            }

            // 2. Validar capacitaciones adicionales (si tienen datos parciales)
            const incompleteTrainings = this.formData.additionalTrainings.filter(training => {
                const hasAnyField = training.courseName || training.institution || training.hours || training.certificationDate;
                const hasAllFields = training.courseName && training.institution && training.hours && training.certificationDate;
                return hasAnyField && !hasAllFields;
            });

            if (incompleteTrainings.length > 0) {
                alert('Hay capacitaciones adicionales con datos incompletos. Por favor, completa todos los campos o elimina la capacitación.');
                this.currentStep = 4; // Llevar al paso de capacitaciones
                return;
            }

            // 3. Limpiar capacitaciones adicionales vacías antes de enviar
            this.formData.additionalTrainings = this.formData.additionalTrainings.filter(training => {
                return training.courseName && training.institution && training.hours && training.certificationDate;
            });

            const form = document.querySelector('form');

            // Limpiar campos hidden previos (por si se intenta enviar múltiples veces)
            const oldActionField = form.querySelector('input[name="action"]');
            const oldDataField = form.querySelector('input[name="formData"]');
            if (oldActionField) oldActionField.remove();
            if (oldDataField) oldDataField.remove();

            // Agregar campo de acción
            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'submit';
            form.appendChild(actionField);

            // Agregar datos del formulario
            const dataField = document.createElement('input');
            dataField.type = 'hidden';
            dataField.name = 'formData';
            dataField.value = JSON.stringify(this.formData);
            form.appendChild(dataField);

            // Limpiar localStorage después de enviar
            localStorage.removeItem('applicationDraft_{{ $jobProfile->id }}');

            // Enviar el formulario
            form.submit();
        }
    }
}
</script>
@endpush

@endsection
