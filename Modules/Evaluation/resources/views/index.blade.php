@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-clipboard-check text-orange-500 mr-3"></i>
            Dashboard de Evaluaciones
        </h1>
        <p class="text-gray-600">Gestiona y realiza tus evaluaciones asignadas</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $assignments->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pendientes</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $assignments->where('status', 'PENDING')->count() + $assignments->where('status', 'IN_PROGRESS')->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Completadas</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $assignments->where('status', 'COMPLETED')->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Vencidas</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $assignments->where('deadline_at', '<', now())->whereIn('status', ['PENDING', 'IN_PROGRESS'])->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('evaluation.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">Todos los estados</option>
                    <option value="ASSIGNED" {{ request('status') == 'ASSIGNED' ? 'selected' : '' }}>Asignada</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Progreso</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Enviada</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                <select name="phase_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">Todas las fases</option>
                    <!-- Aquí cargarías las fases dinámicamente -->
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Unidad Orgánica</label>
                <select name="requesting_unit_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <option value="">Todas las unidades</option>
                    @foreach($organizationalUnits ?? [] as $unit)
                        <option value="{{ $unit->id }}" {{ request('requesting_unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" name="pending_only" value="1" {{ request('pending_only') ? 'checked' : '' }} class="w-5 h-5 text-orange-500 border-gray-300 rounded focus:ring-orange-500">
                    <span class="ml-2 text-sm text-gray-700">Solo pendientes</span>
                </label>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
                <a href="{{ route('evaluation.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-times mr-2"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Evaluations Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad Orgánica</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $assignment->jobPosting->title ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $assignment->jobPosting->code ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $assignment->application->jobProfile->requestingUnit->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $assignment->application->jobProfile->requestingUnit->code ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                @if($assignment->metadata['is_anonymous'] ?? false)
                                    <i class="fas fa-user-secret text-gray-400 mr-2"></i> Anónimo
                                @else
                                    {{ $assignment->application->full_name ?? 'N/A' }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $assignment->phase->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'PENDING' => 'bg-blue-100 text-blue-800',
                                    'IN_PROGRESS' => 'bg-yellow-100 text-yellow-800',
                                    'COMPLETED' => 'bg-green-100 text-green-800',
                                    'CANCELLED' => 'bg-red-100 text-red-800',
                                    'REASSIGNED' => 'bg-orange-100 text-orange-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$assignment->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $assignment->status->value }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($assignment->evaluation && $assignment->evaluation->total_score)
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($assignment->evaluation->total_score, 2) }}</div>
                                <div class="text-xs text-gray-500">de {{ number_format($assignment->evaluation->max_possible_score, 2) }}</div>
                            @else
                                <span class="text-sm text-gray-400">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($assignment->deadline_at)
                                <div class="text-sm {{ $assignment->deadline_at->isPast() && $assignment->status->value != 'COMPLETED' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $assignment->deadline_at->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $assignment->deadline_at->diffForHumans() }}</div>
                            @else
                                <span class="text-sm text-gray-400">Sin límite</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @if($assignment->evaluation)
                                <a href="{{ route('evaluation.show', $assignment->evaluation->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(in_array($assignment->status->value, ['PENDING', 'IN_PROGRESS']))
                                <a href="{{ route('evaluation.evaluate', $assignment->evaluation->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors"
                                   title="Evaluar">
                                    <i class="fas fa-edit mr-1"></i> Evaluar
                                </a>
                                @endif
                            @else
                                <a href="{{ route('evaluation.create', ['assignment_id' => $assignment->id]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white text-sm font-medium rounded-lg hover:bg-green-600 transition-colors"
                                   title="Iniciar evaluación">
                                    <i class="fas fa-play mr-1"></i> Iniciar
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No hay asignaciones disponibles</p>
                                <p class="text-gray-400 text-sm mt-2">Las asignaciones de evaluación aparecerán aquí</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($assignments->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
