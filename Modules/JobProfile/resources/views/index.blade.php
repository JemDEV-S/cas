@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Perfiles de Puesto</h1>
                <p class="mt-1 text-sm text-gray-600">Gestión de perfiles de puesto para convocatorias</p>
            </div>
            @can('create', \Modules\JobProfile\Entities\JobProfile::class)
                <a href="{{ route('jobprofile.profiles.create') }}">
                    <x-button variant="primary">
                        <!-- Icono Plus -->
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo Perfil
                    </x-button>
                </a>
            @else
                @php
                    $policy = new \Modules\JobProfile\Policies\JobProfilePolicy();
                    $dateMessage = $policy->getCreationDateRangeMessage();
                @endphp
                @if($dateMessage && auth()->user()->hasPermission('jobprofile.create.profile'))
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">{{ $dateMessage }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total de Perfiles -->
        <x-card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Total de Perfiles</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ $statistics['total'] }}</p>
                </div>
                <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Total de Vacantes -->
        <x-card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Total de Vacantes</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ $statistics['total_vacancies'] }}</p>
                </div>
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Perfiles en Revisión -->
        <x-card class="bg-gradient-to-br from-yellow-50 to-yellow-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600">En Revisión</p>
                    <p class="text-3xl font-bold text-yellow-900 mt-2">{{ $statistics['by_status']['in_review'] }}</p>
                </div>
                <div class="p-3 bg-yellow-500 rounded-full">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>

        <!-- Perfiles Aprobados -->
        <x-card class="bg-gradient-to-br from-purple-50 to-purple-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600">Aprobados</p>
                    <p class="text-3xl font-bold text-purple-900 mt-2">{{ $statistics['by_status']['approved'] }}</p>
                </div>
                <div class="p-3 bg-purple-500 rounded-full">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filtros -->
    <x-card class="mb-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtros de Búsqueda
            </h3>
        </div>
        <form method="GET" action="{{ route('jobprofile.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Búsqueda por texto -->
                <div>
                    <x-form.input
                        type="text"
                        name="search"
                        label="Buscar"
                        placeholder="Código o título del perfil"
                        :value="request('search')"
                    />
                </div>

                <!-- Filtro por estado -->
                <x-form.select
                    name="status"
                    label="Estado"
                    :options="[
                        'draft' => 'Borrador',
                        'in_review' => 'En Revisión',
                        'modification_requested' => 'Modificación Requerida',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'active' => 'Activo'
                    ]"
                    :selected="request('status')"
                    placeholder="Todos los estados"
                />

                <!-- Filtro por unidad organizacional -->
                <x-form.select
                    name="organizational_unit_id"
                    label="Unidad Organizacional"
                    :options="$organizationalUnits"
                    :selected="request('organizational_unit_id')"
                    placeholder="Todas las unidades"
                />

                <!-- Filtro por código de posición -->
                <x-form.select
                    name="position_code_id"
                    label="Código de Posición"
                    :options="$positionCodes"
                    :selected="request('position_code_id')"
                    placeholder="Todos los códigos"
                />
            </div>

            <div class="flex gap-3">
                <x-button type="submit" variant="primary">
                    <!-- Icono Filtro -->
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Aplicar Filtros
                </x-button>

                @if(request()->hasAny(['status', 'search', 'organizational_unit_id', 'position_code_id']))
                    <a href="{{ route('jobprofile.index') }}">
                        <x-button type="button" variant="secondary">
                            <!-- Icono X (Limpiar) -->
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Limpiar Filtros
                        </x-button>
                    </a>
                @endif
            </div>

            @if(request()->hasAny(['status', 'search', 'organizational_unit_id', 'position_code_id']))
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Filtros activos:</span>
                        @if(request('search'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                Búsqueda: "{{ request('search') }}"
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                Estado: {{ [
                                    'draft' => 'Borrador',
                                    'in_review' => 'En Revisión',
                                    'modification_requested' => 'Modificación Requerida',
                                    'approved' => 'Aprobado',
                                    'rejected' => 'Rechazado',
                                    'active' => 'Activo'
                                ][request('status')] ?? request('status') }}
                            </span>
                        @endif
                        @if(request('organizational_unit_id'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                Unidad: {{ $organizationalUnits[request('organizational_unit_id')] ?? 'N/A' }}
                            </span>
                        @endif
                        @if(request('position_code_id'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                                Código: {{ $positionCodes[request('position_code_id')] ?? 'N/A' }}
                            </span>
                        @endif
                    </p>
                </div>
            @endif
        </form>
    </x-card>

    <!-- Resumen de Resultados -->
    @if(request()->hasAny(['status', 'search', 'organizational_unit_id', 'position_code_id']) || $jobProfiles->isNotEmpty())
        <div class="mb-4 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Mostrando <span class="font-semibold text-gray-900">{{ $jobProfiles->count() }}</span>
                {{ $jobProfiles->count() === 1 ? 'perfil' : 'perfiles' }}
                @if(request()->hasAny(['status', 'search', 'organizational_unit_id', 'position_code_id']))
                    de <span class="font-semibold text-gray-900">{{ $statistics['total'] }}</span> en total
                @endif
            </div>
        </div>
    @endif

    <!-- Tabla de perfiles -->
    <x-card>
        @if($jobProfiles->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay perfiles de puesto</h3>
                <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo perfil de puesto.</p>
                @can('create', \Modules\JobProfile\Entities\JobProfile::class)
                    <div class="mt-6">
                        <a href="{{ route('jobprofile.profiles.create') }}">
                            <x-button variant="primary">
                                <!-- Icono Plus -->
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Crear Perfil
                            </x-button>
                        </a>
                    </div>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Código</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Título</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Código de Posición</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Unidad Organizacional</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Vacantes</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Estado</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Solicitado por</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fecha</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($jobProfiles as $profile)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                    <code class="px-2 py-1 bg-gray-100 rounded text-gray-900 font-mono">{{ $profile->code }}</code>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ $profile->title }}</div>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    @if($profile->positionCode)
                                        <div class="flex flex-col">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mb-1" style="width: fit-content;">
                                                {{ $profile->positionCode->code }}
                                            </span>
                                            <span class="text-xs text-gray-600">{{ $profile->positionCode->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin código</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    {{ $profile->organizationalUnit->name ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-4 text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $profile->total_vacancies }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($profile->status_badge === 'success') bg-green-100 text-green-800
                                        @elseif($profile->status_badge === 'warning') bg-yellow-100 text-yellow-800
                                        @elseif($profile->status_badge === 'danger') bg-red-100 text-red-800
                                        @elseif($profile->status_badge === 'info') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $profile->status_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ optional($profile->requestedBy)->first_name . ' ' . optional($profile->requestedBy)->last_name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    {{ $profile->created_at->format('d/m/Y') }}
                                </td>
                                
                                {{-- COLUMNA DE ACCIONES CORREGIDA --}}
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @can('view', $profile)
                                            <a href="{{ route('jobprofile.profiles.show', $profile->id) }}"
                                               class="inline-flex items-center px-2 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                               title="Ver">
                                                <!-- Icono Ojo -->
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('update', $profile)
                                            <a href="{{ route('jobprofile.profiles.edit', $profile->id) }}"
                                               class="inline-flex items-center px-2 py-2 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                               title="Editar">
                                                <!-- Icono Lápiz -->
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('submitForReview', $profile)
                                            @if($profile->canSubmitForReview())
                                                <form action="{{ route('jobprofile.profiles.submit', $profile->id) }}"
                                                      method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2 py-2 border border-green-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                            title="Enviar a Revisión"
                                                            onclick="return confirm('¿Está seguro de enviar este perfil a revisión?')">
                                                        <!-- Icono Enviar (Avión de papel) -->
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('delete', $profile)
                                            <form action="{{ route('jobprofile.profiles.destroy', $profile->id) }}"
                                                  method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-2 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Está seguro de eliminar este perfil? Esta acción no se puede deshacer.')">
                                                    <!-- Icono Basura -->
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection