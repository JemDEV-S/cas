@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('jobposting.show', $jobPosting) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a la convocatoria
            </a>
        </div>

        {{-- Título --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📅 Cronograma</h1>
                    <p class="text-gray-600 mt-1">{{ $jobPosting->code }} - {{ $jobPosting->title }}</p>
                </div>
                <div>
                    <span class="px-4 py-2 rounded-lg font-semibold text-white {{ $jobPosting->status->badgeClass() }}">
                        {{ $jobPosting->status->icon() }} {{ $jobPosting->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Progreso --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold text-gray-800">Progreso General</h3>
                <span class="text-lg font-bold text-blue-600">{{ $progress['percentage'] }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-3">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-4 rounded-full transition-all" 
                     style="width: {{ $progress['percentage'] }}%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600">
                <span>✅ Completadas: {{ $progress['completed'] }}</span>
                <span>▶️ En progreso: {{ $progress['in_progress'] }}</span>
                <span>⏳ Pendientes: {{ $progress['pending'] }}</span>
            </div>
        </div>

        {{-- Timeline de fases --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">📋 Fases del Proceso</h3>
            
            @if($timeline && count($timeline) > 0)
            <div class="space-y-4">
                @foreach($timeline as $item)
                <div class="flex items-start space-x-4 p-4 rounded-lg border-2 {{ $item['status'] === 'COMPLETED' ? 'border-green-200 bg-green-50' : ($item['status'] === 'IN_PROGRESS' ? 'border-blue-200 bg-blue-50' : 'border-gray-200') }}">
                    
                    {{-- Icono --}}
                    <div class="flex-shrink-0">
                        @if($item['status'] === 'COMPLETED')
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center text-white text-xl">
                            ✓
                        </div>
                        @elseif($item['status'] === 'IN_PROGRESS')
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl animate-pulse">
                            ▶
                        </div>
                        @else
                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                            {{ $item['phase_number'] }}
                        </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $item['phase_name'] }}</h4>
                                <div class="text-sm text-gray-600 mt-1">
                                    📅 {{ \Carbon\Carbon::parse($item['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item['end_date'])->format('d/m/Y') }}
                                    ({{ $item['duration_days'] }} días)
                                </div>
                                @if($item['location'])
                                <div class="text-sm text-gray-600">📍 {{ $item['location'] }}</div>
                                @endif
                                @if($item['responsible_unit'])
                                <div class="text-sm text-gray-600">🏢 {{ $item['responsible_unit'] }}</div>
                                @endif
                            </div>
                            <div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white {{ 
                                    $item['status'] === 'COMPLETED' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 
                                    ($item['status'] === 'IN_PROGRESS' ? 'bg-gradient-to-r from-blue-500 to-indigo-600' : 
                                    ($item['is_delayed'] ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gray-400')) 
                                }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Retrasada --}}
                        @if($item['is_delayed'] && $item['status'] !== 'COMPLETED')
                        <div class="mt-2 text-sm text-red-600 font-semibold">
                            ⚠️ Fase retrasada
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12 text-gray-500">
                <div class="text-6xl mb-4">📅</div>
                <p class="text-lg">No hay fases programadas en el cronograma</p>
                <p class="text-sm mt-2">Agrega fases para comenzar a gestionar el proceso</p>
            </div>
            @endif
        </div>

        {{-- Fase actual destacada --}}
        @if($currentPhase)
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-xl p-6 mt-6 text-white">
            <h3 class="text-xl font-bold mb-2">▶️ Fase Actual en Progreso</h3>
            <p class="text-2xl font-semibold">{{ $currentPhase->phase->name }}</p>
            <div class="mt-3 text-blue-100">
                📅 {{ $currentPhase->start_date->format('d/m/Y') }} - {{ $currentPhase->end_date->format('d/m/Y') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection