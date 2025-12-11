@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Perfiles Pendientes de Revisión</h1>
                <p class="mt-1 text-sm text-gray-600">Revise y apruebe los perfiles de puesto solicitados</p>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pendientes</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $jobProfiles->count() }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <i class="fas fa-file-alt text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En Revisión Hoy</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $jobProfiles->where('reviewed_at', '>=', today())->count() }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Aprobados Este Mes</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('status', 'approved')
                            ->whereMonth('approved_at', now()->month)
                            ->count() }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filtros -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('jobprofile.review.index') }}" id="filtersForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Búsqueda por texto -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Código o título del perfil..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Unidad Organizacional -->
                <div>
                    <label for="organizational_unit_id" class="block text-sm font-medium text-gray-700 mb-1">Unidad Organizacional</label>
                    <select
                        name="organizational_unit_id"
                        id="organizational_unit_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Todas las unidades</option>
                        @foreach($organizationalUnits as $unit)
                            <option value="{{ $unit->id }}" {{ ($filters['organizational_unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Código de Puesto -->
                <div>
                    <label for="position_code_id" class="block text-sm font-medium text-gray-700 mb-1">Código de Puesto</label>
                    <select
                        name="position_code_id"
                        id="position_code_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Todos los códigos</option>
                        @foreach($positionCodes as $code)
                            <option value="{{ $code->id }}" {{ ($filters['position_code_id'] ?? '') == $code->id ? 'selected' : '' }}>
                                {{ $code->code }} - {{ $code->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Solicitado por -->
                <div>
                    <label for="requested_by" class="block text-sm font-medium text-gray-700 mb-1">Solicitado por</label>
                    <select
                        name="requested_by"
                        id="requested_by"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">Todos los solicitantes</option>
                        @foreach($requesters as $requester)
                            <option value="{{ $requester->id }}" {{ ($filters['requested_by'] ?? '') == $requester->id ? 'selected' : '' }}>
                                {{ $requester->first_name }} {{ $requester->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha desde -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Fecha desde</label>
                    <input
                        type="date"
                        name="date_from"
                        id="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                </div>

                <!-- Fecha hasta -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Fecha hasta</label>
                    <input
                        type="date"
                        name="date_to"
                        id="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('jobprofile.review.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-filter mr-2"></i>
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </x-card>

    <!-- Indicador de filtros activos -->
    @php
        $activeFilters = array_filter($filters);
        $hasActiveFilters = count($activeFilters) > 0;
    @endphp

    @if($hasActiveFilters)
        <div class="mb-4">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-filter text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-blue-800">
                        Filtros activos: {{ count($activeFilters) }}
                    </span>
                    <div class="ml-4 flex flex-wrap gap-2">
                        @if(!empty($filters['search']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Búsqueda: "{{ $filters['search'] }}"
                            </span>
                        @endif
                        @if(!empty($filters['organizational_unit_id']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Unidad Org.
                            </span>
                        @endif
                        @if(!empty($filters['position_code_id']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Código de Puesto
                            </span>
                        @endif
                        @if(!empty($filters['requested_by']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Solicitante
                            </span>
                        @endif
                        @if(!empty($filters['date_from']) || !empty($filters['date_to']))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Rango de Fechas
                            </span>
                        @endif
                    </div>
                </div>
                <span class="text-sm text-blue-600">{{ $jobProfiles->count() }} resultado(s)</span>
            </div>
        </div>
    @endif

    <!-- Lista de perfiles pendientes -->
    <x-card>
        @if($jobProfiles->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay perfiles pendientes de revisión</h3>
                <p class="mt-1 text-sm text-gray-500">Todos los perfiles han sido procesados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Código</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Título</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Unidad Organizacional</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Vacantes</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Solicitado por</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fecha Solicitud</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($jobProfiles as $profile)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                    <code class="px-2 py-1 bg-gray-100 rounded text-gray-900 font-mono">{{ $profile->code }}</code>
                                </td>
                                <td class="px-3 py-4 text-sm">
                                    <div class="font-medium text-gray-900">{{ $profile->title }}</div>
                                    @if($profile->positionCode)
                                        <div class="text-xs text-gray-500 mt-1">{{ $profile->positionCode->name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    {{ $profile->organizationalUnit->name ?? 'N/A' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $profile->total_vacancies }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm">
                                    <div class="font-medium text-gray-900">{{ $profile->requestedBy->first_name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $profile->requestedBy->email ?? '' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $profile->requested_at?->format('d/m/Y H:i') ?? $profile->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                    <a href="{{ route('jobprofile.review.show', $profile->id) }}">
                                        <x-button variant="primary" class="text-xs">
                                            <i class="fas fa-eye mr-1"></i> Revisar
                                        </x-button>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('filtersForm');
    const searchInput = document.getElementById('search');

    // Opcional: Auto-submit al cambiar filtros (excepto búsqueda)
    const selectFilters = filtersForm.querySelectorAll('select, input[type="date"]');
    selectFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            // Opcional: descomentar para auto-submit
            // filtersForm.submit();
        });
    });

    // Búsqueda con debounce
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Opcional: descomentar para auto-submit después de escribir
            // filtersForm.submit();
        }, 500);
    });

    // Tecla Enter en búsqueda
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filtersForm.submit();
        }
    });

    // Mejorar UX: Mostrar indicador de carga
    filtersForm.addEventListener('submit', function() {
        const submitButton = filtersForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Aplicando...';
        }
    });

    // Resaltar campos activos
    function highlightActiveFilters() {
        const inputs = filtersForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.value && input.value !== '') {
                input.classList.add('ring-2', 'ring-indigo-500');
            } else {
                input.classList.remove('ring-2', 'ring-indigo-500');
            }
        });
    }

    highlightActiveFilters();
});
</script>
@endpush
@endsection
