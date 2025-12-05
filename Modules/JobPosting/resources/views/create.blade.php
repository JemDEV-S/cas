@extends('layouts.admin')

@section('title', 'Nueva Convocatoria')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Estilos personalizados para Select2 */
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        padding: 8px 12px !important;
        border: 2px solid #d1d5db !important;
        border-radius: 12px !important;
        transition: all 0.3s ease !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        border: 2px solid #3b82f6 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .select2-results__option {
        padding: 10px 15px !important;
        transition: background-color 0.2s ease !important;
    }

    .select2-results__option--highlighted {
        background-color: #3b82f6 !important;
    }

    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }

    /* Input con estado de validación */
    .input-valid {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
    }

    .input-invalid {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
    }

    /* Preview de código */
    #code-preview {
        transition: all 0.3s ease;
    }

    #code-preview.loading {
        opacity: 0.5;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    /* Toggle switch mejorado */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 34px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #3b82f6;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb Premium --}}
        <nav class="mb-6 animate-fade-in-up">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('jobposting.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li>
                    <a href="{{ route('jobposting.list') }}" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">
                        Convocatorias
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 font-semibold">Nueva Convocatoria</li>
            </ol>
        </nav>

        {{-- Header Premium --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8 animate-fade-in-up">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-10">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-1">Nueva Convocatoria CAS</h1>
                        <p class="text-blue-100">Complete la información para crear una nueva convocatoria</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Errores de validación --}}
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl mb-8 shadow-lg animate-fade-in-up">
            <div class="flex items-start">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-bold mb-2">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Mensajes flash --}}
        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-xl mb-8 shadow-lg animate-fade-in-up">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl mb-8 shadow-lg animate-fade-in-up">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        {{-- Formulario Premium --}}
        <form id="jobPostingForm" action="{{ route('jobposting.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Información Básica --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Información Básica
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Año --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Año <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   id="year"
                                   name="year"
                                   value="{{ old('year', now()->year) }}"
                                   min="2000"
                                   max="{{ now()->year + 5 }}"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('year') border-red-500 @enderror">
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                El código se generará automáticamente
                            </p>
                            @error('year')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código (Preview automático) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Código (Vista Previa)
                            </label>
                            <div id="code-preview" class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 font-bold flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span id="code-text">CONV-{{ now()->year }}-001</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Se generará al crear la convocatoria</p>
                        </div>
                    </div>

                    {{-- Título --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Título de la Convocatoria <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="title"
                               name="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Ej: Asistente Administrativo - Recursos Humanos"
                               maxlength="255"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title') border-red-500 @enderror">
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-gray-500">
                                <span id="title-count">0</span>/255 caracteres
                            </p>
                            @error('title')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="4"
                                  maxlength="5000"
                                  placeholder="Descripción detallada de la convocatoria..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-gray-500">
                                <span id="description-count">0</span>/5000 caracteres
                            </p>
                            @error('description')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Fechas de la Convocatoria
                    </h3>
                </div>

                <div class="p-6">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Nota:</strong> Estas fechas son tentativas y pueden ser modificadas. No hay restricciones de fechas pasadas o futuras.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Fecha de Inicio --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date"
                                   id="start_date"
                                   name="start_date"
                                   value="{{ old('start_date') }}"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha de Fin --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Fecha de Fin
                            </label>
                            <input type="date"
                                   id="end_date"
                                   name="end_date"
                                   value="{{ old('end_date') }}"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cronograma Automático --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Cronograma Automático
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Toggle Switch Premium --}}
                    <div class="flex items-start space-x-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200">
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="auto_schedule"
                                   id="auto_schedule"
                                   value="1"
                                   {{ old('auto_schedule') ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="flex-1">
                            <label for="auto_schedule" class="font-bold text-gray-800 cursor-pointer block mb-1">
                                Generar cronograma de 12 fases automáticamente
                            </label>
                            <p class="text-sm text-gray-600">
                                Se creará el cronograma completo con las 12 fases estándar del proceso CAS con duración predeterminada
                            </p>
                            <div id="schedule-info" class="hidden mt-3 p-3 bg-white rounded-lg border border-green-300">
                                <p class="text-xs text-green-700 font-medium">
                                    ✅ Se generarán las siguientes fases automáticamente:
                                </p>
                                <ul class="text-xs text-gray-600 mt-2 space-y-1 ml-4">
                                    <li>• Aprobación de la Convocatoria</li>
                                    <li>• Publicación de la Convocatoria</li>
                                    <li>• Registro Virtual de Postulantes (2 días)</li>
                                    <li>• Publicación de postulantes APTOS</li>
                                    <li>• Presentación de CV documentado</li>
                                    <li>• Evaluación Curricular (3 días)</li>
                                    <li>• Y 6 fases más...</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Fecha de inicio del cronograma --}}
                    <div id="schedule_date_container" class="hidden transition-all duration-300">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Fecha de inicio del cronograma <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date"
                                   name="schedule_start_date"
                                   id="schedule_start_date"
                                   value="{{ old('schedule_start_date', now()->addDays(7)->format('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('schedule_start_date') border-red-500 @enderror">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Las fases se programarán secuencialmente desde esta fecha</p>
                        @error('schedule_start_date')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror

                        {{-- Botón de preview --}}
                        <button type="button" id="preview-schedule-btn" class="mt-4 w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Vista Previa del Cronograma</span>
                        </button>

                        {{-- Modal de preview --}}
                        <div id="preview-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 flex items-center justify-between">
                                    <h3 class="text-xl font-bold text-white flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Vista Previa del Cronograma
                                    </h3>
                                    <button type="button" id="close-preview" class="text-white hover:text-gray-200 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div id="preview-content" class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                                    <div class="flex items-center justify-center py-12">
                                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información Adicional --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border-2 border-blue-200 animate-fade-in-up">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Información Adicional
                </h3>
                <div class="space-y-2 text-sm text-gray-700">
                    <div class="flex items-start">
                        <span class="text-blue-600 mr-2 text-lg">✓</span>
                        <span>La convocatoria se creará en estado <strong class="text-blue-600">BORRADOR</strong></span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 mr-2 text-lg">✓</span>
                        <span>Podrás editar toda la información antes de publicarla</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 mr-2 text-lg">✓</span>
                        <span>El código se generará automáticamente (CONV-{{ now()->year }}-###)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 mr-2 text-lg">✓</span>
                        <span>Si activas el cronograma automático, se crearán las 12 fases estándar del CAS</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 mr-2 text-lg">✓</span>
                        <span>Las fechas son <strong>completamente flexibles</strong> - puedes usar fechas pasadas o futuras</span>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between bg-white rounded-2xl shadow-xl p-6 animate-fade-in-up">
                <a href="{{ route('jobposting.dashboard') }}"
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl font-bold hover:from-gray-200 hover:to-gray-300 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Cancelar</span>
                </a>
                <button type="submit"
                        id="submit-btn"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Crear Convocatoria</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery (requerido para Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    /* ========================================================================
     * CONFIGURACIÓN GLOBAL
     * ======================================================================== */

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    /* ========================================================================
     * CONTADOR DE CARACTERES
     * ======================================================================== */

    // Título
    $('#title').on('input', function() {
        const length = $(this).val().length;
        $('#title-count').text(length);

        // Validación visual
        if (length > 0 && length <= 255) {
            $(this).removeClass('input-invalid').addClass('input-valid');
        } else if (length > 255) {
            $(this).removeClass('input-valid').addClass('input-invalid');
        } else {
            $(this).removeClass('input-valid input-invalid');
        }
    });

    // Descripción
    $('#description').on('input', function() {
        const length = $(this).val().length;
        $('#description-count').text(length);

        if (length <= 5000) {
            $(this).removeClass('input-invalid').addClass('input-valid');
        } else {
            $(this).removeClass('input-valid').addClass('input-invalid');
        }
    });

    /* ========================================================================
     * GENERACIÓN AUTOMÁTICA DE CÓDIGO
     * ======================================================================== */

    $('#year').on('change', function() {
        const year = $(this).val();

        if (!year) return;

        // Mostrar loading
        $('#code-preview').addClass('loading');
        $('#code-text').text('Generando...');

        // Llamar a la API
        $.get('{{ route('generate.job-posting-code') }}', { year: year })
            .done(function(response) {
                if (response.success) {
                    $('#code-text').text(response.code);
                    $('#code-preview').removeClass('loading');

                    // Animación de éxito
                    $('#code-preview').addClass('input-valid');
                    setTimeout(() => {
                        $('#code-preview').removeClass('input-valid');
                    }, 1000);
                }
            })
            .fail(function() {
                $('#code-text').text('Error al generar');
                $('#code-preview').removeClass('loading');
            });
    });

    // Generar código inicial al cargar
    $('#year').trigger('change');

    /* ========================================================================
     * TOGGLE DE CRONOGRAMA AUTOMÁTICO
     * ======================================================================== */

    $('#auto_schedule').on('change', function() {
        const isChecked = $(this).is(':checked');

        if (isChecked) {
            $('#schedule_date_container').removeClass('hidden').addClass('animate-fade-in-up');
            $('#schedule-info').removeClass('hidden').addClass('animate-fade-in-up');
            $('#schedule_start_date').attr('required', true);
        } else {
            $('#schedule_date_container').addClass('hidden');
            $('#schedule-info').addClass('hidden');
            $('#schedule_start_date').attr('required', false);
        }
    });

    // Inicializar estado
    if ($('#auto_schedule').is(':checked')) {
        $('#schedule_date_container').removeClass('hidden');
        $('#schedule-info').removeClass('hidden');
    }

    /* ========================================================================
     * VALIDACIÓN DE FECHAS
     * ======================================================================== */

    $('#start_date, #end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        if (startDate && endDate) {
            if (new Date(endDate) < new Date(startDate)) {
                $('#end_date').addClass('input-invalid');
                alert('⚠️ La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                $('#end_date').removeClass('input-invalid').addClass('input-valid');
            }
        }
    });

    /* ========================================================================
     * VISTA PREVIA DEL CRONOGRAMA
     * ======================================================================== */

    $('#preview-schedule-btn').on('click', function() {
        const startDate = $('#schedule_start_date').val();

        if (!startDate) {
            alert('⚠️ Por favor, selecciona una fecha de inicio para el cronograma');
            return;
        }

        // Mostrar modal
        $('#preview-modal').removeClass('hidden');

        // Mostrar loading
        $('#preview-content').html(`
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-gray-600">Generando vista previa...</p>
                </div>
            </div>
        `);

        // Llamar a la API
        $.post('{{ route('preview.schedule') }}', {
            start_date: startDate,
            _token: csrfToken
        })
        .done(function(response) {
            if (response.success) {
                renderPreview(response);
            }
        })
        .fail(function(xhr) {
            $('#preview-content').html(`
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-red-600 font-medium">Error al generar vista previa</p>
                    <p class="text-gray-500 text-sm mt-2">${xhr.responseJSON?.message || 'Intenta nuevamente'}</p>
                </div>
            `);
        });
    });

    function renderPreview(response) {
        let html = `
            <div class="mb-6">
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Fecha de inicio</p>
                        <p class="text-lg font-bold text-blue-600">${formatDate(response.start_date)}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700">Fecha de fin estimada</p>
                        <p class="text-lg font-bold text-purple-600">${formatDate(response.end_date)}</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h4 class="text-lg font-bold text-gray-800 mb-2">Cronograma Completo (${response.total_phases} fases)</h4>
                <p class="text-sm text-gray-600">Este es el cronograma que se generará automáticamente:</p>
            </div>

            <div class="space-y-3">
        `;

        response.preview.forEach((phase, index) => {
            const colors = [
                'from-blue-400 to-blue-500',
                'from-indigo-400 to-indigo-500',
                'from-purple-400 to-purple-500',
                'from-pink-400 to-pink-500'
            ];
            const color = colors[index % colors.length];

            html += `
                <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            ${phase.phase_number}
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 truncate">${phase.phase_name}</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                ${formatDate(phase.start_date)}
                            </span>
                            <span class="text-gray-400">→</span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                ${formatDate(phase.end_date)}
                            </span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-medium text-gray-700">${phase.duration_days} día${phase.duration_days > 1 ? 's' : ''}</p>
                    </div>
                </div>
            `;
        });

        html += `
            </div>

            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-700 font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Al crear la convocatoria, este cronograma se generará automáticamente
                </p>
            </div>
        `;

        $('#preview-content').html(html);
    }

    function formatDate(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('es-ES', options);
    }

    // Cerrar modal
    $('#close-preview').on('click', function() {
        $('#preview-modal').addClass('hidden');
    });

    // Cerrar modal al hacer click fuera
    $('#preview-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
        }
    });

    /* ========================================================================
     * VALIDACIÓN DEL FORMULARIO
     * ======================================================================== */

    $('#jobPostingForm').on('submit', function(e) {
        let valid = true;
        let errors = [];

        // Validar título
        const title = $('#title').val().trim();
        if (!title || title.length < 5) {
            valid = false;
            errors.push('El título debe tener al menos 5 caracteres');
            $('#title').addClass('input-invalid');
        }

        // Validar año
        const year = $('#year').val();
        if (!year || year < 2000) {
            valid = false;
            errors.push('Selecciona un año válido');
            $('#year').addClass('input-invalid');
        }

        // Validar fechas si ambas están presentes
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        if (startDate && endDate) {
            if (new Date(endDate) < new Date(startDate)) {
                valid = false;
                errors.push('La fecha de fin debe ser posterior a la fecha de inicio');
                $('#end_date').addClass('input-invalid');
            }
        }

        // Validar cronograma automático
        if ($('#auto_schedule').is(':checked')) {
            const scheduleStartDate = $('#schedule_start_date').val();
            if (!scheduleStartDate) {
                valid = false;
                errors.push('Debes seleccionar una fecha de inicio para el cronograma');
                $('#schedule_start_date').addClass('input-invalid');
            }
        }

        // Mostrar errores
        if (!valid) {
            e.preventDefault();

            let errorHtml = `
                <div class="fixed top-4 right-4 bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl shadow-xl z-50 max-w-md animate-fade-in-up">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-bold mb-2">Por favor, corrige los siguientes errores:</p>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                ${errors.map(err => `<li>${err}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(errorHtml);

            // Eliminar alerta después de 5 segundos
            setTimeout(() => {
                $('.fixed.top-4').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            return false;
        }

        // Deshabilitar botón de submit
        $('#submit-btn').prop('disabled', true).html(`
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Creando...</span>
        `);
    });

    /* ========================================================================
     * ATAJOS DE TECLADO
     * ======================================================================== */

    $(document).on('keydown', function(e) {
        // ESC para cerrar modal
        if (e.key === 'Escape') {
            $('#preview-modal').addClass('hidden');
        }

        // Ctrl+Enter para enviar formulario
        if (e.ctrlKey && e.key === 'Enter') {
            $('#jobPostingForm').submit();
        }
    });

    /* ========================================================================
     * INICIALIZACIÓN FINAL
     * ======================================================================== */

    console.log('✅ Formulario de convocatoria inicializado correctamente');
    console.log('📋 Funcionalidades activas:');
    console.log('  - Generación automática de código');
    console.log('  - Validación en tiempo real');
    console.log('  - Vista previa de cronograma');
    console.log('  - Fechas sin restricciones');
    console.log('  - Contador de caracteres');

});
</script>
@endpush
