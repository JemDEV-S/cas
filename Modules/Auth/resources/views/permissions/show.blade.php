@extends('layouts.app')

@section('title', 'Detalle del Permiso')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $permission->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">Información detallada del permiso</p>
        </div>
        <a href="{{ route('permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Volver
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Permiso -->
        <div class="lg:col-span-2">
            <x-card title="Información General">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $permission->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Slug</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-mono rounded bg-gray-100 text-gray-800">
                                {{ $permission->slug }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Módulo</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $permission->module }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            @if($permission->is_active)
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

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $permission->description ?? 'Sin descripción' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Creado</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Modules\Core\Helpers\DateHelper::format($permission->created_at) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Modules\Core\Helpers\DateHelper::format($permission->updated_at) }}
                        </dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Estadísticas -->
            <x-card title="Estadísticas">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Roles con este permiso</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $permission->roles->count() }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Roles con este permiso -->
            <x-card title="Roles Asignados">
                @if($permission->roles->count() > 0)
                    <div class="space-y-3">
                        @foreach($permission->roles as $role)
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                @if($role->description)
                                <p class="text-xs text-gray-500">{{ Str::limit($role->description, 30) }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No hay roles asignados</p>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
