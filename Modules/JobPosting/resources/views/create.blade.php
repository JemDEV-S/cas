@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('jobposting.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a convocatorias
            </a>
        </div>

        {{-- Errores de validación --}}
        @if($errors->any())
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-medium mb-1">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Formulario --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            {{-- Header del formulario --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <h1 class="text-xl font-semibold text-gray-800">Nueva Convocatoria CAS</h1>
                <p class="text-gray-500 text-sm mt-1">Complete la información básica de la convocatoria</p>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('jobposting.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    {{-- Información Básica --}}
                    <div>
                        <h3 class="text-base font-medium text-gray-800 mb-4 pb-2 border-b border-gray-100">Información Básica</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Título --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Título de la Convocatoria *
                                </label>
                                <input type="text" 
                                       name="title" 
                                       value="{{ old('title') }}"
                                       required
                                       placeholder="Ej: Asistente Administrativo - RRHH"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror">
                                @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Año --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Año
                                </label>
                                <input type="number" 
                                       name="year" 
                                       value="{{ old('year', now()->year) }}"
                                       min="2000"
                                       max="{{ now()->year + 1 }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('year') border-red-500 @enderror">
                                @error('year')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Se auto-completa con el año actual</p>
                            </div>

                            {{-- Código (auto-generado) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Código
                                </label>
                                <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600">
                                    Se generará automáticamente
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Ej: CONV-{{ now()->year }}-001</p>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="description" 
                                      rows="4"
                                      placeholder="Descripción detallada de la convocatoria..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div>
                        <h3 class="text-base font-medium text-gray-800 mb-4 pb-2 border-b border-gray-100">Fechas Tentativas</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Fecha de Inicio --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de Inicio
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       value="{{ old('start_date') }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror">
                                @error('start_date')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Fecha de Fin --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha de Fin
                                </label>
                                <input type="date" 
                                       name="end_date" 
                                       value="{{ old('end_date') }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror">
                                @error('end_date')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Cronograma Automático --}}
                    <div>
                        <h3 class="text-base font-medium text-gray-800 mb-4 pb-2 border-b border-gray-100">Cronograma Automático</h3>
                        
                        <div class="flex items-start space-x-3 mb-4">
                            <input type="checkbox" 
                                   name="create_schedule" 
                                   id="create_schedule"
                                   value="1"
                                   {{ old('create_schedule') ? 'checked' : '' }}
                                   onchange="toggleScheduleDate()"
                                   class="mt-1 h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                            <div>
                                <label for="create_schedule" class="font-medium text-gray-800 cursor-pointer">
                                    Generar cronograma de 12 fases automáticamente
                                </label>
                                <p class="text-sm text-gray-500 mt-1">
                                    Se creará el cronograma completo con las 12 fases del proceso CAS con duración predeterminada
                                </p>
                            </div>
                        </div>

                        <div id="schedule_date_container" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha de inicio del cronograma
                            </label>
                            <input type="date" 
                                   name="schedule_start_date" 
                                   value="{{ old('schedule_start_date', now()->addDays(7)->format('Y-m-d')) }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Las fases se programarán secuencialmente desde esta fecha</p>
                        </div>
                    </div>

                    {{-- Información Adicional --}}
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-base font-medium text-gray-800 mb-3">Información Adicional</h3>
                        <div class="space-y-2 text-sm text-gray-700">
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">✓</span>
                                <span>La convocatoria se creará en estado <strong>BORRADOR</strong></span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">✓</span>
                                <span>Podrás editar toda la información antes de publicarla</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">✓</span>
                                <span>El código se generará automáticamente (CONV-{{ now()->year }}-###)</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-blue-600 mr-2">✓</span>
                                <span>Si activas el cronograma automático, se crearán las 12 fases estándar del CAS</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('jobposting.index') }}" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                        Crear Convocatoria
                    </button>
                </div>
            </form>
        </div>
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