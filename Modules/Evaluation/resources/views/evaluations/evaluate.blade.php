@extends('layouts.app')

@section('title', 'Evaluar Postulante')

@section('page-title')
    Evaluación - {{ $application->full_name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evaluation.my-evaluations') }}">Mis Evaluaciones</a></li>
    <li class="breadcrumb-item active">Evaluar</li>
@endsection

@section('content')
<div class="flex h-screen bg-gray-50" x-data="evaluationApp()" x-init="init()">
    <!-- Panel Izquierdo: CV -->
    <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header del CV -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Curriculum Vitae</h3>
                    <p class="text-sm text-blue-100 mt-1">
                        {{ $application->full_name ?? 'N/A' }}
                    </p>
                </div>
                @if($cvDocument)
                <a href="{{ route('application.documents.download', $cvDocument->id) }}"
                   class="px-4 py-2 bg-white text-blue-700 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar CV
                </a>
                @endif
            </div>
        </div>

        <!-- Visor de PDF -->
        <div class="flex-1 overflow-hidden bg-gray-100">
            @if($cvDocument && $cvDocument->fileExists())
                <iframe
                    src="{{ route('evaluation.view-cv', $evaluation->id) }}"
                    class="w-full h-full border-0"
                    title="Curriculum Vitae">
                </iframe>
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">CV no disponible</p>
                        <p class="text-sm mt-1">El postulante no ha cargado su CV</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel Derecho: Formulario de Evaluación -->
    <div class="w-1/2 flex flex-col bg-gray-50">
        <!-- Header del formulario -->
        <div class="px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Evaluación de Currículum</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $jobProfile->jobPosting->title ?? 'Convocatoria' }} - {{ $evaluation->phase->name }}
                    </p>
                </div>

                <!-- Estado de evaluación -->
                <div class="text-right">
                    <div x-show="!isDisqualified" class="text-2xl font-bold text-blue-600" x-text="totalScore.toFixed(2)">0.00</div>
                    <div x-show="isDisqualified" class="text-2xl font-bold text-red-600">DESCALIFICADO</div>
                    <p x-show="!isDisqualified" class="text-xs text-gray-500">de {{ number_format($maxTotalScore, 2) }} pts</p>
                    <div x-show="!isDisqualified" class="mt-1 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido del formulario con scroll -->
        <div class="flex-1 overflow-y-auto px-6 py-6">
            <form id="evaluationForm">

                <!-- INFORMACIÓN DEL PERFIL Y POSTULACIÓN -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-md mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 border-b border-blue-300">
                        <h4 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Información de la Postulación
                        </h4>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Postulación</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->code ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Postulante</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @if($positionCode)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Puesto</p>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800">
                                    {{ $positionCode }}
                                    @if($jobProfile && $jobProfile->positionCode && $jobProfile->positionCode->name)
                                    <span class="ml-2 text-blue-600">- {{ $jobProfile->positionCode->name }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Título del Perfil</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $jobProfile->title ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- FLUJO DE CALIFICACIÓN PREVIA A CRITERIOS -->
                <div class="space-y-5 mb-6">

                    <!-- 1. VERIFICACIÓN DE ORDEN DE DOCUMENTOS DEL CV -->
                    <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-500 text-white text-sm font-bold">1</span>
                                    Verificación de Orden de Documentos del CV
                                </h4>
                                <span x-show="cvOrderCheck === 'not_complies'" class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                    No Cumple
                                </span>
                                <span x-show="cvOrderCheck === 'complies'" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                                    Cumple
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-amber-900 font-medium">
                                    Verifique que el CV del postulante cumpla con el orden establecido de documentos según las bases de la convocatoria.
                                </p>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 bg-green-50 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                                    <input type="radio"
                                           name="cv_order_check"
                                           value="complies"
                                           x-model="cvOrderCheck"
                                           class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-semibold text-gray-900">Cumple con el orden de documentos</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-red-50 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-100 transition-colors">
                                    <input type="radio"
                                           name="cv_order_check"
                                           value="not_complies"
                                           x-model="cvOrderCheck"
                                           class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-semibold text-gray-900">No cumple con el orden de documentos</span>
                                </label>
                            </div>

                            <div x-show="cvOrderCheck === 'not_complies'"
                                 x-transition
                                 class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-800 mb-2">El postulante será descalificado por no cumplir con el orden de documentos.</p>
                                        <textarea x-model="cvOrderReason"
                                                  class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  rows="2"
                                                  placeholder="Especifique qué documento(s) están mal ordenados o faltantes..."></textarea>
                                        <button type="button"
                                                @click="disqualify('cv_order')"
                                                class="mt-3 w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Descalificar por no cumplir orden de documentos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. VERIFICACIÓN DE NIVEL EDUCATIVO -->
                    @if($jobProfile && $jobProfile->education_levels && count($jobProfile->education_levels) > 0)
                    <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden" x-show="cvOrderCheck === 'complies'">
                        <div class="px-5 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-purple-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-purple-500 text-white text-sm font-bold">2</span>
                                    Verificación de Nivel Educativo
                                </h4>
                                <span x-show="educationCheck === 'not_complies'" class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                    No Cumple
                                </span>
                                <span x-show="educationCheck === 'complies'" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                                    Cumple
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                <p class="text-xs font-medium text-purple-900 uppercase tracking-wide mb-2">Niveles Educativos Requeridos:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($jobProfile->education_levels as $level)
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-purple-100 text-purple-800">
                                        {{ $level }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 bg-green-50 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                                    <input type="radio"
                                           name="education_check"
                                           value="complies"
                                           x-model="educationCheck"
                                           class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-semibold text-gray-900">Cumple con el nivel educativo requerido</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-red-50 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-100 transition-colors">
                                    <input type="radio"
                                           name="education_check"
                                           value="not_complies"
                                           x-model="educationCheck"
                                           class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-semibold text-gray-900">No cumple con el nivel educativo requerido</span>
                                </label>
                            </div>

                            <div x-show="educationCheck === 'not_complies'"
                                 x-transition
                                 class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-800 mb-2">El postulante será descalificado por no cumplir el requisito de nivel educativo.</p>
                                        <textarea x-model="educationReason"
                                                  class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  rows="2"
                                                  placeholder="Especifique el motivo (ej: Solo tiene título técnico, se requiere bachiller universitario)..."></textarea>
                                        <button type="button"
                                                @click="disqualify('education')"
                                                class="mt-3 w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Descalificar por no cumplir nivel educativo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. VERIFICACIÓN DE CARRERA PROFESIONAL -->
                    @if($jobProfile && $jobProfile->career_field)
                    <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden"
                         x-show="cvOrderCheck === 'complies' && (educationCheck === 'complies' || educationCheck === '')">
                        <div class="px-5 py-4 bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-teal-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-teal-500 text-white text-sm font-bold">3</span>
                                    Verificación de Carrera Profesional
                                </h4>
                                <span x-show="careerCheck === 'not_complies'" class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                    No Cumple
                                </span>
                                <span x-show="careerCheck === 'complies'" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                                    Cumple
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="bg-teal-50 border border-teal-200 rounded-lg p-4 mb-4">
                                <p class="text-xs font-medium text-teal-900 uppercase tracking-wide mb-2">Carrera Requerida:</p>
                                <p class="text-sm font-bold text-teal-900">{{ $jobProfile->career_field }}</p>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 bg-green-50 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                                    <input type="radio"
                                           name="career_check"
                                           value="complies"
                                           x-model="careerCheck"
                                           class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-semibold text-gray-900">Cumple con la carrera profesional requerida</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-red-50 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-100 transition-colors">
                                    <input type="radio"
                                           name="career_check"
                                           value="not_complies"
                                           x-model="careerCheck"
                                           class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-semibold text-gray-900">No cumple con la carrera profesional requerida</span>
                                </label>
                            </div>

                            <div x-show="careerCheck === 'not_complies'"
                                 x-transition
                                 class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-800 mb-2">El postulante será descalificado por no cumplir el requisito de carrera profesional.</p>
                                        <textarea x-model="careerReason"
                                                  class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  rows="2"
                                                  placeholder="Especifique el motivo (ej: Tiene título en Contabilidad, se requiere Administración)..."></textarea>
                                        <button type="button"
                                                @click="disqualify('career')"
                                                class="mt-3 w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Descalificar por no cumplir carrera requerida
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 4. VERIFICACIÓN DE EXPERIENCIA GENERAL -->
                    @if($jobProfile && $jobProfile->general_experience_years)
                    <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden"
                         x-show="cvOrderCheck === 'complies' && (educationCheck === 'complies' || educationCheck === '') && (careerCheck === 'complies' || careerCheck === '')">
                        <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-indigo-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-500 text-white text-sm font-bold">4</span>
                                    Verificación de Experiencia General
                                </h4>
                                <span x-show="experienceCheck === 'not_complies'" class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                    No Cumple
                                </span>
                                <span x-show="experienceCheck === 'complies'" class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                                    Cumple
                                </span>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4">
                                <p class="text-xs font-medium text-indigo-900 uppercase tracking-wide mb-2">Experiencia General Requerida:</p>
                                <p class="text-sm font-bold text-indigo-900">
                                    @php
                                        $rawExperience = $jobProfile->getAttributes()['general_experience_years'] ?? 0;
                                        $experienceObj = \Modules\Core\ValueObjects\ExperienceDuration::fromDecimal((float) $rawExperience);
                                    @endphp
                                    {{ $experienceObj->toHuman() }}
                                </p>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 bg-green-50 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-100 transition-colors">
                                    <input type="radio"
                                           name="experience_check"
                                           value="complies"
                                           x-model="experienceCheck"
                                           class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-semibold text-gray-900">Cumple con la experiencia general requerida</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 bg-red-50 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-100 transition-colors">
                                    <input type="radio"
                                           name="experience_check"
                                           value="not_complies"
                                           x-model="experienceCheck"
                                           class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-semibold text-gray-900">No cumple con la experiencia general requerida</span>
                                </label>
                            </div>

                            <div x-show="experienceCheck === 'not_complies'"
                                 x-transition
                                 class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-800 mb-2">El postulante será descalificado por no cumplir el requisito de experiencia general.</p>
                                        <textarea x-model="experienceReason"
                                                  class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  rows="2"
                                                  placeholder="Especifique el motivo (ej: Solo acredita 1 año, se requieren {{ $experienceObj->toHuman() }})..."></textarea>
                                        <button type="button"
                                                @click="disqualify('experience')"
                                                class="mt-3 w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Descalificar por no cumplir experiencia requerida
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 5. VERIFICACIÓN DE CAPACITACIONES (CHECKLIST DINÁMICO) -->
                    @if($jobProfile && $jobProfile->required_courses && count($jobProfile->required_courses) > 0)
                    <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden"
                         x-show="cvOrderCheck === 'complies' && (educationCheck === 'complies' || educationCheck === '') && (careerCheck === 'complies' || careerCheck === '') && (experienceCheck === 'complies' || experienceCheck === '')">
                        <div class="px-5 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-500 text-white text-sm font-bold">5</span>
                                    Verificación de Capacitaciones Requeridas
                                </h4>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-green-900 font-medium mb-2">
                                    Marque cada capacitación que el postulante cumple. Si falta alguna, será descalificado.
                                </p>
                            </div>

                            <div class="space-y-3">
                                @foreach($jobProfile->required_courses as $index => $course)
                                <label class="flex items-start gap-3 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                       :class="trainingChecks[{{ $index }}] ? 'border-green-300 bg-green-50' : 'border-gray-200'">
                                    <input type="checkbox"
                                           x-model="trainingChecks[{{ $index }}]"
                                           @change="checkTrainings()"
                                           class="mt-1 w-5 h-5 text-green-600 focus:ring-green-500 rounded">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $course }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Marque si el postulante acredita esta capacitación</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>

                            <div x-show="trainingsFailed"
                                 x-transition
                                 class="mt-4 p-4 bg-red-100 border-l-4 border-red-500 rounded-r-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-red-800 mb-2">El postulante no cumple con todas las capacitaciones requeridas.</p>
                                        <textarea x-model="trainingsReason"
                                                  class="w-full px-3 py-2 text-sm border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                  rows="2"
                                                  placeholder="Especifique qué capacitaciones faltan o no son válidas..."></textarea>
                                        <button type="button"
                                                @click="disqualify('trainings')"
                                                class="mt-3 w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Descalificar por no cumplir capacitaciones
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="!trainingsFailed && trainingChecks.filter(c => c).length === {{ count($jobProfile->required_courses) }}"
                                 x-transition
                                 class="mt-4 p-3 bg-green-100 border-l-4 border-green-500 rounded-r-lg">
                                <p class="text-sm font-semibold text-green-800 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Cumple con todas las capacitaciones requeridas
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- CRITERIOS DE EVALUACIÓN (Solo si pasa las verificaciones previas) -->
                <div x-show="!isDisqualified && allPreChecksPass" x-transition class="space-y-4">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg px-5 py-3 shadow-md">
                        <h4 class="text-base font-bold text-white">Criterios de Evaluación</h4>
                        <p class="text-sm text-blue-100 mt-1">Complete la evaluación de cada criterio según la guía proporcionada</p>
                    </div>

                    @foreach($criteria as $criterion)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm"
                         data-criterion-id="{{ $criterion->id }}">
                        <!-- Header del criterio -->
                        <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $criterion->order }}. {{ $criterion->name }}
                                    </h4>
                                    @if($criterion->description)
                                        <p class="text-sm text-gray-600 mt-1">{{ $criterion->description }}</p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    Peso: {{ $criterion->weight }}x
                                </span>
                            </div>
                        </div>

                        <!-- Cuerpo del criterio -->
                        <div class="px-5 py-4 space-y-4">
                            <!-- Guía de evaluación -->
                            @if($criterion->evaluation_guide)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h5 class="text-sm font-semibold text-blue-900 mb-1">Guía de evaluación:</h5>
                                        <pre class="text-sm text-blue-800 whitespace-pre-line font-sans">{{ $criterion->evaluation_guide }}</pre>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Criterios con escalas (opciones múltiples) -->
                            @if($criterion->score_scales && count($criterion->score_scales) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Seleccione las opciones que cumple el postulante
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($criterion->score_scales as $scale)
                                        <label class="flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox"
                                                   class="scale-checkbox mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                   name="criteria[{{ $criterion->id }}][options][]"
                                                   value="{{ $scale['puntos'] }}"
                                                   data-criterion-id="{{ $criterion->id }}"
                                                   data-max-score="{{ $criterion->max_score }}"
                                                   @change="handleCheckboxChange($event)">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $scale['descripcion'] }}</span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                                        +{{ $scale['puntos'] }} pts
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>

                                    <input type="hidden"
                                           class="score-input"
                                           name="criteria[{{ $criterion->id }}][score]"
                                           data-min="{{ $criterion->min_score }}"
                                           data-max="{{ $criterion->max_score }}"
                                           value="{{ $details[$criterion->id]->score ?? 0 }}">

                                    <div class="mt-3 flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <span class="text-sm font-medium text-gray-700">Puntaje calculado:</span>
                                        <div>
                                            <span class="calculated-score text-xl font-bold text-blue-600">{{ $details[$criterion->id]->score ?? 0 }}</span>
                                            <span class="text-sm text-gray-600"> / {{ number_format($criterion->max_score, 2) }} pts</span>
                                        </div>
                                    </div>
                                </div>

                            <!-- Criterios numéricos (sin escalas) -->
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Puntaje
                                        <span class="text-red-500">*</span>
                                        <span class="text-gray-500 font-normal">(Rango: {{ $criterion->min_score }} - {{ $criterion->max_score }})</span>
                                    </label>
                                    <input type="number"
                                           class="score-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           name="criteria[{{ $criterion->id }}][score]"
                                           data-min="{{ $criterion->min_score }}"
                                           data-max="{{ $criterion->max_score }}"
                                           min="{{ $criterion->min_score }}"
                                           max="{{ $criterion->max_score }}"
                                           step="0.5"
                                           value="{{ $details[$criterion->id]->score ?? '' }}"
                                           @change="handleScoreChange($event)"
                                           {{ $criterion->requires_comment ? 'required' : '' }}>
                                    <p class="invalid-feedback text-red-500 text-sm mt-1 hidden">
                                        El puntaje debe estar entre {{ $criterion->min_score }} y {{ $criterion->max_score }}
                                    </p>

                                    @if(isset($criterion->metadata['puntaje_minimo_aprobatorio']))
                                    <div class="mt-2 flex items-center gap-2 text-red-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm">Puntaje mínimo aprobatorio: {{ $criterion->metadata['puntaje_minimo_aprobatorio'] }} puntos</span>
                                    </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Comentarios (Colapsable y Opcional por defecto) -->
                            <div>
                                <button type="button"
                                        @click="toggleSection('comments-{{ $criterion->id }}')"
                                        class="w-full flex items-center justify-between p-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                    <span class="text-sm font-medium text-gray-700">
                                        Comentarios @if($criterion->requires_comment)<span class="text-red-500">*</span>@else<span class="text-gray-400">(Opcional)</span>@endif
                                    </span>
                                    <svg class="w-4 h-4 text-gray-600 transform transition-transform"
                                         :class="openSections['comments-{{ $criterion->id }}'] ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="openSections['comments-{{ $criterion->id }}']"
                                     x-transition
                                     class="mt-2">
                                    <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              name="criteria[{{ $criterion->id }}][comments]"
                                              rows="3"
                                              placeholder="Observaciones sobre este criterio..."
                                              {{ $criterion->requires_comment ? 'required' : '' }}>{{ $details[$criterion->id]->comments ?? '' }}</textarea>
                                </div>
                            </div>

                            <!-- Evidencia (Colapsable y Opcional) -->
                            @if($criterion->requires_evidence)
                            <div>
                                <button type="button"
                                        @click="toggleSection('evidence-{{ $criterion->id }}')"
                                        class="w-full flex items-center justify-between p-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                    <span class="text-sm font-medium text-gray-700">
                                        Evidencia <span class="text-gray-400">(Opcional)</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-600 transform transition-transform"
                                         :class="openSections['evidence-{{ $criterion->id }}'] ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="openSections['evidence-{{ $criterion->id }}']"
                                     x-transition
                                     class="mt-2">
                                    <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              name="criteria[{{ $criterion->id }}][evidence]"
                                              rows="2"
                                              placeholder="Describa la evidencia encontrada...">{{ $details[$criterion->id]->evidence ?? '' }}</textarea>
                                </div>
                            </div>
                            @endif

                            <!-- Puntaje ponderado -->
                            <div class="flex items-center justify-end pt-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Puntaje ponderado: </span>
                                <span class="ml-2 text-base font-semibold text-gray-900 weighted-score">0.00</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Mensaje si está descalificado -->
                <div x-show="isDisqualified" x-transition class="bg-red-100 border-2 border-red-500 rounded-xl p-6 text-center">
                    <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <h3 class="text-xl font-bold text-red-900 mb-2">Postulante Descalificado</h3>
                    <p class="text-sm text-red-800 font-medium mb-4" x-text="disqualificationReason"></p>
                    <button type="button"
                            @click="resetDisqualification()"
                            class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Revertir Descalificación
                    </button>
                </div>

                <!-- Comentarios Generales -->
                <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm" x-show="!isDisqualified">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h4 class="text-base font-semibold text-gray-900">Comentarios Generales</h4>
                    </div>
                    <div class="px-5 py-4">
                        <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  name="general_comments"
                                  rows="4"
                                  placeholder="Observaciones generales sobre la evaluación...">{{ $evaluation->general_comments }}</textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer con botones de acción -->
        <div class="px-6 py-4 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span>Auto-guardado activo</span>
                    </div>
                    @if($evaluation->deadline_at)
                    <div class="flex items-center gap-2 {{ $evaluation->isOverdue() ? 'text-red-600' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Vence: {{ $evaluation->deadline_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <button type="button"
                            @click="saveDraft()"
                            :disabled="isSaving"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span x-text="isSaving ? 'Guardando...' : 'Guardar Borrador'">Guardar Borrador</span>
                    </button>

                    <a href="{{ route('evaluation.index') }}"
                       class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg border border-gray-300 transition-colors">
                        Cancelar
                    </a>

                    <button type="button"
                            @click="submitEvaluation()"
                            :disabled="!canSubmit || isSubmitting"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="isSubmitting ? 'Enviando...' : 'Enviar Evaluación'">Enviar Evaluación</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Configurar CSRF token para axios (por si se usa en otros lugares)
if (typeof axios !== 'undefined') {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    } else {
        console.error('CSRF token meta tag not found!');
    }
}

function evaluationApp() {
    return {
        evaluationId: {{ $evaluation->id }},
        criteria: @json($criteria),
        totalScore: 0,
        progress: 0,
        evaluatedCount: 0,
        isSaving: false,
        isSubmitting: false,
        canSubmit: {{ $evaluation->canEdit() ? 'true' : 'false' }},
        saveTimeout: null,
        csrfTokenExpired: false,

        // Estados de verificación previa
        cvOrderCheck: '',
        cvOrderReason: '',
        educationCheck: '',
        educationReason: '',
        careerCheck: '',
        careerReason: '',
        experienceCheck: '',
        experienceReason: '',
        trainingChecks: @json(array_fill(0, $jobProfile && $jobProfile->required_courses ? count($jobProfile->required_courses) : 0, false)),
        trainingsReason: '',
        trainingsFailed: false,

        // Estado de descalificación
        isDisqualified: @json(isset($evaluation->metadata['disqualified']) && $evaluation->metadata['disqualified'] === true),
        disqualificationReason: @json($evaluation->metadata['disqualified'] ?? false ? $evaluation->general_comments : ''),
        disqualificationType: @json($evaluation->metadata['disqualification_type'] ?? ''),

        // Secciones colapsables
        openSections: {},

        init() {
            // Verificar CSRF token al inicio
            this.verifyCsrfToken();
            // Inicializar checkboxes guardados
            this.initializeCheckboxes();
            // Calcular puntajes
            this.calculateWeightedScores();
        },

        get allPreChecksPass() {
            // Verificar que todas las verificaciones previas hayan pasado
            const cvPass = this.cvOrderCheck === 'complies';
            const eduPass = this.educationCheck === 'complies' || this.educationCheck === '';
            const careerPass = this.careerCheck === 'complies' || this.careerCheck === '';
            const expPass = this.experienceCheck === 'complies' || this.experienceCheck === '';
            const trainingsPass = !this.trainingsFailed;

            return cvPass && eduPass && careerPass && expPass && trainingsPass;
        },

        toggleSection(sectionId) {
            if (!this.openSections[sectionId]) {
                this.openSections[sectionId] = true;
            } else {
                this.openSections[sectionId] = false;
            }
        },

        checkTrainings() {
            // Verificar si todas las capacitaciones están marcadas
            const allChecked = this.trainingChecks.every(check => check === true);
            this.trainingsFailed = !allChecked;
        },

        disqualify(type) {
            let reason = '';
            switch(type) {
                case 'cv_order':
                    reason = 'No cumple con el orden de documentos del CV: ' + this.cvOrderReason;
                    break;
                case 'education':
                    reason = 'No cumple con el nivel educativo requerido: ' + this.educationReason;
                    break;
                case 'career':
                    reason = 'No cumple con la carrera profesional requerida: ' + this.careerReason;
                    break;
                case 'experience':
                    reason = 'No cumple con la experiencia general requerida: ' + this.experienceReason;
                    break;
                case 'trainings':
                    reason = 'No cumple con las capacitaciones requeridas: ' + this.trainingsReason;
                    break;
            }

            if (!reason.split(':')[1].trim()) {
                alert('Por favor, especifique el motivo de la descalificación');
                return;
            }

            if (confirm('¿Está seguro de descalificar a este postulante? Esta acción se guardará en la evaluación.')) {
                this.isDisqualified = true;
                this.disqualificationReason = reason;
                this.disqualificationType = type;
                this.totalScore = 0;
                this.progress = 0;

                // Guardar la descalificación
                this.saveDisqualification();
            }
        },

        resetDisqualification() {
            if (confirm('¿Está seguro de revertir la descalificación?')) {
                this.isDisqualified = false;
                this.disqualificationReason = '';
                this.disqualificationType = '';

                // Resetear checks según el tipo
                this.cvOrderCheck = '';
                this.educationCheck = '';
                this.careerCheck = '';
                this.experienceCheck = '';
                this.trainingChecks = this.trainingChecks.map(() => false);
                this.trainingsFailed = false;

                this.calculateWeightedScores();
            }
        },

        async saveDisqualification() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('{{ route("evaluation.save-detail", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        disqualified: true,
                        disqualification_reason: this.disqualificationReason,
                        disqualification_type: this.disqualificationType
                    })
                });

                if (!response.ok) {
                    console.error('Error al guardar descalificación');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        verifyCsrfToken() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found in meta tag');
                alert('Error de configuración: Token CSRF no encontrado. Por favor, recarga la página.');
                this.csrfTokenExpired = true;
            }
        },

        initializeCheckboxes() {
            // Para criterios con checkboxes, restaurar el puntaje calculado y marcar checkboxes
            document.querySelectorAll('[data-criterion-id]').forEach(card => {
                const scoreInput = card.querySelector('input.score-input[type="hidden"]');
                if (scoreInput) {
                    const savedScore = parseFloat(scoreInput.value) || 0;
                    const calculatedSpan = card.querySelector('.calculated-score');
                    if (calculatedSpan) {
                        calculatedSpan.textContent = savedScore.toFixed(2);
                    }

                    if (savedScore > 0) {
                        const checkboxes = card.querySelectorAll('.scale-checkbox');
                        if (checkboxes.length > 0) {
                            let accumulated = 0;
                            checkboxes.forEach(cb => {
                                const points = parseFloat(cb.value);
                                if (accumulated + points <= savedScore) {
                                    cb.checked = true;
                                    accumulated += points;
                                }
                            });
                        }
                    }
                }
            });
        },

        calculateWeightedScores() {
            if (this.isDisqualified) {
                this.totalScore = 0;
                this.progress = 0;
                return;
            }

            let total = 0;
            let count = 0;

            document.querySelectorAll('[data-criterion-id]').forEach(card => {
                const criterionId = card.dataset.criterionId;
                const criterion = this.criteria.find(c => c.id == criterionId);

                if (!criterion) {
                    console.warn('Criterion not found:', criterionId);
                    return;
                }

                const scoreInput = card.querySelector('input.score-input');

                if (!scoreInput) {
                    console.warn('Score input not found for criterion:', criterionId);
                    return;
                }

                const rawValue = scoreInput.value;
                const score = parseFloat(rawValue) || 0;

                const weighted = score * (parseFloat(criterion.weight) || 1);
                const weightedSpan = card.querySelector('.weighted-score');
                if (weightedSpan) {
                    weightedSpan.textContent = weighted.toFixed(2);
                }

                if (score > 0) {
                    count++;
                }
                total += weighted;
            });

            this.totalScore = total;
            this.evaluatedCount = count;
            this.progress = this.criteria.length > 0 ? (count / this.criteria.length) * 100 : 0;
        },

        handleCheckboxChange(event) {
            const checkbox = event.target;
            const card = checkbox.closest('div.bg-white[data-criterion-id]');
            const criterionId = checkbox.dataset.criterionId;
            const maxScore = parseFloat(checkbox.dataset.maxScore);

            if (!card) {
                console.error('Criterion card not found for checkbox');
                return;
            }

            const checkboxes = card.querySelectorAll('.scale-checkbox:checked');
            let total = 0;
            checkboxes.forEach(cb => {
                total += parseFloat(cb.value);
            });

            if (total > maxScore) {
                checkbox.checked = false;
                alert(`El puntaje máximo para este criterio es ${maxScore} puntos`);
                return;
            }

            const scoreInput = card.querySelector('input.score-input');
            if (!scoreInput) {
                console.error('Score input not found for criterion:', criterionId);
                return;
            }

            scoreInput.value = total;

            const calculatedSpan = card.querySelector('.calculated-score');
            if (calculatedSpan) {
                calculatedSpan.textContent = total.toFixed(2);
            }

            this.calculateWeightedScores();
            this.autoSave(card, criterionId);
        },

        handleScoreChange(event) {
            const input = event.target;
            const card = input.closest('[data-criterion-id]');
            const criterionId = card.dataset.criterionId;
            const score = parseFloat(input.value);
            const min = parseFloat(input.dataset.min);
            const max = parseFloat(input.dataset.max);

            if (input.value && (isNaN(score) || score < min || score > max)) {
                input.classList.add('border-red-500');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.classList.remove('hidden');
                }
                return;
            } else {
                input.classList.remove('border-red-500');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.classList.add('hidden');
                }
            }

            this.calculateWeightedScores();
            this.autoSave(card, criterionId);
        },

        autoSave(card, criterionId) {
            clearTimeout(this.saveTimeout);

            this.saveTimeout = setTimeout(() => {
                const scoreInput = card.querySelector('input.score-input');
                const score = scoreInput ? parseFloat(scoreInput.value) || 0 : 0;
                const comments = card.querySelector('textarea[name*="comments"]')?.value || '';
                const evidenceInput = card.querySelector('textarea[name*="evidence"]');
                const evidence = evidenceInput ? evidenceInput.value : null;

                this.saveCriterionDetail(criterionId, score, comments, evidence);
            }, 1000);
        },

        async saveCriterionDetail(criterionId, score, comments, evidence) {
            if (this.csrfTokenExpired) {
                console.warn('Guardado omitido: CSRF token expirado');
                return;
            }

            const criterion = this.criteria.find(c => c.id == criterionId);
            if (criterion) {
                if (criterion.requires_comment && !comments) {
                    console.warn('Guardado omitido: El criterio requiere comentarios obligatorios');
                    return;
                }
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    console.error('No se pudo obtener el token CSRF');
                    this.csrfTokenExpired = true;
                    alert('Error: No se pudo obtener el token de seguridad. Por favor, recarga la página.');
                    return;
                }

                const minScore = parseFloat(criterion.min_score) || 0;
                const maxScore = parseFloat(criterion.max_score) || 100;

                if (score < minScore || score > maxScore) {
                    console.log('Guardado omitido: Score fuera del rango permitido');
                    return;
                }

                console.log('Guardando criterio:', criterionId, 'Score:', score);

                const bodyData = {
                    criterion_id: criterionId,
                    score: score,
                    comments: comments || null,
                    evidence: evidence || null
                };

                console.log('Datos a enviar:', bodyData);

                const response = await fetch('{{ route("evaluation.save-detail", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(bodyData)
                });

                if (!response.ok) {
                    if (response.status === 419) {
                        this.csrfTokenExpired = true;
                        console.warn('CSRF token expirado. La sesión ha caducado.');
                        alert('Tu sesión ha expirado. Por favor, recarga la página para continuar.');
                        return;
                    }

                    if (response.status === 422) {
                        const errorData = await response.json();
                        console.error('Error de validación 422:', errorData);
                        return;
                    }

                    const errorData = await response.json().catch(() => ({}));
                    console.error('Error del servidor:', errorData);
                    return;
                }

                const data = await response.json();
                console.log('✓ Criterio guardado:', criterionId);
                return data;
            } catch (error) {
                console.error('Error guardando criterio:', error);
            }
        },

        async saveDraft() {
            this.isSaving = true;

            try {
                const promises = [];
                document.querySelectorAll('[data-criterion-id]').forEach(card => {
                    const criterionId = card.dataset.criterionId;
                    const scoreInput = card.querySelector('input.score-input');
                    const score = scoreInput ? parseFloat(scoreInput.value) || 0 : 0;

                    const comments = card.querySelector('textarea[name*="comments"]')?.value || '';
                    const evidenceInput = card.querySelector('textarea[name*="evidence"]');
                    const evidence = evidenceInput ? evidenceInput.value : null;

                    promises.push(this.saveCriterionDetail(criterionId, score, comments, evidence));
                });

                await Promise.all(promises);

                this.$dispatch('notify', {
                    type: 'success',
                    message: 'Evaluación guardada como borrador'
                });
            } catch (error) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Error al guardar: ' + (error.response?.data?.message || 'Error desconocido')
                });
            } finally {
                this.isSaving = false;
            }
        },

        async submitEvaluation() {
            if (this.isDisqualified) {
                if (!confirm('Está a punto de enviar una evaluación con descalificación. ¿Está seguro?')) {
                    return;
                }
            } else {
                if (!confirm('¿Está seguro de enviar la evaluación? No podrá modificarla después.')) {
                    return;
                }
            }

            this.isSubmitting = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    alert('Error: No se pudo obtener el token de seguridad. Por favor, recarga la página.');
                    this.isSubmitting = false;
                    return;
                }

                const generalComments = document.querySelector('textarea[name="general_comments"]').value;

                const response = await fetch('{{ route("evaluation.submit", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        confirm: true,
                        general_comments: generalComments,
                        disqualified: this.isDisqualified,
                        disqualification_reason: this.disqualificationReason
                    })
                });

                if (!response.ok) {
                    if (response.status === 419) {
                        alert('Tu sesión ha expirado. Por favor, recarga la página para continuar.');
                        this.isSubmitting = false;
                        return;
                    }

                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || errorData.message || 'Error desconocido');
                }

                alert('Evaluación enviada exitosamente');
                window.location.href = '{{ route("evaluation.index") }}';
            } catch (error) {
                alert('Error: ' + error.message);
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
