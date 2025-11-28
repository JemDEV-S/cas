@extends('layouts.admin')

@section('title', $jobPosting->title)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

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
                <li class="text-gray-900 font-semibold">{{ $jobPosting->code }}</li>
            </ol>
        </nav>

        {{-- Card Principal --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
            {{-- Header con estado --}}
            <div class="px-6 py-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <div class="text-2xl font-bold text-white">{{ substr($jobPosting->year, -2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-medium">{{ $jobPosting->code }}</div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $jobPosting->title }}</h1>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $jobPosting->status->badgeClass() }}">
                            {{ $jobPosting->status->icon() }} {{ $jobPosting->status->label() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Barra de progreso --}}
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progreso General</span>
                    <span class="text-sm font-medium text-blue-600">{{ number_format($progress['percentage'], 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $progress['percentage'] }}%"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-600">
                    <span>{{ $progress['completed'] }} completadas</span>
                    <span>{{ $progress['in_progress'] }} en progreso</span>
                    <span>{{ $progress['pending'] }} pendientes</span>
                </div>
            </div>

            {{-- Contenido principal --}}
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Información principal --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Descripción --}}
                        @if($jobPosting->description)
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-3">Descripción</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $jobPosting->description }}</p>
                        </div>
                        @endif

                        {{-- Fase actual --}}
                        @if($currentPhase)
                        <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 mb-2">Fase Actual</h3>
                                    <p class="text-lg font-medium text-gray-900">{{ $currentPhase->phase->name }}</p>
                                    <p class="text-sm text-gray-600 mt-2">
                                        {{ $currentPhase->start_date->format('d/m/Y') }} - {{ $currentPhase->end_date->format('d/m/Y') }}
                                    </p>
                                    @if($currentPhase->responsibleUnit)
                                    <p class="text-sm text-gray-600">
                                        {{ $currentPhase->responsibleUnit->name }}
                                    </p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-lg font-medium text-sm">
                                    Fase {{ $currentPhase->phase->phase_number }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Próxima fase --}}
                        @if($nextPhase)
                        <div class="bg-amber-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-2">Próxima Fase</h3>
                            <p class="text-lg font-medium text-gray-900">{{ $nextPhase->phase->name }}</p>
                            <p class="text-sm text-gray-600 mt-2">
                                Inicia: {{ $nextPhase->start_date->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif

                        {{-- Fases retrasadas --}}
                        @if($delayedPhases->isNotEmpty())
                        <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                            <h3 class="text-base font-semibold text-red-800 mb-3">Fases Retrasadas ({{ $delayedPhases->count() }})</h3>
                            <div class="space-y-2">
                                @foreach($delayedPhases as $delayed)
                                <div class="flex items-center justify-between bg-white rounded-lg p-3">
                                    <span class="text-sm font-medium text-gray-800">{{ $delayed->phase->name }}</span>
                                    <span class="text-xs text-red-600">Venció: {{ $delayed->end_date->format('d/m/Y') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Cronograma resumido --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-800">Cronograma</h3>
                                <a href="{{ route('jobposting.schedule.edit', $jobPosting) }}" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                                        Ver cronograma completo
                                </a>
                            </div>
                            <div class="space-y-3">
                                @foreach($jobPosting->schedules->take(5) as $schedule)
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($schedule->status->value === 'COMPLETED')
                                        <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-medium">
                                            ✓
                                        </div>
                                        @elseif($schedule->status->value === 'IN_PROGRESS')
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                            ▶
                                        </div>
                                        @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-medium">
                                            {{ $schedule->phase->phase_number }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-800">{{ $schedule->phase->name }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full {{ $schedule->status->badgeClass() }} text-white">
                                                {{ $schedule->status->icon() }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $schedule->start_date->format('d/m/Y') }} - {{ $schedule->end_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="space-y-6">
                        
                        {{-- Información general --}}
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">Información</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Código</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->code }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Año</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->year }}</div>
                                </div>
                                @if($jobPosting->start_date)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $jobPosting->start_date->format('d/m/Y') }}
                                </span>
                                @endif
                                @if($jobPosting->end_date)
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $jobPosting->end_date->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">Acciones</h3>
                            <div class="space-y-3">
                                @if($jobPosting->canBeEdited())
                                <a href="{{ route('jobposting.edit', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-amber-500 text-white rounded-lg text-center font-medium hover:bg-amber-600 transition-colors">
                                    Editar
                                </a>
                                @endif

                                @if($jobPosting->canBePublished())
                                <form action="{{ route('jobposting.publish', $jobPosting) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors"
                                            onclick="return confirm('¿Publicar convocatoria?')">
                                        Publicar
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('jobposting.schedule.edit', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-purple-500 text-white rounded-lg text-center font-medium hover:bg-purple-600 transition-colors">
                                    Ver Cronograma
                                </a>

                                <a href="{{ route('jobposting.history', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-cyan-500 text-white rounded-lg text-center font-medium hover:bg-cyan-600 transition-colors">
                                    Ver Historial
                                </a>

                                <form action="{{ route('jobposting.clone', $jobPosting) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors"
                                            onclick="return confirm('¿Clonar convocatoria?')">
                                        Clonar
                                    </button>
                                </form>

                                @if($jobPosting->canBeCancelled())
                                <button onclick="showCancelModal()" 
                                        class="w-full px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors">
                                    Cancelar
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Zona de peligro --}}
                        @if($jobPosting->canBeEdited())
                        <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                            <h3 class="text-sm font-medium text-red-800 mb-3">Zona de Peligro</h3>
                            <form action="{{ route('jobposting.destroy', $jobPosting) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                                        onclick="return confirm('¿Está seguro de eliminar esta convocatoria?')">
                                    Eliminar Convocatoria
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Barra de Progreso Premium --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Progreso General del Proceso
                </h3>
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    {{ number_format($progress['percentage'], 1) }}%
                </span>
            </div>
            <div class="w-full bg-gradient-to-r from-gray-100 to-gray-200 rounded-full h-4 shadow-inner">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full transition-all duration-1000 shadow-lg"
                     style="width: {{ $progress['percentage'] }}%"></div>
            </div>
            <div class="flex justify-between mt-4 text-sm font-medium">
                <span class="text-green-600 flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    {{ $progress['completed'] }} Completadas
                </span>
                <span class="text-blue-600 flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    {{ $progress['in_progress'] }} En Progreso
                </span>
                <span class="text-gray-600 flex items-center">
                    <div class="w-3 h-3 bg-gray-400 rounded-full mr-2"></div>
                    {{ $progress['pending'] }} Pendientes
                </span>
            </div>
        </div>

        {{-- Contenido Principal --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Columna Principal --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Descripción --}}
                @if($jobPosting->description)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Descripción
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 leading-relaxed text-lg">{{ $jobPosting->description }}</p>
                    </div>
                </div>
                @endif

                {{-- Fase Actual --}}
                @if($currentPhase)
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl shadow-xl p-6 border-2 border-green-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-xl text-white font-bold text-lg mr-4">
                                    ▶
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Fase Actual</h3>
                                    <p class="text-green-600 font-medium">En progreso</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900 mb-2">{{ $currentPhase->phase->name }}</p>
                                    <div class="flex items-center space-x-6 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $currentPhase->start_date->format('d/m/Y') }} - {{ $currentPhase->end_date->format('d/m/Y') }}
                                        </span>
                                        @if($currentPhase->responsibleUnit)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            {{ $currentPhase->responsibleUnit->name }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-xl font-bold text-sm">
                                Fase {{ $currentPhase->phase->phase_number }}
                            </span>
                            <div class="mt-2 text-3xl font-bold text-green-600">
                                {{ $currentPhase->getDaysRemaining() }}d
                            </div>
                            <div class="text-xs text-green-600">restantes</div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Próxima Fase --}}
                @if($nextPhase)
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-xl p-6 border-2 border-amber-200">
                    <div class="flex items-center mb-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-amber-500 rounded-xl text-white font-bold text-lg mr-4">
                            ⏭️
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Próxima Fase</h3>
                            <p class="text-amber-600 font-medium">Programada</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-2xl font-bold text-gray-900">{{ $nextPhase->phase->name }}</p>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Inicia: {{ $nextPhase->start_date->format('d/m/Y') }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Finaliza: {{ $nextPhase->end_date->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Fases Retrasadas --}}
                @if($delayedPhases->isNotEmpty())
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl shadow-xl p-6 border-2 border-red-200">
                    <div class="flex items-center mb-4">
                        <div class="flex items-center justify-center w-12 h-12 bg-red-500 rounded-xl text-white font-bold text-lg mr-4">
                            ⚠️
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-red-800">Fases Retrasadas</h3>
                            <p class="text-red-600 font-medium">{{ $delayedPhases->count() }} fases requieren atención</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        @foreach($delayedPhases as $delayed)
                        <div class="flex items-center justify-between bg-white rounded-xl p-4 border-2 border-red-200">
                            <div>
                                <span class="font-bold text-gray-800">{{ $delayed->phase->name }}</span>
                                <div class="text-sm text-red-600 mt-1">
                                    Venció: {{ $delayed->end_date->format('d/m/Y') }}
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm font-bold">
                                Retrasada
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Cronograma Resumido --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                Cronograma
                            </h3>
                            <a href="{{ route('jobposting.schedule.edit', $jobPosting) }}"
                               class="px-4 py-2 bg-white/20 backdrop-blur-md text-white rounded-xl font-bold hover:bg-white/30 transition-all shadow-lg">
                                Ver Completo
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($jobPosting->schedules->take(5) as $schedule)
                            <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl hover:shadow-md transition-all">
                                <div class="flex-shrink-0">
                                    @if($schedule->status->value === 'COMPLETED')
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-lg font-bold shadow-lg">
                                        ✓
                                    </div>
                                    @elseif($schedule->status->value === 'IN_PROGRESS')
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-lg font-bold shadow-lg animate-pulse">
                                        ▶
                                    </div>
                                    @else
                                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center text-white text-lg font-bold shadow-lg">
                                        {{ $schedule->phase->phase_number }}
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-gray-800">{{ $schedule->phase->name }}</span>
                                        <span class="px-3 py-1 rounded-full text-sm font-bold text-white {{ $schedule->status->badgeClass() }}">
                                            {{ $schedule->status->icon() }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $schedule->start_date->format('d/m/Y') }} - {{ $schedule->end_date->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-8">

                {{-- Información General --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Información General
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Código</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->code }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Año</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->year }}</span>
                        </div>
                        @if($jobPosting->start_date)
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Fecha Inicio</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->start_date->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($jobPosting->end_date)
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Fecha Fin</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->end_date->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($jobPosting->published_at)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Publicada</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->published_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($jobPosting->publisher)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
                            <span class="text-sm font-medium text-gray-700">Publicada por</span>
                            <span class="font-bold text-gray-900">{{ $jobPosting->publisher->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Acciones
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($jobPosting->canBeEdited())
                        <a href="{{ route('jobposting.edit', $jobPosting) }}"
                           class="block w-full px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl font-bold hover:from-amber-600 hover:to-orange-700 transition-all shadow-lg hover:shadow-xl text-center">
                            ✏️ Editar Convocatoria
                        </a>
                        @endif

                        @if($jobPosting->canBePublished())
                        <form action="{{ route('jobposting.publish', $jobPosting) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl"
                                    onclick="return confirm('¿Está seguro de publicar esta convocatoria?')">
                                📢 Publicar
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('jobposting.schedule.edit', $jobPosting) }}"
                           class="block w-full px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl font-bold hover:from-purple-600 hover:to-pink-700 transition-all shadow-lg hover:shadow-xl text-center">
                            📅 Gestionar Cronograma
                        </a>

                        <a href="{{ route('jobposting.history', $jobPosting) }}"
                           class="block w-full px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-xl font-bold hover:from-cyan-600 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl text-center">
                            📊 Ver Historial
                        </a>

                        <form action="{{ route('jobposting.clone', $jobPosting) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold hover:from-green-600 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl"
                                    onclick="return confirm('¿Está seguro de clonar esta convocatoria?')">
                                🐑 Clonar Convocatoria
                            </button>
                        </form>

                        @if($jobPosting->canBeCancelled())
                        <button onclick="showCancelModal()"
                                class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl font-bold hover:from-red-600 hover:to-pink-700 transition-all shadow-lg hover:shadow-xl">
                            ❌ Cancelar Convocatoria
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Zona de Peligro --}}
                @if($jobPosting->canBeEdited())
                <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-2xl shadow-xl p-6 border-2 border-red-200">
                    <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Zona de Peligro
                    </h3>
                    <form action="{{ route('jobposting.destroy', $jobPosting) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-bold hover:from-red-700 hover:to-red-800 transition-all shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
                                onclick="return confirm('¿ESTÁ SEGURO DE ELIMINAR ESTA CONVOCATORIA? Esta acción no se puede deshacer.')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Eliminar Convocatoria</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal de Cancelación Premium --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-auto">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-2xl mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Cancelar Convocatoria</h3>
            <p class="text-gray-600">Esta acción no se puede deshacer</p>
        </div>
        <form action="{{ route('jobposting.cancel', $jobPosting) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Motivo de cancelación *</label>
                <textarea name="cancellation_reason"
                          rows="4"
                          required
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                          placeholder="Explique detalladamente el motivo de la cancelación..."></textarea>
            </div>
            <div class="flex space-x-4">
                <button type="button"
                        onclick="hideCancelModal()"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl font-bold hover:from-gray-200 hover:to-gray-300 transition-all shadow-md">
                    Cerrar
                </button>
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl font-bold hover:from-red-600 hover:to-red-700 transition-all shadow-lg">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}
function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCancelModal();
    }
});
</script>
@endpush
@endsection
