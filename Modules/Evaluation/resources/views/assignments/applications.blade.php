@extends('layouts.app')

@section('title', 'Postulaciones Activas - Estado de Evaluación')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-clipboard-list text-indigo-600 mr-3"></i>
                        Postulaciones Activas
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">Vista completa del estado de evaluación de todas las postulaciones</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('evaluator-assignments.index') }}"
                       class="inline-flex items-center px-5 py-3 bg-white border-2 border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a Asignaciones
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-full p-3">
                        <i class="fas fa-users text-gray-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Con Asignación</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['with_assignment'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-user-check text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Sin Asignación</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['without_assignment'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-user-xmark text-orange-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Con Evaluación</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['with_evaluation'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-file-alt text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">En Progreso</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['evaluations_in_progress'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-spinner text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Completadas</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['evaluations_completed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-check-circle text-green-600"></i>
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
                        Filtros Avanzados
                    </h3>
                    <a href="{{ route('evaluator-assignments.applications') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="fas fa-redo mr-1"></i>
                        Limpiar filtros
                    </a>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('evaluator-assignments.applications') }}">
                    <!-- Text Search -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search text-gray-400 mr-1"></i>
                            Buscar por texto
                        </label>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Buscar por nombre, DNI o código de postulación..."
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>

                    <!-- Other Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                                Convocatoria
                            </label>
                            <select name="job_posting_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                <option value="">Todas las convocatorias</option>
                                @foreach($jobPostings ?? [] as $posting)
                                    <option value="{{ $posting->id }}" {{ request('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                        {{ Str::limit($posting->title, 40) }}
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
                            <i class="fas fa-user-tie text-gray-400 mr-1"></i>
                            Evaluador
                        </label>
                        <select name="evaluator_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">Todos los evaluadores</option>
                            @foreach($evaluators ?? [] as $evaluator)
                                <option value="{{ $evaluator->id }}" {{ request('evaluator_id') == $evaluator->id ? 'selected' : '' }}>
                                    {{ $evaluator->first_name }} {{ $evaluator->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clipboard-check text-gray-400 mr-1"></i>
                            Estado Evaluación
                        </label>
                        <select name="evaluation_status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                            <option value="">Todos los estados</option>
                            <option value="WITHOUT_EVALUATION" {{ request('evaluation_status') == 'WITHOUT_EVALUATION' ? 'selected' : '' }}>Sin evaluación</option>
                            <option value="ASSIGNED" {{ request('evaluation_status') == 'ASSIGNED' ? 'selected' : '' }}>Asignada</option>
                            <option value="IN_PROGRESS" {{ request('evaluation_status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Progreso</option>
                            <option value="SUBMITTED" {{ request('evaluation_status') == 'SUBMITTED' ? 'selected' : '' }}>Enviada</option>
                            <option value="MODIFIED" {{ request('evaluation_status') == 'MODIFIED' ? 'selected' : '' }}>Modificada</option>
                        </select>
                    </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-gray-700 to-gray-800 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white hover:from-gray-800 hover:to-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 transition-all">
                                <i class="fas fa-search mr-2"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-table text-gray-600 mr-2"></i>
                        Listado de Postulaciones
                    </h3>
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">{{ $applications->total() }}</span> postulaciones encontradas
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código</th>
                            <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Postulante</th>
                            <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Convocatoria</th>
                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Evaluador Asignado</th>
                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado Asignación</th>
                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado Evaluación</th>
                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Puntaje</th>
                            <th scope="col" class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($applications ?? [] as $application)
                        @php
                            $assignment = $application->evaluatorAssignments->first();
                            $evaluation = $application->evaluations->first();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $application->code }}</div>
                                <div class="text-xs text-gray-500">{{ $application->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center shadow-sm">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-gray-900">{{ $application->full_name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $application->dni ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ Str::limit($application->jobProfile->jobPosting->title ?? 'N/A', 35) }}</div>
                                <div class="text-xs text-gray-500">{{ $application->jobProfile->jobPosting->code ?? '' }}</div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($assignment)
                                    <div class="text-sm font-medium text-gray-900">{{ $assignment->user->getFullNameAttribute() ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $assignment->phase->name ?? '' }}</div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Sin asignar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($assignment)
                                    @php
                                        $statusConfig = [
                                            'PENDING' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock'],
                                            'IN_PROGRESS' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-spinner'],
                                            'COMPLETED' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
                                        ];
                                        $config = $statusConfig[$assignment->status->value ?? 'PENDING'] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                        <i class="fas {{ $config['icon'] }} mr-1"></i>
                                        {{ $assignment->status->label() ?? 'N/A' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($evaluation)
                                    @php
                                        $evalStatusConfig = [
                                            'ASSIGNED' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-clipboard'],
                                            'IN_PROGRESS' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-edit'],
                                            'SUBMITTED' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check'],
                                            'MODIFIED' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-pen'],
                                        ];
                                        $evalConfig = $evalStatusConfig[$evaluation->status->value ?? 'ASSIGNED'] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-question'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $evalConfig['bg'] }} {{ $evalConfig['text'] }}">
                                        <i class="fas {{ $evalConfig['icon'] }} mr-1"></i>
                                        {{ $evaluation->status->label() ?? 'N/A' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <i class="fas fa-minus-circle mr-1"></i>
                                        Sin evaluación
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($evaluation && $evaluation->total_score)
                                    <div class="text-sm font-bold text-gray-900">{{ number_format($evaluation->total_score, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format($evaluation->percentage, 1) }}%</div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    @if($evaluation)
                                        <a href="{{ route('evaluation.show', $evaluation->id) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors text-xs font-medium"
                                           title="Ver evaluación">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-16">
                                <div class="flex flex-col items-center">
                                    <div class="bg-gray-100 rounded-full p-6 mb-4">
                                        <i class="fas fa-inbox text-gray-400 text-5xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay postulaciones</h3>
                                    <p class="text-gray-500 mb-6 max-w-md">No se encontraron postulaciones con los filtros seleccionados</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($applications) && $applications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $applications->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
