@extends('layouts.app')

@section('title', 'Asignaciones de Evaluadores')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Asignaciones de Evaluadores</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestiona las asignaciones de jurados a postulaciones</p>
                </div>
                @can('assign-evaluators')
                <div class="flex space-x-3">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-bs-toggle="modal" data-bs-target="#manualAssignModal">
                        <i class="fas fa-user-plus mr-2"></i>
                        Asignar Manual
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" data-bs-toggle="modal" data-bs-target="#autoAssignModal">
                        <i class="fas fa-magic mr-2"></i>
                        Asignar Automático
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-clipboard-list text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Asignaciones</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completadas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 rounded-lg p-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Vencidas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['overdue'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Búsqueda</h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('evaluator-assignments.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria</label>
                        <select name="job_posting_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todas</option>
                            @foreach($jobPostings ?? [] as $posting)
                                <option value="{{ $posting->id }}" {{ request('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                    {{ $posting->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                        <select name="phase_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todas las fases</option>
                            @foreach($phases ?? [] as $phase)
                                <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                            <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Proceso</option>
                            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurado</label>
                        <input type="text" name="evaluator" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Buscar..." value="{{ request('evaluator') }}">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Asignaciones -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Listado de Asignaciones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurado Evaluador</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo/Rol</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Postulaciones</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Carga</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignments ?? [] as $assignment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-gavel text-indigo-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $assignment->juryMember->full_name ?? $assignment->evaluator->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $assignment->juryMember->email ?? $assignment->evaluator->email ?? '' }}</div>
                                        @if($assignment->juryMember && !$assignment->juryMember->training_completed)
                                            <div class="text-xs text-yellow-600 mt-1">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Sin capacitación
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($assignment->juryAssignment ?? false)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $assignment->juryAssignment->member_type->label() }}
                                        </span>
                                        @if($assignment->juryAssignment->role_in_jury)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $assignment->juryAssignment->role_in_jury->label() }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $assignment->application->jobPosting->title ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $assignment->application->jobPosting->code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $assignment->phase->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                                    {{ $assignment->application_count ?? 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($assignment->juryAssignment ?? false)
                                    <div class="text-sm">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-medium">{{ $assignment->juryAssignment->current_evaluations }}</span>
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-gray-600">Máx:</span>
                                            <span class="font-medium">{{ $assignment->juryAssignment->max_evaluations ?? '∞' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-{{ $assignment->juryAssignment->workload_percentage > 80 ? 'red' : 'green' }} h-2 rounded-full" style="width: {{ $assignment->juryAssignment->workload_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusColors = [
                                        'PENDING' => 'bg-yellow-100 text-yellow-800',
                                        'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                        'COMPLETED' => 'bg-green-100 text-green-800',
                                        'CANCELLED' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$assignment->status->value ?? 'PENDING'] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $assignment->status->label() ?? $assignment->status->value ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($assignment->deadline_at)
                                    <div class="text-sm">
                                        <span class="{{ $assignment->deadline_at->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $assignment->deadline_at->format('d/m/Y') }}
                                        </span>
                                        @if($assignment->deadline_at->isPast() && $assignment->status->value != 'COMPLETED')
                                            <div class="text-xs text-red-600 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Vencida
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">Sin límite</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-6">
                                    <div class="bg-indigo-600 h-6 rounded-full flex items-center justify-center text-xs text-white font-medium" style="width: {{ $assignment->progress_percentage ?? 0 }}%">
                                        {{ $assignment->progress_percentage ?? 0 }}%
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('evaluator-assignments.show', $assignment->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('assign-evaluators')
                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="confirmDelete('{{ $assignment->id }}')" title="Cancelar asignación">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-12">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay asignaciones</h3>
                                    <p class="text-gray-500 mb-4">Las asignaciones de evaluadores aparecerán aquí</p>
                                    @can('assign-evaluators')
                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-bs-toggle="modal" data-bs-target="#manualAssignModal">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Crear Asignación
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
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $assignments->links() }}
            </div>
            @endif
        </div>

        <!-- Carga de Trabajo por Jurado -->
        @if(isset($workloadStats) && $workloadStats->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Carga de Trabajo por Jurado</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo/Rol</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Asignadas</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pendientes</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Completadas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distribución</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($workloadStats as $stat)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $stat->evaluator_name }}</div>
                                    @if($stat->specialty)
                                        <div class="text-sm text-gray-500">{{ $stat->specialty }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stat->member_type)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $stat->member_type }}
                                        </span>
                                        @if($stat->role)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $stat->role }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $stat->total }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $stat->pending }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $stat->completed }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-yellow-400 h-4 rounded-full" style="width: {{ $stat->total > 0 ? ($stat->pending / $stat->total) * 100 : 0 }}%"></div>
                                    <div class="bg-green-500 h-4 rounded-full" style="width: {{ $stat->total > 0 ? ($stat->completed / $stat->total) * 100 : 0 }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $workloadPct = $stat->workload_percentage ?? 0;
                                @endphp
                                @if($workloadPct >= 100)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Sobrecargado
                                    </span>
                                @elseif($workloadPct >= 80)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Alta carga
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>
                                        Disponible
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Modal Asignación Manual -->
        <div class="modal fade" id="manualAssignModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gray-50">
                        <h5 class="modal-title text-lg font-medium text-gray-900">
                            <i class="fas fa-user-plus mr-2 text-indigo-600"></i>
                            Asignación Manual de Evaluador
                        </h5>
                        <button type="button" class="btn-close text-gray-400 hover:text-gray-500" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="manualAssignForm">
                        @csrf
                        <div class="modal-body p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria *</label>
                                    <select name="job_posting_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" id="modalJobPosting" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach($jobPostings ?? [] as $posting)
                                            <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fase *</label>
                                    <select name="phase_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach($phases ?? [] as $phase)
                                            <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Postulación *</label>
                                <select name="application_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" id="applicationSelect" required>
                                    <option value="">Primero seleccione una convocatoria...</option>
                                </select>
                                <small class="text-gray-500 text-sm mt-1 block">Se mostrarán solo las postulaciones de la convocatoria seleccionada</small>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jurado Evaluador *</label>
                                <select name="evaluator_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" id="evaluatorSelect" required>
                                    <option value="">Primero seleccione una postulación...</option>
                                </select>
                                <small class="text-gray-500 text-sm mt-1 block">Solo se muestran jurados asignados a la convocatoria sin conflictos</small>
                            </div>

                            <div id="evaluatorInfo" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 d-none">
                                <h6 class="text-blue-800 font-medium mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Información del Jurado
                                </h6>
                                <div id="evaluatorDetails" class="text-sm text-blue-700"></div>
                            </div>

                            <div id="conflictWarning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 d-none">
                                <h6 class="text-yellow-800 font-medium mb-2">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Advertencias
                                </h6>
                                <ul id="conflictList" class="text-sm text-yellow-700 list-disc list-inside"></ul>
                            </div>
                        </div>
                        <div class="modal-footer bg-gray-50 px-6 py-4">
                            <button type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="btnSubmitAssign">
                                <i class="fas fa-save mr-2"></i>
                                Asignar Evaluador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Asignación Automática -->
        <div class="modal fade" id="autoAssignModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-gray-50">
                        <h5 class="modal-title text-lg font-medium text-gray-900">
                            <i class="fas fa-magic mr-2 text-green-600"></i>
                            Asignación Automática
                        </h5>
                        <button type="button" class="btn-close text-gray-400 hover:text-gray-500" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="autoAssignForm">
                        @csrf
                        <div class="modal-body p-6">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria *</label>
                                <select name="job_posting_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($jobPostings ?? [] as $posting)
                                        <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fase *</label>
                                <select name="phase_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($phases ?? [] as $phase)
                                        <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h6 class="text-blue-800 font-medium mb-2">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Asignación Inteligente
                                </h6>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• Solo se asignarán jurados registrados en el módulo Jury</li>
                                    <li>• Se verificarán conflictos de interés automáticamente</li>
                                    <li>• La distribución será equitativa según la carga de trabajo</li>
                                    <li>• Se respetarán los límites de capacidad de cada jurado</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer bg-gray-50 px-6 py-4">
                            <button type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-magic mr-2"></i>
                                Asignar Automáticamente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>


</div>

<script>
// Cargar postulaciones al seleccionar convocatoria
document.getElementById('modalJobPosting')?.addEventListener('change', function() {
    const jobPostingId = this.value;
    const applicationSelect = document.getElementById('applicationSelect');
    const evaluatorSelect = document.getElementById('evaluatorSelect');

    if (!jobPostingId) {
        applicationSelect.innerHTML = '<option value="">Primero seleccione una convocatoria...</option>';
        evaluatorSelect.innerHTML = '<option value="">Primero seleccione una postulación...</option>';
        return;
    }

    applicationSelect.innerHTML = '<option value="">Cargando...</option>';

    fetch(`/api/applications?job_posting_id=${jobPostingId}`)
        .then(response => response.json())
        .then(data => {
            applicationSelect.innerHTML = '<option value="">Seleccione una postulación...</option>';
            data.data.forEach(app => {
                const option = document.createElement('option');
                option.value = app.id;
                option.textContent = `${app.code} - ${app.full_name} (${app.dni})`;
                applicationSelect.appendChild(option);
            });
        })
        .catch(() => {
            applicationSelect.innerHTML = '<option value="">Error al cargar</option>';
        });
});

// Cargar evaluadores disponibles al seleccionar postulación
document.getElementById('applicationSelect')?.addEventListener('change', function() {
    const applicationId = this.value;
    const evaluatorSelect = document.getElementById('evaluatorSelect');
    const evaluatorInfo = document.getElementById('evaluatorInfo');
    const conflictWarning = document.getElementById('conflictWarning');

    evaluatorInfo.classList.add('d-none');
    conflictWarning.classList.add('d-none');

    if (!applicationId) {
        evaluatorSelect.innerHTML = '<option value="">Primero seleccione una postulación...</option>';
        return;
    }

    evaluatorSelect.innerHTML = '<option value="">Cargando jurados disponibles...</option>';

    fetch(`/evaluator-assignments/available-evaluators?application_id=${applicationId}`)
        .then(response => response.json())
        .then(data => {
            evaluatorSelect.innerHTML = '<option value="">Seleccione un jurado...</option>';

            if (data.data.length === 0) {
                evaluatorSelect.innerHTML = '<option value="">No hay jurados disponibles</option>';
                return;
            }

            data.data.forEach(evaluator => {
                const option = document.createElement('option');
                option.value = evaluator.id;
                option.dataset.workload = evaluator.workload_percentage;
                option.dataset.specialty = evaluator.specialty || '';
                option.dataset.memberType = evaluator.member_type || '';
                option.dataset.role = evaluator.role || '';

                let text = `${evaluator.name}`;
                if (evaluator.member_type) text += ` - ${evaluator.member_type}`;
                if (evaluator.role) text += ` (${evaluator.role})`;
                text += ` - Carga: ${evaluator.workload_percentage}%`;

                if (evaluator.workload_percentage >= 100) {
                    option.disabled = true;
                    text += ' [SOBRECARGADO]';
                }

                option.textContent = text;
                evaluatorSelect.appendChild(option);
            });
        })
        .catch(() => {
            evaluatorSelect.innerHTML = '<option value="">Error al cargar jurados</option>';
        });
});

// Mostrar info del evaluador seleccionado
document.getElementById('evaluatorSelect')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const evaluatorInfo = document.getElementById('evaluatorInfo');
    const evaluatorDetails = document.getElementById('evaluatorDetails');

    if (!this.value) {
        evaluatorInfo.classList.add('d-none');
        return;
    }

    const workload = selectedOption.dataset.workload || 0;
    const specialty = selectedOption.dataset.specialty || 'N/A';
    const memberType = selectedOption.dataset.memberType || 'N/A';
    const role = selectedOption.dataset.role || 'N/A';

    evaluatorDetails.innerHTML = `
        <div class="grid grid-cols-2 gap-2">
            <div><strong>Especialidad:</strong> ${specialty}</div>
            <div><strong>Tipo:</strong> ${memberType}</div>
            <div><strong>Rol:</strong> ${role}</div>
            <div><strong>Carga actual:</strong> ${workload}%</div>
        </div>
    `;

    evaluatorInfo.classList.remove('d-none');
});

// Submit asignación manual
document.getElementById('manualAssignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btnSubmit = document.getElementById('btnSubmitAssign');

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Asignando...';

    fetch('/evaluator-assignments', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error al asignar evaluador');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-save mr-2"></i>Asignar Evaluador';
        }
    })
    .catch(error => {
        alert('Error al asignar evaluador');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-save mr-2"></i>Asignar Evaluador';
    });
});

// Submit asignación automática
document.getElementById('autoAssignForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!confirm('¿Está seguro de realizar la asignación automática? Se distribuirán las evaluaciones entre los jurados disponibles.')) {
        return;
    }

    const formData = new FormData(this);

    fetch('/evaluator-assignments/auto-assign', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Asignación automática completada exitosamente');
            window.location.reload();
        } else {
            alert(data.message || 'Error en la asignación automática');
        }
    })
    .catch(error => {
        alert('Error en la asignación automática');
    });
});

// Confirmar eliminación
function confirmDelete(assignmentId) {
    if (confirm('¿Está seguro de cancelar esta asignación?')) {
        fetch(`/evaluator-assignments/${assignmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error al cancelar la asignación');
            }
        });
    }
}
</script>
@endsection
