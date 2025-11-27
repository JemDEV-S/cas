@extends('layouts.app')

@section('title', 'Detalle del Rol')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $role->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">Información detallada del rol</p>
        </div>
        <div class="flex space-x-3">
            @can('auth.update.role')
            <a href="{{ route('roles.edit', $role) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Rol -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Información General">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $role->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Identificador (Slug)</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ $role->slug }}
                            </span>
                        </dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $role->description ?? 'Sin descripción' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            @if($role->is_active)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Inactivo
                            </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Creado</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Modules\Core\Helpers\DateHelper::format($role->created_at) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Modules\Core\Helpers\DateHelper::format($role->updated_at) }}
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Permisos -->
            <x-card title="Permisos Asignados ({{ $role->permissions->count() }})">
                @if($role->permissions->count() > 0)
                    @php
                        $permissionsByModule = $role->permissions->groupBy('module');
                    @endphp
                    <div class="space-y-4">
                        @foreach($permissionsByModule as $module => $permissions)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase">{{ $module }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($permissions as $permission)
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-8">No hay permisos asignados a este rol</p>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estadísticas -->
            <x-card title="Estadísticas">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Usuarios con este rol</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $role->users->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Permisos asignados</span>
                        <span class="text-2xl font-bold text-green-600">{{ $role->permissions->count() }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Usuarios con este rol -->
            <x-card title="Usuarios Asignados">
                @if($role->users->count() > 0)
                    <div class="space-y-3">
                        @foreach($role->users->take(5) as $user)
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-600">
                                        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $user->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        @endforeach

                        @if($role->users->count() > 5)
                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-sm text-gray-500 text-center">
                                y {{ $role->users->count() - 5 }} usuarios más
                            </p>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No hay usuarios asignados</p>
                @endif
            </x-card>

            <!-- Acciones -->
            <x-card title="Acciones">
                <div class="space-y-2">
                    @can('auth.update.role')
                    <a href="{{ route('roles.edit', $role) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Rol
                    </a>
                    @endcan

                    @can('auth.delete.role')
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este rol?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Eliminar Rol
                        </button>
                    </form>
                    @endcan
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
