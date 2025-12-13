@extends('layouts.app')

@section('title', 'Conflictos de Interés')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Conflictos de Interés</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestión de conflictos reportados</p>
                </div>
                <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" data-bs-toggle="modal" data-bs-target="#reportConflictModal">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Reportar Conflicto
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Búsqueda</h3>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="">Todos</option>
                            <option value="REPORTED">Reportado</option>
                            <option value="UNDER_REVIEW">En Revisión</option>
                            <option value="CONFIRMED">Confirmado</option>
                            <option value="DISMISSED">Desestimado</option>
                            <option value="RESOLVED">Resuelto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Severidad</label>
                        <select name="severity" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="">Todas</option>
                            <option value="LOW">Baja</option>
                            <option value="MEDIUM">Media</option>
                            <option value="HIGH">Alta</option>
                            <option value="CRITICAL">Crítica</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Solo Pendientes</label>
                        <select name="pending_only" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alta Prioridad</label>
                        <select name="high_priority" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="">No</option>
                            <option value="1">Sí</option>
                        </select>
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

        <!-- Tabla de Conflictos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Listado de Conflictos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severidad</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reportado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($conflicts ?? [] as $conflict)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $conflict->juryMember->full_name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $conflict->juryMember->email ?? '' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $conflict->conflict_type->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $severityColors = [
                                        'LOW' => 'bg-gray-100 text-gray-800',
                                        'MEDIUM' => 'bg-yellow-100 text-yellow-800',
                                        'HIGH' => 'bg-orange-100 text-orange-800',
                                        'CRITICAL' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $severityColors[$conflict->severity->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $conflict->severity->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-md">
                                    {{ Str::limit($conflict->description, 80) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'REPORTED' => 'bg-yellow-100 text-yellow-800',
                                        'UNDER_REVIEW' => 'bg-blue-100 text-blue-800',
                                        'CONFIRMED' => 'bg-red-100 text-red-800',
                                        'DISMISSED' => 'bg-gray-100 text-gray-800',
                                        'RESOLVED' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$conflict->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $conflict->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $conflict->reported_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $conflict->reporter_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('jury-conflicts.show', $conflict->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($conflict->isPending())
                                    <button type="button" class="text-green-600 hover:text-green-900" onclick="reviewConflict('{{ $conflict->id }}')" title="Revisar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-shield-alt text-gray-300 text-6xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay conflictos reportados</h3>
                                    <p class="text-gray-500 mb-4">Los conflictos de interés aparecerán aquí</p>
                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" data-bs-toggle="modal" data-bs-target="#reportConflictModal">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Reportar Conflicto
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($conflicts) && $conflicts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $conflicts->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Reportar Conflicto -->
    <div class="modal fade" id="reportConflictModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gray-50">
                    <h5 class="modal-title text-lg font-medium text-gray-900">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                        Reportar Conflicto de Interés
                    </h5>
                    <button type="button" class="btn-close text-gray-400 hover:text-gray-500" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('jury-conflicts.store') }}">
                    @csrf
                    <div class="modal-body p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jurado *</label>
                            <select name="jury_member_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Conflicto *</label>
                            <select name="conflict_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                                <option value="">Seleccionar...</option>
                                <option value="FAMILY">Familiar</option>
                                <option value="LABOR">Laboral</option>
                                <option value="PROFESSIONAL">Profesional</option>
                                <option value="FINANCIAL">Financiero</option>
                                <option value="PERSONAL">Personal</option>
                                <option value="ACADEMIC">Académico</option>
                                <option value="PRIOR_EVALUATION">Evaluación Previa</option>
                                <option value="OTHER">Otro</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                            <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-gray-50 px-6 py-4">
                        <button type="button" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reportar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function reviewConflict(id) {
    if (confirm('¿Mover este conflicto a revisión?')) {
        fetch(`/jury-conflicts/${id}/move-to-review`, {
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
</script>
@endsection
