@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            Mis Evaluaciones Completadas
        </h1>
        <p class="text-gray-600">Consulta y gestiona tus evaluaciones finalizadas</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Completadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Enviadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['submitted'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-paper-plane text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Modificadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['modified'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Promedio</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['average_score'] ?? 0, 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('evaluation.my-evaluations') }}" class="space-y-4">
            <!-- Barra de Búsqueda -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Buscar
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por nombre del postulante, DNI o unidad orgánica..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <!-- Filtros Adicionales -->
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                    <select name="phase_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Todas las fases</option>
                        @foreach($phases ?? [] as $phase)
                            <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                {{ $phase->name }}
                            </option>
                        @endforeach
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

                <div class="flex items-end gap-2">
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                    <a href="{{ route('evaluation.my-evaluations') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                </div>
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
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad Orgánica</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Envío</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($evaluations as $evaluation)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->jobPosting->title ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->jobPosting->code ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->profile_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->positionCode->code ?? '' }}
                                    @if($evaluation->evaluatorAssignment->application->jobProfile->positionCode->name ?? null)
                                        - {{ $evaluation->evaluatorAssignment->application->jobProfile->positionCode->name }}
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->requestingUnit->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->requestingUnit->code ?? '' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($evaluation->is_anonymous || ($evaluation->metadata['is_anonymous'] ?? false))
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-user-secret text-gray-400 mr-2"></i> Anónimo
                                </div>
                            @elseif($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    DNI: {{ $evaluation->evaluatorAssignment->application->dni ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $evaluation->phase->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'SUBMITTED' => 'bg-green-100 text-green-800',
                                    'MODIFIED' => 'bg-blue-100 text-blue-800',
                                ];
                            @endphp
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$evaluation->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $evaluation->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($evaluation->total_score !== null)
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($evaluation->total_score, 2) }}</div>
                                <div class="text-xs text-gray-500">de {{ number_format($evaluation->max_possible_score, 2) }}</div>
                                @if($evaluation->percentage !== null)
                                    <div class="text-xs text-blue-600 font-medium">{{ number_format($evaluation->percentage, 1) }}%</div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">Sin puntaje</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($evaluation->submitted_at)
                                <div class="text-sm text-gray-900">{{ $evaluation->submitted_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $evaluation->submitted_at->format('H:i') }}</div>
                            @else
                                <span class="text-sm text-gray-400">Sin fecha</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('evaluation.show', $evaluation->id) }}"
                                   class="inline-flex items-center justify-center px-4 py-2 border-2 border-blue-500 text-blue-700 text-sm font-semibold rounded-lg hover:bg-blue-50 transition-colors"
                                   title="Ver detalles">
                                    Ver
                                </a>

                                <button type="button"
                                        onclick="confirmDelete({{ $evaluation->id }}, '{{ addslashes($evaluation->evaluatorAssignment->application->full_name ?? 'esta evaluación') }}')"
                                        class="inline-flex items-center justify-center px-4 py-2 border-2 border-red-500 text-red-700 text-sm font-semibold rounded-lg hover:bg-red-50 transition-colors"
                                        title="Eliminar evaluación">
                                    Eliminar
                                </button>

                                <form id="delete-form-{{ $evaluation->id }}"
                                      action="{{ route('evaluation.destroy', $evaluation->id) }}"
                                      method="POST"
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No tienes evaluaciones completadas</p>
                                <p class="text-gray-400 text-sm mt-2">Las evaluaciones que completes aparecerán aquí</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($evaluations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $evaluations->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Eliminación</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Estás seguro de que deseas eliminar la evaluación de <strong id="evaluationName"></strong>?
                </p>
                <p class="text-sm text-red-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta acción permitirá que vuelvas a evaluar esta postulación desde cero.
                </p>
            </div>
            <div class="items-center px-4 py-3 space-x-3">
                <button id="confirmDeleteBtn"
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    <i class="fas fa-trash mr-2"></i> Eliminar
                </button>
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentDeleteId = null;

function confirmDelete(evaluationId, evaluationName) {
    currentDeleteId = evaluationId;
    document.getElementById('evaluationName').textContent = evaluationName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentDeleteId = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentDeleteId) {
        document.getElementById('delete-form-' + currentDeleteId).submit();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
