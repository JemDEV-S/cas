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
    <form @submit.prevent="submitApplication" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

        <!-- Paso 1: Datos Personales -->
        <div x-show="currentStep === 1" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Datos Personales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo *</label>
                    <input type="text"
                           x-model="formData.personal.fullName"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">DNI *</label>
                    <input type="text"
                           x-model="formData.personal.dni"
                           @input="autoSave"
                           required
                           maxlength="8"
                           pattern="[0-9]{8}"
                           readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Nacimiento *</label>
                    <input type="date"
                           x-model="formData.personal.birthDate"
                           @input="autoSave"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección Completa *</label>
                    <input type="text"
                           x-model="formData.personal.address"
                           @input="autoSave"
                           required
                           placeholder="Ej: Av. Los Incas 123, San Jerónimo, Cusco"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono Fijo</label>
                    <input type="tel"
                           x-model="formData.personal.phone"
                           @input="autoSave"
                           placeholder="Ej: 084-123456"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Celular *</label>
                    <input type="tel"
                           x-model="formData.personal.mobilePhone"
                           @input="autoSave"
                           required
                           pattern="[0-9]{9}"
                           maxlength="9"
                           placeholder="Ej: 987654321"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email"
                           x-model="formData.personal.email"
                           @input="autoSave"
                           required
                           readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50">
                    <p class="text-xs text-gray-500 mt-1">Este campo no es editable</p>
                </div>
            </div>
        </div>

        <!-- Paso 2: Formación Académica -->
        <div x-show="currentStep === 2" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Formación Académica</h2>
            <p class="text-gray-600 mb-6">Declara tu formación académica. Los documentos sustentatorios serán solicitados en la Fase 5.</p>

            <!-- Requisito del perfil -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Requisito del perfil:</strong> {{ $jobProfile->education_level ?? 'No especificado' }}
                    @if($jobProfile->career_field)
                        - {{ $jobProfile->career_field }}
                    @endif
                </p>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Grado Académico *</label>
                            <select x-model="academic.degreeType"
                                    @change="autoSave"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccione...</option>
                                <option value="SECUNDARIA">Secundaria Completa</option>
                                <option value="TECNICO">Técnico</option>
                                <option value="BACHILLER">Bachiller</option>
                                <option value="TITULO">Título Profesional</option>
                                <option value="MAESTRIA">Maestría</option>
                                <option value="DOCTORADO">Doctorado</option>
                            </select>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Carrera/Especialidad *</label>
                            <input type="text"
                                   x-model="academic.careerField"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
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
            <p class="text-gray-600 mb-6">Declara tu experiencia laboral. El sistema calculará automáticamente la duración.</p>

            <!-- Requisitos del perfil -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-blue-700 mb-1">
                    <strong>Experiencia General:</strong> {{ $jobProfile->general_experience_years ?? 0 }} años mínimo
                </p>
                <p class="text-sm text-blue-700">
                    <strong>Experiencia Específica:</strong> {{ $jobProfile->specific_experience_years ?? 0 }} años mínimo
                </p>
            </div>

            <!-- Totales calculados -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <p class="text-sm text-green-700 font-semibold mb-1">Experiencia General Total</p>
                    <p class="text-2xl font-bold text-green-900" x-text="calculateTotalExperience('general')"></p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                    <p class="text-sm text-purple-700 font-semibold mb-1">Experiencia Específica Total</p>
                    <p class="text-2xl font-bold text-purple-900" x-text="calculateTotalExperience('specific')"></p>
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
                            <input type="month"
                                   x-model="experience.startDate"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Fin</label>
                            <input type="month"
                                   x-model="experience.endDate"
                                   @input="autoSave"
                                   :disabled="experience.isCurrent"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            <label class="flex items-center mt-2">
                                <input type="checkbox"
                                       x-model="experience.isCurrent"
                                       @change="autoSave"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Trabajo actual</span>
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           x-model="experience.isPublicSector"
                                           @change="autoSave"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">¿Es experiencia en el sector público?</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           x-model="experience.isSpecific"
                                           @change="autoSave"
                                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">¿Es experiencia específica relacionada al puesto?</span>
                                </label>
                            </div>
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
            <p class="text-gray-600 mb-6">Declara tus capacitaciones y cursos. Los certificados serán solicitados en la Fase 5.</p>

            <template x-for="(training, index) in formData.trainings" :key="index">
                <div class="border border-gray-200 rounded-xl p-6 mb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold text-gray-900">Capacitación <span x-text="index + 1"></span></h3>
                        <button type="button"
                                @click="removeTraining(index)"
                                x-show="formData.trainings.length > 1"
                                class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Curso/Capacitación *</label>
                            <input type="text"
                                   x-model="training.courseName"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Institución que dictó *</label>
                            <input type="text"
                                   x-model="training.institution"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Horas *</label>
                            <input type="number"
                                   x-model="training.hours"
                                   @input="autoSave"
                                   required
                                   min="1"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Mes/Año de Certificación *</label>
                            <input type="month"
                                   x-model="training.certificationDate"
                                   @input="autoSave"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </template>

            <button type="button"
                    @click="addTraining"
                    class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-blue-500 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Agregar otra capacitación
            </button>
        </div>

        <!-- Paso 5: Conocimientos Técnicos -->
        <div x-show="currentStep === 5" class="fade-in">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Conocimientos Técnicos</h2>
            <p class="text-gray-600 mb-6">Indica tu nivel de conocimiento en cada área requerida.</p>

            @if($jobProfile->knowledge_areas && count($jobProfile->knowledge_areas) > 0)
                <div class="space-y-4 mb-6">
                    @foreach($jobProfile->knowledge_areas as $index => $area)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">{{ $area }}</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio"
                                           x-model="formData.knowledge[{{ $index }}].level"
                                           value="Básico"
                                           @change="autoSave"
                                           name="knowledge_{{ $index }}"
                                           class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm">Básico</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio"
                                           x-model="formData.knowledge[{{ $index }}].level"
                                           value="Intermedio"
                                           @change="autoSave"
                                           name="knowledge_{{ $index }}"
                                           class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm">Intermedio</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio"
                                           x-model="formData.knowledge[{{ $index }}].level"
                                           value="Avanzado"
                                           @change="autoSave"
                                           name="knowledge_{{ $index }}"
                                           class="text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm">Avanzado</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
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
                <!-- Colegiatura -->
                @if($jobProfile->colegiatura_required)
                    <div class="border border-gray-200 rounded-xl p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Colegiatura *</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Colegio Profesional</label>
                                <input type="text"
                                       x-model="formData.registrations.colegiatura.college"
                                       @input="autoSave"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Colegiatura</label>
                                <input type="text"
                                       x-model="formData.registrations.colegiatura.number"
                                       @input="autoSave"
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
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
                <div class="border border-gray-200 rounded-xl p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Datos Personales</h4>
                    <p class="text-sm text-gray-600" x-text="`${formData.personal.fullName} - DNI: ${formData.personal.dni}`"></p>
                </div>

                <div class="border border-gray-200 rounded-xl p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Formación Académica</h4>
                    <p class="text-sm text-gray-600" x-text="`${formData.academics.length} título(s)/grado(s) declarado(s)`"></p>
                </div>

                <div class="border border-gray-200 rounded-xl p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Experiencia Laboral</h4>
                    <p class="text-sm text-gray-600">
                        <span x-text="`${formData.experiences.length} experiencia(s) declarada(s)`"></span><br>
                        <span x-text="`General: ${calculateTotalExperience('general')}`"></span><br>
                        <span x-text="`Específica: ${calculateTotalExperience('specific')}`"></span>
                    </p>
                </div>

                <div class="border border-gray-200 rounded-xl p-4">
                    <h4 class="font-bold text-gray-900 mb-2">Capacitaciones</h4>
                    <p class="text-sm text-gray-600" x-text="`${formData.trainings.length} capacitación(es) declarada(s)`"></p>
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
        complianceStatus: 'partial', // full, partial, none

        formData: {
            personal: {
                fullName: '{{ auth()->user()->name ?? '' }}',
                dni: '{{ auth()->user()->dni ?? '' }}',
                birthDate: '',
                address: '',
                phone: '',
                mobilePhone: '',
                email: '{{ auth()->user()->email ?? '' }}'
            },
            academics: [{
                degreeType: '',
                institution: '',
                careerField: '',
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
            trainings: [{
                courseName: '',
                institution: '',
                hours: '',
                certificationDate: ''
            }],
            knowledge: [],
            otherKnowledge: '',
            registrations: {
                colegiatura: {
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
            // Cargar datos desde localStorage si existen
            this.loadFromLocalStorage();

            // Auto-save cada 30 segundos
            setInterval(() => {
                this.autoSave();
            }, 30000);

            // Inicializar knowledge array con las áreas del perfil
            @if($jobProfile->knowledge_areas)
                @foreach($jobProfile->knowledge_areas as $index => $area)
                    this.formData.knowledge[{{ $index }}] = {
                        area: '{{ $area }}',
                        level: ''
                    };
                @endforeach
            @endif
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

        addAcademic() {
            this.formData.academics.push({
                degreeType: '',
                institution: '',
                careerField: '',
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

        addTraining() {
            this.formData.trainings.push({
                courseName: '',
                institution: '',
                hours: '',
                certificationDate: ''
            });
        },

        removeTraining(index) {
            this.formData.trainings.splice(index, 1);
        },

        calculateDuration(start, end) {
            if (!start) return '0 años, 0 meses';

            const startDate = new Date(start + '-01');
            const endDate = end ? new Date(end + '-01') : new Date();

            const months = (endDate.getFullYear() - startDate.getFullYear()) * 12 +
                          (endDate.getMonth() - startDate.getMonth());

            const years = Math.floor(months / 12);
            const remainingMonths = months % 12;

            return `${years} año(s), ${remainingMonths} mes(es)`;
        },

        calculateTotalExperience(type) {
            let totalMonths = 0;

            this.formData.experiences.forEach(exp => {
                if (type === 'specific' && !exp.isSpecific) return;

                if (exp.startDate) {
                    const startDate = new Date(exp.startDate + '-01');
                    const endDate = exp.endDate ? new Date(exp.endDate + '-01') :
                                   (exp.isCurrent ? new Date() : null);

                    if (endDate) {
                        const months = (endDate.getFullYear() - startDate.getFullYear()) * 12 +
                                     (endDate.getMonth() - startDate.getMonth());
                        totalMonths += months;
                    }
                }
            });

            const years = Math.floor(totalMonths / 12);
            const months = totalMonths % 12;

            return `${years} año(s), ${months} mes(es)`;
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
                    this.formData = { ...this.formData, ...data };
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
            if (!this.formData.termsAccepted || !this.formData.declarationAccepted) {
                alert('Debes aceptar la declaración jurada y los términos y condiciones');
                return;
            }

            // El formulario se enviará normalmente con action='submit'
            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = 'submit';
            document.querySelector('form').appendChild(actionField);

            const dataField = document.createElement('input');
            dataField.type = 'hidden';
            dataField.name = 'formData';
            dataField.value = JSON.stringify(this.formData);
            document.querySelector('form').appendChild(dataField);

            // Limpiar localStorage después de enviar
            localStorage.removeItem('applicationDraft_{{ $jobProfile->id }}');
        }
    }
}
</script>
@endpush

@endsection
