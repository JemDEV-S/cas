@extends('layouts.app')

@section('title', 'Asignaciones de Jurados')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-user-tie mr-2 text-indigo-600"></i>
                        Asignaciones de Jurados
                    </h1>
                </div>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" data-bs-toggle="modal" data-bs-target="#autoAssignModal">
                    <i class="fas fa-magic mr-2"></i>
                    Asignación Automática
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-users text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Jurados</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalJurors ?? 0 }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Disponibles</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $availableJurors ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-tasks text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Asignaciones Activas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $activeAssignments ?? 0 }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">{{ $pendingAssignments ?? 0 }}</p>
                    </div>
                </div>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carga</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if($assignments->count() > 0)
                            @foreach($assignments as $assignment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $assignment->jury_member_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $assignment->job_posting_title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $assignment->member_type->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($assignment->role_in_jury)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $assignment->role_in_jury->label() }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $assignment->current_evaluations }}/{{ $assignment->max_evaluations ?? '∞' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignment->status->color() }}">
                                        {{ $assignment->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('jury-assignments.show', $assignment->id) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-user-times text-gray-300 text-6xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay asignaciones</h3>
                                        <p class="text-gray-500 mb-4">Las asignaciones de jurados aparecerán aquí</p>
                                        <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" data-bs-toggle="modal" data-bs-target="#autoAssignModal">
                                            <i class="fas fa-magic mr-2"></i>
                                            Realizar Asignación
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if($assignments->count() > 0)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $assignments->links() }}
            </div>
            @endif
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
                <div class="modal-body p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h6 class="text-blue-800 font-medium mb-2">
                            <i class="fas fa-info-circle mr-2"></i>
                            Asignación Inteligente
                        </h6>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Distribución equitativa de cargas de trabajo</li>
                            <li>• Verificación de disponibilidad de jurados</li>
                            <li>• Respeto a especialidades y capacidades</li>
                            <li>• Optimización de asignaciones</li>
                        </ul>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccionar convocatoria...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                <option value="">Seleccionar fase...</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-gray-50 px-6 py-4">
                    <button type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="px-4 py-2 bg-green-600 text-white border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-magic mr-2"></i>
                        Asignar Automáticamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
