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

    <!-- Filtros -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('jobprofile.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <div class="flex items-end">
                    <x-button type="submit" variant="primary" class="w-full">
                        <!-- Icono Filtro -->
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrar
                    </x-button>
                </div>

                @if(request()->hasAny(['status']))
                    <div class="flex items-end">
                        <a href="{{ route('jobprofile.index') }}" class="w-full">
                            <x-button type="button" variant="secondary" class="w-full">
                                <!-- Icono X (Limpiar) -->
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Limpiar
                            </x-button>
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </x-card>

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