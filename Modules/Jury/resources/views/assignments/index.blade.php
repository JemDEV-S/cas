@extends('layouts.app')

@section('title', 'Asignaciones de Jurados')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50" x-data="juryAssignmentsData()">
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
                <div class="flex space-x-3">
                    <button
                        type="button"
                        @click="openManualAssignModal()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-user-plus mr-2"></i>
                        Asignar Jurado
                    </button>
                    <button
                        type="button"
                        @click="openAutoAssignModal()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-magic mr-2"></i>
                        Asignación Automática
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('jury-assignments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Convocatoria</label>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role_in_jury" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos</option>
                        <option value="PRESIDENTE" {{ request('role_in_jury') == 'PRESIDENTE' ? 'selected' : '' }}>Presidente</option>
                        <option value="SECRETARIO" {{ request('role_in_jury') == 'SECRETARIO' ? 'selected' : '' }}>Secretario</option>
                        <option value="VOCAL" {{ request('role_in_jury') == 'VOCAL' ? 'selected' : '' }}>Vocal</option>
                        <option value="MIEMBRO" {{ request('role_in_jury') == 'MIEMBRO' ? 'selected' : '' }}>Miembro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos</option>
                        <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-filter mr-2"></i>
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-users text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Asignaciones</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $assignments->total() }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Activos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $activeCount ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-gray-100 rounded-lg p-3">
                            <i class="fas fa-user-slash text-gray-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Inactivos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $inactiveCount ?? 0 }}</p>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dependencia</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if($assignments->count() > 0)
                            @foreach($assignments as $assignment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <i class="fas fa-user text-indigo-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $assignment->user->full_name ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ $assignment->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $assignment->job_posting_title }}</div>
                                    <div class="text-sm text-gray-500">{{ $assignment->jobPosting->code ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($assignment->role_in_jury)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $assignment->role_in_jury->color() }}-100 text-{{ $assignment->role_in_jury->color() }}-800">
                                            {{ $assignment->role_in_jury->label() }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $assignment->dependencyScope->name ?? 'Todas' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $assignment->status->color() }}-100 text-{{ $assignment->status->color() }}-800">
                                        {{ $assignment->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('jury-assignments.show', $assignment->id) }}"
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($assignment->isActive())
                                            <button
                                                @click="deactivateAssignment('{{ $assignment->id }}')"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @else
                                            <button
                                                @click="activateAssignment('{{ $assignment->id }}')"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-user-times text-gray-300 text-6xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay asignaciones</h3>
                                        <p class="text-gray-500 mb-4">Las asignaciones de jurados aparecerán aquí</p>
                                        <button
                                            type="button"
                                            @click="openAutoAssignModal()"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
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

    <!-- Modal Asignación Manual -->
    <div
        x-show="showManualAssignModal"
        @keydown.escape.window="showManualAssignModal = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div
                x-show="showManualAssignModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                @click="showManualAssignModal = false"
                aria-hidden="true"></div>

            <!-- Spacer -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div
                x-show="showManualAssignModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showManualAssignModal = false"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10">

                <form @submit.prevent="submitManualAssign()">
                    <div class="bg-white px-6 pt-5 pb-4">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                                <i class="fas fa-user-plus text-indigo-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Asignar Jurado Manualmente
                                </h3>
                                <p class="text-sm text-gray-500">Seleccione un usuario con rol JURADO</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Usuario (Jurado) <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="manualAssignForm.user_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar usuario...</option>
                                    @foreach($jurors ?? [] as $juror)
                                        <option value="{{ $juror->id }}">{{ $juror->name }} - {{ $juror->email }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Solo se muestran usuarios con rol JURADO</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Convocatoria <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="manualAssignForm.job_posting_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar convocatoria...</option>
                                    @foreach($jobPostings ?? [] as $posting)
                                        <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Rol en el Jurado <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="manualAssignForm.role_in_jury"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Seleccionar rol...</option>
                                    <option value="PRESIDENTE">Presidente</option>
                                    <option value="SECRETARIO">Secretario</option>
                                    <option value="VOCAL">Vocal</option>
                                    <option value="MIEMBRO">Miembro</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ámbito de Dependencia (opcional)
                                </label>
                                <select
                                    x-model="manualAssignForm.dependency_scope_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Sin restricción (puede evaluar todas)</option>
                                    @foreach($dependencies ?? [] as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Limita las postulaciones que este jurado puede evaluar según dependencia</p>
                            </div>
                        </div>

                        <div x-show="manualAssignError" class="mt-4 bg-red-50 border border-red-200 rounded-md p-3" x-cloak>
                            <p class="text-sm text-red-800" x-text="manualAssignError"></p>
                        </div>

                        <div x-show="manualAssignLoading" class="mt-4 text-center" x-cloak>
                            <i class="fas fa-spinner fa-spin text-indigo-600 text-2xl"></i>
                            <p class="text-sm text-gray-600 mt-2">Asignando jurado...</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showManualAssignModal = false"
                            :disabled="manualAssignLoading"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="manualAssignLoading"
                            class="px-4 py-2 bg-indigo-600 text-white border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <i class="fas fa-user-plus mr-2"></i>
                            Asignar Jurado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Asignación Automática -->
    <div
        x-show="showAutoAssignModal"
        @keydown.escape.window="showAutoAssignModal = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div
                x-show="showAutoAssignModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                @click="showAutoAssignModal = false"
                aria-hidden="true"></div>

            <!-- Spacer para centrar verticalmente -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div
                x-show="showAutoAssignModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showAutoAssignModal = false"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-10">

                <form @submit.prevent="submitAutoAssign()">
                    <div class="bg-white px-6 pt-5 pb-4">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <i class="fas fa-magic text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Asignación Automática de Jurados
                                </h3>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h6 class="text-blue-800 font-medium mb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                Asignación Inteligente
                            </h6>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Distribución equitativa de cargas de trabajo</li>
                                <li>• Verificación de disponibilidad de jurados</li>
                                <li>• Exclusión automática de conflictos de interés</li>
                                <li>• Selección basada en roles (JURADO)</li>
                            </ul>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Convocatoria <span class="text-red-500">*</span>
                                </label>
                                <select
                                    x-model="autoAssignForm.job_posting_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                                    <option value="">Seleccionar convocatoria...</option>
                                    @foreach($jobPostings ?? [] as $posting)
                                        <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Jurados <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    x-model="autoAssignForm.total_jurors"
                                    min="1"
                                    max="10"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                    placeholder="Ej: 3">
                                <p class="mt-1 text-xs text-gray-500">Número de jurados a asignar (1-10)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Roles Preferidos (opcional)
                                </label>
                                <div class="space-y-2">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" value="PRESIDENTE" x-model="autoAssignForm.preferred_roles" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Presidente</span>
                                    </label>
                                    <label class="inline-flex items-center mr-4">
                                        <input type="checkbox" value="SECRETARIO" x-model="autoAssignForm.preferred_roles" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Secretario</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" value="VOCAL" x-model="autoAssignForm.preferred_roles" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Vocal</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div x-show="autoAssignError" class="mt-4 bg-red-50 border border-red-200 rounded-md p-3" x-cloak>
                            <p class="text-sm text-red-800" x-text="autoAssignError"></p>
                        </div>

                        <div x-show="autoAssignLoading" class="mt-4 text-center" x-cloak>
                            <i class="fas fa-spinner fa-spin text-green-600 text-2xl"></i>
                            <p class="text-sm text-gray-600 mt-2">Asignando jurados...</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showAutoAssignModal = false"
                            :disabled="autoAssignLoading"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="autoAssignLoading"
                            class="px-4 py-2 bg-green-600 text-white border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                            <i class="fas fa-magic mr-2"></i>
                            Asignar Automáticamente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function juryAssignmentsData() {
    return {
        // Manual Assignment
        showManualAssignModal: false,
        manualAssignLoading: false,
        manualAssignError: '',
        manualAssignForm: {
            user_id: '',
            job_posting_id: '',
            role_in_jury: '',
            dependency_scope_id: ''
        },

        // Auto Assignment
        showAutoAssignModal: false,
        autoAssignLoading: false,
        autoAssignError: '',
        autoAssignForm: {
            job_posting_id: '',
            total_jurors: 3,
            preferred_roles: []
        },

        openManualAssignModal() {
            this.showManualAssignModal = true;
            this.manualAssignError = '';
            this.manualAssignForm = {
                user_id: '',
                job_posting_id: '',
                role_in_jury: '',
                dependency_scope_id: ''
            };
        },

        async submitManualAssign() {
            this.manualAssignLoading = true;
            this.manualAssignError = '';

            try {
                const response = await fetch('{{ route("jury-assignments.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.manualAssignForm)
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    this.manualAssignError = data.message || 'Error al asignar jurado';
                }
            } catch (error) {
                this.manualAssignError = 'Error de conexión. Por favor intente nuevamente.';
            } finally {
                this.manualAssignLoading = false;
            }
        },

        openAutoAssignModal() {
            this.showAutoAssignModal = true;
            this.autoAssignError = '';
            this.autoAssignForm = {
                job_posting_id: '',
                total_jurors: 3,
                preferred_roles: []
            };
        },

        async submitAutoAssign() {
            this.autoAssignLoading = true;
            this.autoAssignError = '';

            try {
                const response = await fetch('{{ route("jury-assignments.auto-assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.autoAssignForm)
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    this.autoAssignError = data.message || 'Error al asignar jurados';
                }
            } catch (error) {
                this.autoAssignError = 'Error de conexión. Por favor intente nuevamente.';
            } finally {
                this.autoAssignLoading = false;
            }
        },

        async deactivateAssignment(assignmentId) {
            if (!confirm('¿Está seguro de desactivar esta asignación?')) return;

            try {
                const response = await fetch(`/jury-assignments/${assignmentId}/deactivate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al desactivar asignación');
                }
            } catch (error) {
                alert('Error de conexión');
            }
        },

        async activateAssignment(assignmentId) {
            if (!confirm('¿Está seguro de activar esta asignación?')) return;

            try {
                const response = await fetch(`/jury-assignments/${assignmentId}/activate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al activar asignación');
                }
            } catch (error) {
                alert('Error de conexión');
            }
        }
    }
}
</script>
@endpush
@endsection
