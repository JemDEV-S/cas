@extends('layouts.app')

@section('title', 'Distribución de Evaluaciones')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100" x-data="assignmentManager()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-distribute-spacing-horizontal text-indigo-600 mr-3"></i>
                        Distribución de Postulaciones
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">Asigna postulaciones equitativamente entre jurados de la convocatoria</p>
                </div>
                @can('assign-evaluators')
                <div class="flex space-x-3">
                    <a href="{{ route('evaluator-assignments.applications') }}"
                       class="inline-flex items-center px-5 py-3 bg-white border-2 border-indigo-600 rounded-lg shadow-sm text-sm font-semibold text-indigo-600 hover:bg-indigo-50 transition-all duration-150">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Ver Postulaciones
                    </a>
                    <button
                        @click="showDistributionModal = true"
                        class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-150">
                        <i class="fas fa-magic mr-2"></i>
                        Distribución Automática
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Asignaciones</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-4">
                        <i class="fas fa-clipboard-list text-indigo-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-4">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Completadas</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Vencidas</p>
                        <p class="text-3xl font-bold text-red-600">{{ $stats['overdue'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter text-gray-600 mr-2"></i>
                        Filtros de Búsqueda
                    </h3>
                    <button @click="resetFilters()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="fas fa-redo mr-1"></i>
                        Limpiar filtros
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('evaluator-assignments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                            Convocatoria
                        </label>
                        <select name="job_posting_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">Todas las convocatorias</option>
                            @foreach($jobPostings ?? [] as $posting)
                                <option value="{{ $posting->id }}" {{ request('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                    {{ $posting->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-layer-group text-gray-400 mr-1"></i>
                            Fase
                        </label>
                        <select name="phase_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">Todas las fases</option>
                            @foreach($phases ?? [] as $phase)
                                <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-flag text-gray-400 mr-1"></i>
                            Estado
                        </label>
                        <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">Todos los estados</option>
                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                            <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Proceso</option>
                            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completada</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-gray-700 to-gray-800 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white hover:from-gray-800 hover:to-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 transition-all">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Workload Distribution Cards -->
        @if(isset($workloadStats) && $workloadStats->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-gray-600 mr-2"></i>
                    Distribución de Carga por Jurado
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($workloadStats as $stat)
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-lg border border-gray-200 p-5 hover:shadow-lg transition-all duration-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <div class="bg-indigo-100 rounded-full p-3 mr-3">
                                    <i class="fas fa-user-tie text-indigo-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $stat->evaluator_name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $stat->email }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $stat->role }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Total asignaciones:</span>
                                <span class="font-bold text-gray-900">{{ $stat->total }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Pendientes:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $stat->pending }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Completadas:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $stat->completed }}
                                </span>
                            </div>

                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-medium text-gray-600">Progreso</span>
                                    <span class="text-xs font-bold text-gray-900">{{ $stat->completion_rate }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500"
                                         style="width: {{ $stat->completion_rate }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Asignaciones por Fase -->
        @if(isset($assignments) && $assignments->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-layer-group text-gray-600 mr-2"></i>
                    Asignaciones por Fase
                </h3>
            </div>
            <div class="p-6">
                @php
                    $assignmentsByPhase = $assignments->groupBy('phase_id');
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($assignmentsByPhase as $phaseId => $phaseAssignments)
                        @php
                            $phase = $phaseAssignments->first()->phase;
                            $totalPhase = $phaseAssignments->count();
                            $pendingPhase = $phaseAssignments->where('status', 'PENDING')->count();
                            $inProgressPhase = $phaseAssignments->where('status', 'IN_PROGRESS')->count();
                            $completedPhase = $phaseAssignments->where('status', 'COMPLETED')->count();
                            $progressPercentage = $totalPhase > 0 ? round(($completedPhase / $totalPhase) * 100) : 0;
                        @endphp
                        <div class="bg-gradient-to-br from-white to-purple-50 rounded-lg border-2 border-purple-200 p-5 hover:shadow-xl transition-all duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-gray-900 text-lg">{{ $phase->name ?? 'Sin fase' }}</h4>
                                <div class="bg-purple-100 rounded-full p-2">
                                    <i class="fas fa-clipboard-check text-purple-600"></i>
                                </div>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-list-check text-gray-400 mr-2 w-4"></i>
                                        Total:
                                    </span>
                                    <span class="font-bold text-gray-900">{{ $totalPhase }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-clock text-yellow-500 mr-2 w-4"></i>
                                        Pendientes:
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $pendingPhase }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-spinner text-blue-500 mr-2 w-4"></i>
                                        En progreso:
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $inProgressPhase }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2 w-4"></i>
                                        Completadas:
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $completedPhase }}
                                    </span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-purple-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-medium text-gray-600">Progreso de fase</span>
                                    <span class="text-xs font-bold text-purple-700">{{ $progressPercentage }}%</span>
                                </div>
                                <div class="w-full bg-purple-100 rounded-full h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500"
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('evaluator-assignments.index', ['phase_id' => $phaseId]) }}"
                                   class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                    <i class="fas fa-filter mr-2"></i>
                                    Ver asignaciones
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Assignments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-gray-600 mr-2"></i>
                        Asignaciones Activas
                    </h3>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">{{ $assignments->total() }}</span> asignaciones totales
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jurado</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Postulación</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Convocatoria</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Fase</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignments ?? [] as $assignment)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-11 w-11 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center shadow-sm">
                                        <i class="fas fa-user-tie text-white"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $assignment->user->getFullNameAttribute() ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $assignment->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $assignment->application->code ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $assignment->application->applicant->full_name ?? 'Sin postulante' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($assignment->application->jobPosting->title ?? 'N/A', 40) }}</div>
                                <div class="text-xs text-gray-500">{{ $assignment->application->jobPosting->code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $assignment->phase->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusConfig = [
                                        'PENDING' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
                                        'IN_PROGRESS' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-spinner'],
                                        'COMPLETED' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
                                        'CANCELLED' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
                                        'REASSIGNED' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => 'fa-exchange-alt'],
                                    ];
                                    $config = $statusConfig[$assignment->status->value ?? 'PENDING'] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question'];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                    <i class="fas {{ $config['icon'] }} mr-1.5"></i>
                                    {{ $assignment->status->label() ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $assignment->assignment_type == 'AUTOMATIC' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas {{ $assignment->assignment_type == 'AUTOMATIC' ? 'fa-magic' : 'fa-hand-pointer' }} mr-1.5"></i>
                                    {{ $assignment->assignment_type == 'AUTOMATIC' ? 'Automático' : 'Manual' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('evaluator-assignments.show', $assignment->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors text-xs font-medium"
                                       title="Ver detalles">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver
                                    </a>
                                    @can('assign-evaluators')
                                    @if($assignment->isActive())
                                    <button type="button"
                                            @click="cancelAssignment('{{ $assignment->id }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium"
                                            title="Cancelar">
                                        <i class="fas fa-times mr-1"></i>
                                        Cancelar
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center">
                                    <div class="bg-gray-100 rounded-full p-6 mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-5xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay asignaciones</h3>
                                    <p class="text-gray-500 mb-6 max-w-md">Comienza distribuyendo postulaciones entre los jurados asignados a la convocatoria</p>
                                    @can('assign-evaluators')
                                    <button
                                        @click="showDistributionModal = true"
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                        <i class="fas fa-magic mr-2"></i>
                                        Distribuir Ahora
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($assignments) && $assignments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $assignments->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de Distribución Automática -->
    <div x-show="showDistributionModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        @keydown.escape.window="showDistributionModal = false">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showDistributionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="showDistributionModal = false"
                aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showDistributionModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showDistributionModal = false"
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative z-10">

                <form @submit.prevent="distributeApplications()">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                                    <i class="fas fa-magic text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white" id="modal-title">
                                        Distribución Automática
                                    </h3>
                                    <p class="text-green-100 text-sm mt-1">Asignación equitativa round-robin</p>
                                </div>
                            </div>
                            <button type="button"
                                    @click="showDistributionModal = false"
                                    class="text-white hover:text-green-100 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6 space-y-5">
                        <!-- Convocatoria -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-briefcase text-gray-400 mr-2"></i>
                                Convocatoria *
                            </label>
                            <select x-model="distribution.job_posting_id"
                                    @change="loadJuryStats()"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                                <option value="">Seleccione una convocatoria...</option>
                                @foreach($jobPostings ?? [] as $posting)
                                    <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fase -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-layer-group text-gray-400 mr-2"></i>
                                Fase de Evaluación *
                            </label>
                            <select x-model="distribution.phase_id"
                                    @change="loadDistributionMetrics()"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                                <option value="">Seleccione una fase...</option>
                                @foreach($phases ?? [] as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Métricas de Distribución -->
                        <div x-show="metrics.loaded" x-cloak class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-5">
                            <h4 class="text-indigo-900 font-semibold mb-4 flex items-center">
                                <i class="fas fa-chart-pie mr-2"></i>
                                Estado Actual de Postulaciones
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Total Elegibles</p>
                                            <p class="text-2xl font-bold text-gray-900" x-text="metrics.total_eligible"></p>
                                        </div>
                                        <div class="bg-gray-100 rounded-full p-3">
                                            <i class="fas fa-users text-gray-600"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Disponibles</p>
                                            <p class="text-2xl font-bold text-green-600" x-text="metrics.available_to_assign"></p>
                                        </div>
                                        <div class="bg-green-100 rounded-full p-3">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">En Progreso</p>
                                            <p class="text-2xl font-bold text-blue-600" x-text="metrics.with_evaluation_in_progress"></p>
                                        </div>
                                        <div class="bg-blue-100 rounded-full p-3">
                                            <i class="fas fa-spinner text-blue-600"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-3 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">Completadas</p>
                                            <p class="text-2xl font-bold text-purple-600" x-text="metrics.with_evaluation_completed"></p>
                                        </div>
                                        <div class="bg-purple-100 rounded-full p-3">
                                            <i class="fas fa-check-double text-purple-600"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mensaje de advertencia si no hay disponibles -->
                            <div x-show="metrics.available_to_assign === 0 && metrics.total_eligible > 0"
                                 class="mt-4 bg-yellow-100 border border-yellow-300 rounded-lg p-3">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    <p class="text-sm text-yellow-800 font-medium">
                                        No hay postulaciones disponibles para asignar. Todas ya tienen evaluación asignada, en progreso o completada.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox"
                                    x-model="distribution.only_unassigned"
                                    class="mt-1 rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <span class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Solo postulaciones sin asignar</span>
                                    <span class="block text-xs text-gray-600 mt-1">Excluye postulaciones con asignación activa o con evaluaciones en progreso/completadas</span>
                                </span>
                            </label>
                        </div>

                        <!-- Información del algoritmo -->
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-5">
                            <h4 class="text-green-900 font-semibold mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Cómo funciona la distribución
                            </h4>
                            <ul class="text-sm text-green-800 space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-600 mr-2 mt-0.5"></i>
                                    <span>Obtiene postulaciones <strong>ELEGIBLES</strong> sin asignación ni evaluación</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-shield-halved text-green-600 mr-2 mt-0.5"></i>
                                    <span>Excluye postulaciones con <strong>evaluaciones en progreso o completadas</strong></span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-users text-green-600 mr-2 mt-0.5"></i>
                                    <span>Obtiene todos los jurados <strong>ACTIVOS</strong> asignados</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-arrows-rotate text-green-600 mr-2 mt-0.5"></i>
                                    <span>Distribuye <strong>equitativamente</strong> usando algoritmo round-robin</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-user-shield text-green-600 mr-2 mt-0.5"></i>
                                    <span>Verifica <strong>conflictos de interés</strong> automáticamente</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Preview de jurados (si hay convocatoria seleccionada) -->
                        <div x-show="juryPreview.length > 0" x-cloak class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-users text-gray-500 mr-2"></i>
                                Jurados disponibles (<span x-text="juryPreview.length"></span>)
                            </h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="juror in juryPreview" :key="juror.id">
                                    <div class="flex items-center justify-between text-sm bg-white px-3 py-2 rounded-md">
                                        <span class="font-medium text-gray-900" x-text="juror.firts_name"></span>
                                        <span class="text-xs text-gray-500" x-text="'Carga: ' + juror.workload"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                        <button type="button"
                                @click="showDistributionModal = false"
                                :disabled="isDistributing"
                                class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="isDistributing || (metrics.loaded && metrics.available_to_assign === 0)"
                                :class="(isDistributing || (metrics.loaded && metrics.available_to_assign === 0)) ? 'opacity-50 cursor-not-allowed' : ''"
                                class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white border border-transparent rounded-lg shadow-md hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all font-semibold">
                            <i :class="isDistributing ? 'fas fa-spinner fa-spin' : 'fas fa-magic'" class="mr-2"></i>
                            <span x-text="isDistributing ? 'Distribuyendo...' : (metrics.loaded && metrics.available_to_assign === 0 ? 'Sin postulaciones disponibles' : 'Distribuir Ahora')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function assignmentManager() {
    return {
        showDistributionModal: false,
        isDistributing: false,
        distribution: {
            job_posting_id: '',
            phase_id: '',
            only_unassigned: true
        },
        juryPreview: [],
        metrics: {
            loaded: false,
            total_eligible: 0,
            without_assignment: 0,
            with_assignment_no_evaluation: 0,
            with_evaluation_in_progress: 0,
            with_evaluation_completed: 0,
            available_to_assign: 0,
        },

        async loadJuryStats() {
            if (!this.distribution.job_posting_id) {
                this.juryPreview = [];
                this.metrics.loaded = false;
                return;
            }

            // Aquí podrías hacer una petición AJAX para obtener los jurados
            // Por ahora simulamos
            this.juryPreview = [
                { id: 1, name: 'Jurado ejemplo', workload: 5 }
            ];

            // Si también hay fase seleccionada, cargar métricas
            if (this.distribution.phase_id) {
                await this.loadDistributionMetrics();
            }
        },

        async loadDistributionMetrics() {
            if (!this.distribution.job_posting_id || !this.distribution.phase_id) {
                this.metrics.loaded = false;
                return;
            }

            try {
                const url = new URL('{{ route('evaluator-assignments.distribution-metrics') }}');
                url.searchParams.append('job_posting_id', this.distribution.job_posting_id);
                url.searchParams.append('phase_id', this.distribution.phase_id);

                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.metrics = {
                        loaded: true,
                        ...data.data
                    };
                } else {
                    console.error('Error loading metrics:', data.message);
                    this.metrics.loaded = false;
                }
            } catch (error) {
                console.error('Error loading distribution metrics:', error);
                this.metrics.loaded = false;
            }
        },

        async distributeApplications() {
            if (!this.distribution.job_posting_id || !this.distribution.phase_id) {
                alert('Por favor complete todos los campos requeridos');
                return;
            }

            // Verificar si hay postulaciones disponibles
            if (this.metrics.loaded && this.metrics.available_to_assign === 0) {
                alert('No hay postulaciones disponibles para asignar. Todas las postulaciones elegibles ya tienen asignación o evaluación.');
                return;
            }

            let confirmMessage = '¿Está seguro de realizar la distribución automática?';
            if (this.metrics.loaded) {
                confirmMessage += `\n\nSe asignarán ${this.metrics.available_to_assign} postulaciones disponibles equitativamente entre todos los jurados.`;
            } else {
                confirmMessage += '\n\nSe asignarán las postulaciones equitativamente entre todos los jurados disponibles.';
            }

            if (!confirm(confirmMessage)) {
                return;
            }

            this.isDistributing = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('job_posting_id', this.distribution.job_posting_id);
            formData.append('phase_id', this.distribution.phase_id);
            formData.append('only_unassigned', this.distribution.only_unassigned ? '1' : '0');

            try {
                const response = await fetch('{{ route('evaluator-assignments.auto-assign') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    let message = data.message || 'Distribución automática completada exitosamente';

                    // Agregar detalles de la distribución si están disponibles
                    if (data.data) {
                        message += `\n\n✅ Asignaciones exitosas: ${data.data.success || 0}`;

                        if (data.data.conflicts > 0) {
                            message += `\n⚠️ Conflictos resueltos: ${data.data.conflicts} (se asignó otro jurado automáticamente)`;
                        }

                        if (data.data.unassignable > 0) {
                            message += `\n❌ Sin evaluador disponible: ${data.data.unassignable} (todos los jurados tienen conflictos)`;
                        }

                        if (data.data.errors > 0) {
                            message += `\n⛔ Otros errores: ${data.data.errors}`;
                        }
                    }

                    alert(message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error en la distribución automática');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al realizar la distribución automática');
            } finally {
                this.isDistributing = false;
            }
        },

        async cancelAssignment(assignmentId) {
            if (!confirm('¿Está seguro de cancelar esta asignación?')) {
                return;
            }

            try {
                const url = '{{ route('evaluator-assignments.destroy', ':id') }}'.replace(':id', assignmentId);
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al cancelar la asignación');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cancelar la asignación');
            }
        },

        resetFilters() {
            window.location.href = '{{ route("evaluator-assignments.index") }}';
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
