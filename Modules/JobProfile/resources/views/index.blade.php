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
                        <i class="fas fa-plus mr-2"></i> Nuevo Perfil
                    </x-button>
                </a>
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
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </x-button>
                </div>

                @if(request()->hasAny(['status']))
                    <div class="flex items-end">
                        <a href="{{ route('jobprofile.index') }}" class="w-full">
                            <x-button type="button" variant="secondary" class="w-full">
                                <i class="fas fa-times mr-2"></i> Limpiar
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
                                <i class="fas fa-plus mr-2"></i> Crear Perfil
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
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @can('view', $profile)
                                            <a href="{{ route('jobprofile.profiles.show', $profile->id) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                               title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan

                                        @can('update', $profile)
                                            <a href="{{ route('jobprofile.profiles.edit', $profile->id) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 border border-yellow-300 shadow-sm text-xs font-medium rounded text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('submitForReview', $profile)
                                            @if($profile->canSubmitForReview())
                                                <form action="{{ route('jobprofile.profiles.submit', $profile->id) }}"
                                                      method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-green-300 shadow-sm text-xs font-medium rounded text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                            title="Enviar a Revisión"
                                                            onclick="return confirm('¿Está seguro de enviar este perfil a revisión?')">
                                                        <i class="fas fa-paper-plane"></i>
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
