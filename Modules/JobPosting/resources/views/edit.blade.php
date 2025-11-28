@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('jobposting.show', $jobPosting) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a la convocatoria
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

        {{-- Alerta si no puede editarse --}}
        @if(!$jobPosting->canBeEdited())
        <div class="bg-orange-50 text-orange-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span>Esta convocatoria no puede ser editada en su estado actual: <strong>{{ $jobPosting->status->label() }}</strong></span>
        </div>
        @endif

        {{-- Formulario --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            {{-- Header del formulario --}}
            <div class="px-6 py-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Editar Convocatoria</h1>
                        <p class="text-gray-500 text-sm mt-1">{{ $jobPosting->code }}</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $jobPosting->status->badgeClass() }}">
                            {{ $jobPosting->status->icon() }} {{ $jobPosting->status->label() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('jobposting.update', $jobPosting) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

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
                                       value="{{ old('title', $jobPosting->title) }}"
                                       required
                                       {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100' : '' }}">
                                @error('title')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Código (no editable) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Código
                                </label>
                                <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-medium">
                                    {{ $jobPosting->code }}
                                </div>
                                <p class="text-xs text-gray-500 mt-1">El código no puede modificarse</p>
                            </div>

                            {{-- Año (no editable) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Año
                                </label>
                                <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-medium">
                                    {{ $jobPosting->year }}
                                </div>
                                <p class="text-xs text-gray-500 mt-1">El año no puede modificarse</p>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="description" 
                                      rows="4"
                                      {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100' : '' }}">{{ old('description', $jobPosting->description) }}</textarea>
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
                                       value="{{ old('start_date', $jobPosting->start_date?->format('Y-m-d')) }}"
                                       {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100' : '' }}">
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
                                       value="{{ old('end_date', $jobPosting->end_date?->format('Y-m-d')) }}"
                                       {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100' : '' }}">
                                @error('end_date')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Estado actual --}}
                    <div>
                        <h3 class="text-base font-medium text-gray-800 mb-4 pb-2 border-b border-gray-100">Estado de la Convocatoria</h3>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <div class="text-sm text-gray-600">Estado Actual</div>
                                <div class="text-lg font-medium text-gray-900 flex items-center mt-1">
                                    <span class="mr-2">{{ $jobPosting->status->icon() }}</span>
                                    {{ $jobPosting->status->label() }}
                                </div>
                            </div>
                            @if($jobPosting->canBeEdited())
                            <div class="text-sm">
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
                                    Puede editarse
                                </span>
                            </div>
                            @else
                            <div class="text-sm">
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-medium">
                                    No editable
                                </span>
                            </div>
                            @endif
                        </div>

                        @if($jobPosting->published_at)
                        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="text-sm text-blue-800">
                                <strong>Publicada:</strong> {{ $jobPosting->published_at->format('d/m/Y H:i') }}
                                @if($jobPosting->publisher)
                                por {{ $jobPosting->publisher->name }}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($jobPosting->finalized_at)
                        <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="text-sm text-green-800">
                                <strong>Finalizada:</strong> {{ $jobPosting->finalized_at->format('d/m/Y H:i') }}
                                @if($jobPosting->finalizer)
                                por {{ $jobPosting->finalizer->name }}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($jobPosting->cancelled_at)
                        <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="text-sm text-red-800">
                                <strong>Cancelada:</strong> {{ $jobPosting->cancelled_at->format('d/m/Y H:i') }}
                                @if($jobPosting->canceller)
                                por {{ $jobPosting->canceller->name }}
                                @endif
                            </div>
                            @if($jobPosting->cancellation_reason)
                            <div class="mt-2 text-sm text-red-700">
                                <strong>Motivo:</strong> {{ $jobPosting->cancellation_reason }}
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Cronograma --}}
                    @if($jobPosting->schedules->isNotEmpty())
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-medium text-gray-800">Cronograma</h3>
                            <a href="{{ route('jobposting.schedule.edit', $jobPosting) }}" 
                               class="px-4 py-2 bg-purple-500 text-white rounded-lg text-sm font-medium hover:bg-purple-600 transition-colors">
                                Gestionar Cronograma
                            </a>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-2xl font-medium text-gray-900">{{ $jobPosting->schedules->count() }}</div>
                                <div class="text-sm text-gray-600">Fases totales</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-2xl font-medium text-green-600">{{ $jobPosting->schedules->where('status', 'COMPLETED')->count() }}</div>
                                <div class="text-sm text-gray-600">Completadas</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-2xl font-medium text-blue-600">{{ number_format($jobPosting->getProgressPercentage(), 1) }}%</div>
                                <div class="text-sm text-gray-600">Progreso</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('jobposting.show', $jobPosting) }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                        Cancelar
                    </a>
                    
                    @if($jobPosting->canBeEdited())
                    <button type="submit" 
                            class="px-6 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600 transition-colors">
                        Guardar Cambios
                    </button>
                    @else
                    <div class="px-6 py-2 bg-gray-300 text-gray-600 rounded-lg font-medium cursor-not-allowed">
                        No se puede editar en este estado
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection