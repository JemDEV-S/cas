@extends('layouts.admin')

@section('title', 'Nueva Convocatoria')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb Premium --}}
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('jobposting.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">
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
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
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
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl mb-8 shadow-lg">
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

        {{-- Formulario Premium --}}
        <form action="{{ route('jobposting.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Información Básica --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
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
                        {{-- Título --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Título de la Convocatoria
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   placeholder="Ej: Asistente Administrativo - Recursos Humanos"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title') border-red-500 @enderror">
                            @error('title')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Año --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Año
                            </label>
                            <input type="number"
                                   name="year"
                                   value="{{ old('year', now()->year) }}"
                                   min="2000"
                                   max="{{ now()->year + 1 }}"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('year') border-red-500 @enderror">
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Se auto-completa con el año actual
                            </p>
                            @error('year')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código (auto-generado) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Código
                            </label>
                            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 font-medium flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Se generará automáticamente
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Ejemplo: CONV-{{ now()->year }}-001</p>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  placeholder="Descripción detallada de la convocatoria..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Fechas --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Fechas Tentativas
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Fecha de Inicio --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Fecha de Inicio
                            </label>
                            <input type="date"
                                   name="start_date"
                                   value="{{ old('start_date') }}"
                                   min="{{ now()->format('Y-m-d') }}"
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
                                   name="end_date"
                                   value="{{ old('end_date') }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cronograma Automático --}}
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Cronograma Automático
                    </h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-start space-x-3 p-4 bg-green-50 rounded-xl border-2 border-green-200">
                        <input type="checkbox"
                               name="create_schedule"
                               id="create_schedule"
                               value="1"
                               {{ old('create_schedule') ? 'checked' : '' }}
                               onchange="toggleScheduleDate()"
                               class="mt-1 h-5 w-5 text-green-600 rounded focus:ring-green-500">
                        <div>
                            <label for="create_schedule" class="font-bold text-gray-800 cursor-pointer">
                                Generar cronograma de 12 fases automáticamente
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                Se creará el cronograma completo con las 12 fases estándar del proceso CAS con duración predeterminada
                            </p>
                        </div>
                    </div>

                    <div id="schedule_date_container" class="hidden">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Fecha de inicio del cronograma
                        </label>
                        <input type="date"
                               name="schedule_start_date"
                               value="{{ old('schedule_start_date', now()->addDays(7)->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                        <p class="text-xs text-gray-500 mt-1">Las fases se programarán secuencialmente desde esta fecha</p>
                    </div>
                </div>
            </div>

            {{-- Información Adicional --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border-2 border-blue-200">
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
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between bg-white rounded-2xl shadow-xl p-6">
                <a href="{{ route('jobposting.dashboard') }}"
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl font-bold hover:from-gray-200 hover:to-gray-300 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Cancelar</span>
                </a>
                <button type="submit"
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

@push('scripts')
<script>
function toggleScheduleDate() {
    const checkbox = document.getElementById('create_schedule');
    const container = document.getElementById('schedule_date_container');

    if (checkbox.checked) {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

// Ejecutar al cargar la página si ya está checked
document.addEventListener('DOMContentLoaded', function() {
    toggleScheduleDate();
});
</script>
@endpush
@endsection
