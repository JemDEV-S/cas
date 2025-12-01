@extends('layouts.app')

@section('title', 'Detalle de Asignación')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Alertas -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Encabezado -->
        <x-card class="mb-6">
            <div class="px-6 py-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Detalle de Asignación</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Creada: {{ $assignment->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="text-right">
                        @if($assignment->is_active && $assignment->isCurrent())
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 mb-2">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Activa
                            </span>
                        @elseif($assignment->is_active)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 mb-2">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Programada
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 mb-2">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Inactiva
                            </span>
                        @endif
                        <br>
                        @if($assignment->is_primary)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Principal
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Secundaria
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Información del Usuario -->
            <x-card>
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Usuario Asignado
                    </h2>
                </div>
                <div class="p-6">
                    <div class="text-center mb-4">
                        @if($assignment->user->photo_url)
                            <img src="{{ $assignment->user->photo_url }}"
                                 class="h-28 w-28 rounded-full object-cover mx-auto mb-4 border-4 border-gray-100"
                                 alt="">
                        @else
                            <div class="h-28 w-28 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-3xl mx-auto mb-4 border-4 border-gray-100">
                                {{ substr($assignment->user->first_name, 0, 1) }}{{ substr($assignment->user->last_name, 0, 1) }}
                            </div>
                        @endif
                        <h3 class="text-xl font-bold text-gray-900">{{ $assignment->user->full_name }}</h3>
                        <p class="text-gray-600">{{ $assignment->user->email }}</p>
                    </div>

                    <hr class="my-4">

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">DNI</span>
                            <span class="text-sm font-medium text-gray-900">{{ $assignment->user->dni }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Teléfono</span>
                            <span class="text-sm font-medium text-gray-900">{{ $assignment->user->phone ?? 'No registrado' }}</span>
                        </div>
                        @if($assignment->user->profile)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Fecha de Nacimiento</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $assignment->user->profile->birth_date
                                        ? $assignment->user->profile->birth_date->format('d/m/Y')
                                        : 'No registrado' }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('users.show', $assignment->user) }}">
                            <x-button variant="primary" class="w-full justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver Perfil Completo
                            </x-button>
                        </a>
                    </div>
                </div>
            </x-card>

            <!-- Información de la Unidad Organizacional -->
            <x-card>
                <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Unidad Organizacional
                    </h2>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $assignment->organizationUnit->name }}</h3>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Código</span>
                            <span class="text-sm font-medium text-gray-900">{{ $assignment->organizationUnit->code }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Tipo</span>
                            <span class="text-sm font-medium text-gray-900">{{ $assignment->organizationUnit->type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Nivel</span>
                            <span class="text-sm font-medium text-gray-900">Nivel {{ $assignment->organizationUnit->level }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Estado</span>
                            @if($assignment->organizationUnit->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactiva
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($assignment->organizationUnit->description)
                        <div class="mb-6">
                            <span class="block text-sm text-gray-600 mb-1">Descripción</span>
                            <p class="text-sm text-gray-900">{{ $assignment->organizationUnit->description }}</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('organizational-units.show', $assignment->organizationUnit) }}">
                            <x-button variant="primary" class="w-full justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Ver Unidad Completa
                            </x-button>
                        </a>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Información de la Asignación -->
        <x-card class="mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Detalles de la Asignación
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <span class="block text-sm text-gray-600 mb-1">Fecha de Inicio</span>
                        <p class="text-lg font-bold text-gray-900">{{ $assignment->start_date->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $assignment->start_date->diffForHumans() }}</p>
                    </div>

                    <div class="border-l-4 border-red-500 pl-4">
                        <span class="block text-sm text-gray-600 mb-1">Fecha de Fin</span>
                        @if($assignment->end_date)
                            <p class="text-lg font-bold text-gray-900">{{ $assignment->end_date->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->end_date->diffForHumans() }}</p>
                        @else
                            <p class="text-lg font-bold text-gray-900">Indefinido</p>
                            <p class="text-xs text-gray-500">Sin fecha de fin</p>
                        @endif
                    </div>

                    <div class="border-l-4 border-green-500 pl-4">
                        <span class="block text-sm text-gray-600 mb-1">Duración</span>
                        @if($assignment->end_date)
                            <p class="text-lg font-bold text-gray-900">
                                {{ $assignment->start_date->diffInDays($assignment->end_date) }} días
                            </p>
                        @else
                            <p class="text-lg font-bold text-gray-900">En curso</p>
                        @endif
                        <p class="text-xs text-gray-500">
                            Días transcurridos: {{ $assignment->start_date->diffInDays(now()) }}
                        </p>
                    </div>

                    <div class="border-l-4 border-yellow-500 pl-4">
                        <span class="block text-sm text-gray-600 mb-1">Tipo de Asignación</span>
                        <p class="text-lg font-bold text-gray-900">
                            @if($assignment->is_primary)
                                <svg class="w-5 h-5 inline text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Principal
                            @else
                                Secundaria
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $assignment->is_active ? 'Activa' : 'Inactiva' }}
                        </p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Historial -->
        @if($assignment->created_at || $assignment->updated_at)
            <x-card class="mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Historial
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-medium text-gray-900">Asignación Creada</h4>
                                <p class="text-sm text-gray-500">
                                    {{ $assignment->created_at->format('d/m/Y H:i') }}
                                    <span class="text-gray-400">({{ $assignment->created_at->diffForHumans() }})</span>
                                </p>
                            </div>
                        </div>

                        @if($assignment->updated_at && !$assignment->updated_at->eq($assignment->created_at))
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-sm font-medium text-gray-900">Última Actualización</h4>
                                    <p class="text-sm text-gray-500">
                                        {{ $assignment->updated_at->format('d/m/Y H:i') }}
                                        <span class="text-gray-400">({{ $assignment->updated_at->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        @endif

        <!-- Acciones -->
        <x-card>
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Acciones
                </h2>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('assignments.index') }}">
                        <x-button variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Volver al Listado
                        </x-button>
                    </a>

                    @can('user.update.assignment')
                        <a href="{{ route('assignments.edit', $assignment) }}">
                            <x-button variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar Asignación
                            </x-button>
                        </a>
                    @endcan

                    @can('user.unassign.organization')
                        @if($assignment->is_active)
                            <button type="button"
                                    onclick="confirmDelete()"
                                    x-data
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Desasignar Usuario
                            </button>
                        @endif
                    @endcan

                    <a href="{{ route('users.assignments', $assignment->user) }}">
                        <x-button variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Ver Todas las Asignaciones del Usuario
                        </x-button>
                    </a>
                </div>
            </div>
        </x-card>
    </div>
</div>

<!-- Modal de confirmación -->
<div x-data="{ show: false }"
     @confirm-delete.window="show = true"
     x-show="show"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show"
             @click="show = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="show"
             @click.away="show = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form action="{{ route('assignments.destroy', $assignment) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Confirmar Desasignación
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                ¿Está seguro que desea desasignar a <strong>{{ $assignment->user->full_name }}</strong> de <strong>{{ $assignment->organizationUnit->name }}</strong>? Esta acción no se puede deshacer.
                            </p>
                            <div class="mt-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Motivo (opcional)</label>
                                <textarea name="reason" id="reason" rows="3"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                          placeholder="Describa el motivo de la desasignación..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <x-button type="submit" variant="danger">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Desasignar
                    </x-button>
                    <x-button type="button" variant="secondary" @click="show = false">
                        Cancelar
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    window.dispatchEvent(new CustomEvent('confirm-delete'));
}
</script>
@endpush
