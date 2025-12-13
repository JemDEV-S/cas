@extends('layouts.app')

@section('title', 'Detalle de Jurado')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $member->full_name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $member->email }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('jury-members.edit', $member->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                    <a href="{{ route('jury-members.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información General -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">
                            <i class="fas fa-user mr-2"></i>
                            Información General
                        </h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Estado:</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member->status_badge['color'] }}">
                                        {{ $member->status_badge['label'] }}
                                    </span>
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Especialidad:</dt>
                                <dd class="text-sm text-gray-900">{{ $member->specialty ?? 'N/A' }}</dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Experiencia:</dt>
                                <dd class="text-sm text-gray-900">{{ $member->years_of_experience ?? 'N/A' }} años</dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Título:</dt>
                                <dd class="text-sm text-gray-900">{{ $member->professional_title ?? 'N/A' }}</dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Capacitación:</dt>
                                <dd class="text-sm">
                                    @if($member->training_completed)
                                        <span class="text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Completada
                                        </span>
                                        <br>
                                        <small class="text-gray-500">{{ $member->training_completed_at?->format('d/m/Y') }}</small>
                                    @else
                                        <span class="text-yellow-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pendiente
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Disponible:</dt>
                                <dd class="text-sm">
                                    @if($member->is_available)
                                        <span class="text-green-600">
                                            <i class="fas fa-check mr-1"></i>
                                            Sí
                                        </span>
                                    @else
                                        <span class="text-red-600">
                                            <i class="fas fa-times mr-1"></i>
                                            No
                                        </span>
                                        @if($member->unavailability_reason)
                                            <br>
                                            <small class="text-gray-500">{{ $member->unavailability_reason }}</small>
                                        @endif
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @if($member->bio)
                            <hr class="my-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Biografía</h4>
                            <p class="text-sm text-gray-600">{{ $member->bio }}</p>
                        @endif
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if(!$member->training_completed)
                            <button class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" onclick="completeTraining()">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                Completar Capacitación
                            </button>
                        @endif

                        @if($member->is_available)
                            <button class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500" onclick="markUnavailable()">
                                <i class="fas fa-ban mr-2"></i>
                                Marcar No Disponible
                            </button>
                        @else
                            <button class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" onclick="markAvailable()">
                                <i class="fas fa-check mr-2"></i>
                                Marcar Disponible
                            </button>
                        @endif

                        <button class="w-full px-4 py-2 {{ $member->is_active ? 'bg-gray-600 hover:bg-gray-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $member->is_active ? 'focus:ring-gray-500' : 'focus:ring-indigo-500' }}" onclick="toggleActive()">
                            <i class="fas fa-power-off mr-2"></i>
                            {{ $member->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas y Detalles -->
            <div class="lg:col-span-2">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <i class="fas fa-tasks text-indigo-600 text-2xl mb-3"></i>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $statistics['total_assignments'] }}</h3>
                        <p class="text-sm text-gray-500">Asignaciones Totales</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <i class="fas fa-clipboard-check text-green-600 text-2xl mb-3"></i>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $statistics['total_evaluations'] }}</h3>
                        <p class="text-sm text-gray-500">Evaluaciones Realizadas</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <i class="fas fa-percentage text-yellow-600 text-2xl mb-3"></i>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $statistics['workload_percentage'] }}%</h3>
                        <p class="text-sm text-gray-500">Carga Actual</p>
                    </div>
                </div>

                <!-- Carga de Trabajo -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Carga de Trabajo
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Asignaciones Activas</label>
                                <h4 class="text-2xl font-bold text-gray-900">{{ $statistics['active_assignments'] }}</h4>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Capacidad Disponible</label>
                                <h4 class="text-2xl font-bold text-gray-900">{{ $statistics['available_capacity'] }}/{{ $statistics['max_capacity'] }}</h4>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Progreso</label>
                            <div class="w-full bg-gray-200 rounded-full h-6">
                                <div class="bg-{{ $statistics['is_overloaded'] ? 'red' : 'green' }} h-6 rounded-full flex items-center justify-center text-xs text-white font-medium" style="width: {{ $statistics['workload_percentage'] }}%">
                                    {{ $statistics['current_workload'] }} / {{ $statistics['max_capacity'] }}
                                </div>
                            </div>
                        </div>

                        @if($statistics['average_evaluation_time'])
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tiempo Promedio por Evaluación</label>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $statistics['average_evaluation_time'] }} minutos</h5>
                            </div>
                        @endif

                        @if($statistics['consistency_score'])
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Score de Consistencia</label>
                                <h5 class="text-lg font-semibold text-gray-900">{{ $statistics['consistency_score'] }}/100</h5>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Asignaciones Activas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list mr-2"></i>
                            Asignaciones Activas
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($member->assignments->where('is_active', true)->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($member->assignments->where('is_active', true) as $assignment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assignment->jobPosting->title ?? 'N/A' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $assignment->member_type->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                @if($assignment->role_in_jury)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $assignment->role_in_jury->label() }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assignment->current_evaluations }}/{{ $assignment->max_evaluations ?? '∞' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $assignment->assigned_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No tiene asignaciones activas</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const memberId = '{{ $member->id }}';

function completeTraining() {
    if (confirm('¿Marcar capacitación como completada?')) {
        fetch(`/jury-members/${memberId}/complete-training`, {
            method: 'POST',
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
                alert(data.message);
            }
        });
    }
}

function markAvailable() {
    fetch(`/jury-members/${memberId}/mark-available`, {
        method: 'POST',
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
            alert(data.message);
        }
    });
}

function markUnavailable() {
    const reason = prompt('Razón de no disponibilidad:');
    if (reason) {
        fetch(`/jury-members/${memberId}/mark-unavailable`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function toggleActive() {
    fetch(`/jury-members/${memberId}/toggle-active`, {
        method: 'POST',
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
            alert(data.message);
        }
    });
}
</script>
@endsection
