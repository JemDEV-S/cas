@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-clipboard-check text-orange-500 mr-3"></i>
            Dashboard de Evaluaciones
        </h1>
        <p class="text-gray-600">Selecciona una convocatoria y fase para comenzar a evaluar</p>
    </div>

    @if($jobPostingsData->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-md p-12">
            <div class="flex flex-col items-center">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes asignaciones pendientes</h3>
                <p class="text-gray-500">Las asignaciones de evaluación aparecerán aquí cuando sean creadas</p>
            </div>
        </div>
    @else
        <!-- Grid de Cards de Convocatorias -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($jobPostingsData as $data)
                @php
                    $jobPosting = $data['job_posting'];
                    $phases = $data['phases'];
                    $totalPending = $data['total_pending'];
                    $totalCompleted = $data['total_completed'];
                    $totalAssignments = $data['total_assignments'];
                @endphp

                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow border border-gray-200">
                    <!-- Header del Card -->
                    <div class="px-6 py-4 bg-gradient-to-r from-orange-500 to-orange-600 border-b">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-white mb-1">
                                    {{ $jobPosting->title }}
                                </h3>
                                <p class="text-sm text-orange-100">
                                    <i class="fas fa-code mr-1"></i>
                                    {{ $jobPosting->code }}
                                </p>
                            </div>
                            <div class="ml-4">
                                <div class="bg-white bg-opacity-20 rounded-lg px-3 py-2 backdrop-blur-sm">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-white">{{ $totalAssignments }}</div>
                                        <div class="text-xs text-orange-100">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Resumen -->
                    <div class="px-6 py-4 bg-orange-50 border-b grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $totalPending }}</div>
                            <div class="text-xs text-gray-600">Pendientes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $totalCompleted }}</div>
                            <div class="text-xs text-gray-600">Completadas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-600">{{ count($phases) }}</div>
                            <div class="text-xs text-gray-600">Fases</div>
                        </div>
                    </div>

                    <!-- Fases -->
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-layer-group mr-2"></i>
                            Selecciona una fase para evaluar:
                        </label>

                        <div class="space-y-2">
                            @foreach($phases as $phaseId => $phaseData)
                                @php
                                    $phase = $phaseData['phase'];
                                    $phasePending = $phaseData['pending'];
                                    $phaseCompleted = $phaseData['completed'];
                                    $phaseOverdue = $phaseData['overdue'];
                                    $phaseTotal = $phaseData['total'];
                                @endphp

                                <a href="{{ route('evaluation.list', ['job_posting_id' => $jobPosting->id, 'phase_id' => $phaseId]) }}"
                                   class="block p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all group">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                                                    <i class="fas fa-clipboard-list text-purple-600"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-gray-900 group-hover:text-orange-600">
                                                        {{ $phase->name }}
                                                    </h4>
                                                    <p class="text-xs text-gray-500">{{ $phase->code }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stats de la Fase -->
                                        <div class="flex items-center gap-4 ml-4">
                                            @if($phaseOverdue > 0)
                                                <div class="flex items-center gap-1 px-2 py-1 bg-red-100 rounded text-red-700">
                                                    <i class="fas fa-exclamation-triangle text-xs"></i>
                                                    <span class="text-sm font-semibold">{{ $phaseOverdue }}</span>
                                                </div>
                                            @endif

                                            @if($phasePending > 0)
                                                <div class="flex items-center gap-1 px-2 py-1 bg-yellow-100 rounded text-yellow-700">
                                                    <i class="fas fa-hourglass-half text-xs"></i>
                                                    <span class="text-sm font-semibold">{{ $phasePending }}</span>
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-1 px-2 py-1 bg-green-100 rounded text-green-700">
                                                <i class="fas fa-check-circle text-xs"></i>
                                                <span class="text-sm font-semibold">{{ $phaseCompleted }}</span>
                                            </div>

                                            <div class="text-sm text-gray-500 font-medium">
                                                {{ $phaseTotal }} total
                                            </div>

                                            <i class="fas fa-arrow-right text-gray-400 group-hover:text-orange-500 group-hover:translate-x-1 transition-all"></i>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Footer del Card -->
                    <div class="px-6 py-3 bg-gray-50 border-t">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div>
                                <i class="far fa-calendar mr-1"></i>
                                Creado: {{ $jobPosting->created_at->format('d/m/Y') }}
                            </div>
                            <div>
                                @if($totalPending > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-tasks mr-1"></i>
                                        {{ $totalPending }} por evaluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-check-double mr-1"></i>
                                        Todas completadas
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
