@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('evaluation.index') }}" class="text-orange-500 hover:text-orange-600 font-medium">
            <i class="fas fa-home mr-1"></i> Dashboard
        </a>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="text-gray-600">{{ $jobPosting->title }}</span>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="text-gray-900 font-medium">{{ $phase->name }}</span>
    </nav>

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md mb-6 border border-gray-200">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h1 class="text-2xl font-bold mb-1">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        {{ $phase->name }}
                    </h1>
                    <p class="text-purple-100">
                        <i class="fas fa-briefcase mr-1"></i>
                        {{ $jobPosting->title }} - {{ $jobPosting->code }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-white">{{ $assignments->total() }}</div>
                    <div class="text-sm text-purple-100">Evaluaciones asignadas</div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-purple-50">
            <div class="bg-white rounded-lg p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $assignments->total() }}</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-list text-blue-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $assignments->whereIn('status.value', ['PENDING', 'IN_PROGRESS'])->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-yellow-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Completadas</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $assignments->where('status.value', 'COMPLETED')->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Vencidas</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $assignments->filter(fn($a) => $a->deadline_at && $a->deadline_at->isPast() && in_array($a->status->value, ['PENDING', 'IN_PROGRESS']))->count() }}
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Rápidos -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <input type="hidden" name="job_posting_id" value="{{ $jobPosting->id }}">
            <input type="hidden" name="phase_id" value="{{ $phase->id }}">

            <!-- Búsqueda -->
            <div class="flex-1 min-w-[250px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Buscar
                </label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nombre o DNI del postulante..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <!-- Estado -->
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Progreso</option>
                    <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completada</option>
                </select>
            </div>

            <!-- Unidad Orgánica -->
            <div class="w-64">
                <label class="block text-sm font-medium text-gray-700 mb-2">Unidad Orgánica</label>
                <select name="requesting_unit_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todas</option>
                    @foreach($organizationalUnits ?? [] as $unit)
                        <option value="{{ $unit->id }}" {{ request('requesting_unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Solo Pendientes -->
            <div class="flex items-center">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="pending_only" value="1" {{ request('pending_only') ? 'checked' : '' }}
                           class="w-5 h-5 text-purple-500 border-gray-300 rounded focus:ring-purple-500">
                    <span class="ml-2 text-sm text-gray-700 font-medium">Solo pendientes</span>
                </label>
            </div>

            <!-- Botones -->
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
                <a href="{{ route('evaluation.list', ['job_posting_id' => $jobPosting->id, 'phase_id' => $phase->id]) }}"
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    <i class="fas fa-times mr-2"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Evaluaciones -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil / Unidad</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ ($assignments->currentPage() - 1) * $assignments->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-purple-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($assignment->metadata['is_anonymous'] ?? false)
                                            <i class="fas fa-user-secret text-gray-400 mr-2"></i> Anónimo
                                        @else
                                            {{ $assignment->application->full_name ?? 'N/A' }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        DNI: {{ $assignment->application->dni ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $assignment->application->jobProfile->profile_name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $assignment->application->jobProfile->requestingUnit->name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'PENDING' => 'bg-blue-100 text-blue-800',
                                    'IN_PROGRESS' => 'bg-yellow-100 text-yellow-800',
                                    'COMPLETED' => 'bg-green-100 text-green-800',
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
                            @if($assignment->evaluation && $assignment->evaluation->id)
                                <a href="{{ route('evaluation.show', $assignment->evaluation->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if(in_array($assignment->status->value, ['PENDING', 'IN_PROGRESS']))
                                    <a href="{{ route('evaluation.evaluate', $assignment->evaluation->id) }}"
                                       onclick="saveListState()"
                                       class="inline-flex items-center px-3 py-1.5 bg-purple-500 text-white text-sm font-medium rounded-lg hover:bg-purple-600 transition-colors"
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No se encontraron evaluaciones</p>
                                <p class="text-gray-400 text-sm mt-2">Intenta ajustar los filtros de búsqueda</p>
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
            {{ $assignments->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
// Guardar el estado actual de la lista (filtros y página) antes de ir a evaluar
function saveListState() {
    const state = {
        url: window.location.href,
        filters: {
            job_posting_id: '{{ $jobPosting->id }}',
            phase_id: '{{ $phase->id }}',
            search: '{{ request("search") }}',
            status: '{{ request("status") }}',
            requesting_unit_id: '{{ request("requesting_unit_id") }}',
            pending_only: '{{ request("pending_only") ? "1" : "" }}',
            page: '{{ $assignments->currentPage() }}'
        },
        timestamp: Date.now()
    };

    sessionStorage.setItem('evaluationListState', JSON.stringify(state));
    console.log('Estado guardado:', state);
}

// Restaurar filtros si volvemos desde una evaluación
function restoreFiltersFromStorage() {
    const savedState = sessionStorage.getItem('evaluationListState');

    if (savedState) {
        try {
            const state = JSON.parse(savedState);
            const timeDiff = Date.now() - state.timestamp;

            // Si pasaron menos de 30 minutos, consideramos el estado válido
            if (timeDiff < 30 * 60 * 1000) {
                console.log('Estado restaurado:', state);

                // Si la URL actual no tiene filtros pero el estado sí, redirigir
                const currentUrl = new URL(window.location.href);
                if (!currentUrl.searchParams.has('page') && state.filters.page > 1) {
                    // Construir URL con filtros guardados
                    const params = new URLSearchParams();
                    params.set('job_posting_id', state.filters.job_posting_id);
                    params.set('phase_id', state.filters.phase_id);
                    if (state.filters.search) params.set('search', state.filters.search);
                    if (state.filters.status) params.set('status', state.filters.status);
                    if (state.filters.requesting_unit_id) params.set('requesting_unit_id', state.filters.requesting_unit_id);
                    if (state.filters.pending_only) params.set('pending_only', state.filters.pending_only);
                    if (state.filters.page) params.set('page', state.filters.page);

                    const newUrl = `{{ route('evaluation.list') }}?${params.toString()}`;

                    // Solo redirigir si venimos de una evaluación (hay diferencia en la URL)
                    if (window.location.href !== newUrl && document.referrer.includes('/evaluate')) {
                        console.log('Redirigiendo a:', newUrl);
                        window.location.href = newUrl;
                    }
                }
            } else {
                // Estado expirado, limpiar
                sessionStorage.removeItem('evaluationListState');
            }
        } catch (e) {
            console.error('Error al restaurar estado:', e);
            sessionStorage.removeItem('evaluationListState');
        }
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    restoreFiltersFromStorage();
});
</script>
@endpush
@endsection
