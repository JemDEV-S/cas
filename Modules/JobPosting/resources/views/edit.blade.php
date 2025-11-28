@extends('layouts.admin')

@section('title', 'Editar Convocatoria')

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
                <li>
                    <a href="{{ route('jobposting.show', $jobPosting) }}" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">
                        {{ $jobPosting->code }}
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 font-semibold">Editar</li>
            </ol>
        </nav>

        {{-- Header Premium para EDIT --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-1">Editar Convocatoria</h1>
                            <p class="text-orange-100">{{ $jobPosting->code }} - {{ $jobPosting->title }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-bold bg-white/20 backdrop-blur-md text-white border border-white/30">
                            {{ $jobPosting->status->iconEmoji() }} {{ $jobPosting->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerta si no puede editarse --}}
        @if(!$jobPosting->canBeEdited())
        <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl shadow-2xl p-6 mb-6 text-white">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold mb-1">Convocatoria no editable</h3>
                    <p>Esta convocatoria no puede ser editada en su estado actual: <strong>{{ $jobPosting->status->label() }}</strong></p>
                </div>
            </div>
        </div>
        @endif

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
        <form action="{{ route('jobposting.update', $jobPosting) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

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
                                   value="{{ old('title', $jobPosting->title) }}"
                                   required
                                   {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                   placeholder="Ej: Asistente Administrativo - Recursos Humanos"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                            @error('title')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        {{-- Código (no editable) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Código
                            </label>
                            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 font-medium flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ $jobPosting->code }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">El código no puede modificarse</p>
                        </div>

                        {{-- Año (no editable) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Año
                            </label>
                            <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 font-medium flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $jobPosting->year }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1">El año no puede modificarse</p>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea name="description"
                                  rows="4"
                                  {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                  placeholder="Descripción detallada de la convocatoria..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ old('description', $jobPosting->description) }}</textarea>
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
                                   value="{{ old('start_date', $jobPosting->start_date?->format('Y-m-d')) }}"
                                   {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('start_date') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100 cursor-not-allowed' : '' }}">
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
                                   value="{{ old('end_date', $jobPosting->end_date?->format('Y-m-d')) }}"
                                   {{ !$jobPosting->canBeEdited() ? 'readonly' : '' }}
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('end_date') border-red-500 @enderror {{ !$jobPosting->canBeEdited() ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                            @error('end_date')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estado y Cronograma --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Estado de la Convocatoria --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Estado Actual
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl border-2 border-amber-200">
                            <div>
                                <div class="text-sm text-amber-700 font-medium">Estado</div>
                                <div class="text-lg font-bold text-amber-900 flex items-center mt-1">
                                    <span class="mr-2 text-xl">{{ $jobPosting->status->iconEmoji() }}</span>
                                    {{ $jobPosting->status->label() }}
                                </div>
                            </div>
                            @if($jobPosting->canBeEdited())
                            <div class="text-sm">
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-bold">
                                    ✅ Puede editarse
                                </span>
                            </div>
                            @else
                            <div class="text-sm">
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-bold">
                                    ❌ No editable
                                </span>
                            </div>
                            @endif
                        </div>

                        @if($jobPosting->published_at)
                        <div class="p-4 bg-blue-50 rounded-xl border-2 border-blue-200">
                            <div class="text-sm text-blue-800 font-medium">
                                <strong>📅 Publicada:</strong> {{ $jobPosting->published_at->format('d/m/Y H:i') }}
                                @if($jobPosting->publisher)
                                por {{ $jobPosting->publisher->name }}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($jobPosting->finalized_at)
                        <div class="p-4 bg-green-50 rounded-xl border-2 border-green-200">
                            <div class="text-sm text-green-800 font-medium">
                                <strong>✅ Finalizada:</strong> {{ $jobPosting->finalized_at->format('d/m/Y H:i') }}
                                @if($jobPosting->finalizer)
                                por {{ $jobPosting->finalizer->name }}
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($jobPosting->cancelled_at)
                        <div class="p-4 bg-red-50 rounded-xl border-2 border-red-200">
                            <div class="text-sm text-red-800 font-medium">
                                <strong>❌ Cancelada:</strong> {{ $jobPosting->cancelled_at->format('d/m/Y H:i') }}
                                @if($jobPosting->canceller)
                                por {{ $jobPosting->canceller->name }}
                                @endif
                            </div>
                            @if($jobPosting->cancellation_reason)
                            <div class="mt-2 text-sm text-red-700">
                                <strong>📝 Motivo:</strong> {{ $jobPosting->cancellation_reason }}
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Cronograma --}}
                @if($jobPosting->schedules->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Cronograma
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4 text-center mb-4">
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border-2 border-purple-200">
                                <div class="text-2xl font-bold text-purple-600">{{ $jobPosting->schedules->count() }}</div>
                                <div class="text-sm text-purple-700 font-medium">Fases totales</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border-2 border-green-200">
                                <div class="text-2xl font-bold text-green-600">{{ $jobPosting->schedules->where('status', 'COMPLETED')->count() }}</div>
                                <div class="text-sm text-green-700 font-medium">Completadas</div>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border-2 border-blue-200">
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($jobPosting->getProgressPercentage(), 1) }}%</div>
                                <div class="text-sm text-blue-700 font-medium">Progreso</div>
                            </div>
                        </div>
                        <a href="{{ route('jobposting.schedule', $jobPosting) }}"
                           class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-bold hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Gestionar Cronograma</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between bg-white rounded-2xl shadow-xl p-6">
                <a href="{{ route('jobposting.show', $jobPosting) }}"
                   class="px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl font-bold hover:from-gray-200 hover:to-gray-300 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Cancelar</span>
                </a>

                @if($jobPosting->canBeEdited())
                <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl font-bold hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Guardar Cambios</span>
                </button>
                @else
                <div class="px-8 py-3 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-xl font-bold cursor-not-allowed flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>No se puede editar</span>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
