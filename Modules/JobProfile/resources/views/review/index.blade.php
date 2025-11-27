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
                                    @if($profile->position Code)
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
                                    <div class="font-medium text-gray-900">{{ $profile->requestedBy->name ?? 'N/A' }}</div>
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
@endsection
