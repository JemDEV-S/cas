@extends('layouts.app')

@section('title', 'Mis Evaluaciones')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mis Evaluaciones Asignadas</h1>
                    <p class="mt-1 text-sm text-gray-500">Postulaciones que debes evaluar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-clipboard-list text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Asignadas</p>
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

        <!-- Tabla de Evaluaciones -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Listado de Evaluaciones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if($assignments->count() > 0)
                            @foreach($assignments as $assignment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $assignment->application->applicant->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">DNI: {{ $assignment->application->applicant->dni ?? '' }}</div>
                                    </div>
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                                            'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                            'COMPLETED' => 'bg-green-100 text-green-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$assignment->status->value ?? 'PENDING'] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $assignment->status->label() ?? 'Pendiente' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($assignment->deadline_at)
                                        <div class="text-sm">
                                            <span class="{{ $assignment->deadline_at->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                                <i class="far fa-calendar me-1"></i>
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
                                        @if($assignment->status->value == 'COMPLETED')
                                            <a href="{{ route('evaluation.show', $assignment->id) }}" class="text-green-600 hover:text-green-900" title="Ver evaluación">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('evaluation.evaluate', $assignment->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Evaluar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes evaluaciones asignadas</h3>
                                        <p class="text-gray-500 mb-4">Tus asignaciones aparecerán aquí cuando te sean asignadas</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if(isset($assignments) && $assignments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $assignments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
