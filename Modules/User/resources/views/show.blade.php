@extends('layouts.app')

@section('title', 'Detalle del Usuario')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Detalle del Usuario</h2>
            <p class="mt-1 text-sm text-gray-600">Información completa del usuario</p>
        </div>
        <div class="flex space-x-2">
            <x-button variant="secondary" onclick="window.location='{{ route('users.index') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>

            @can('user.update.user')
            <x-button variant="primary" onclick="window.location='{{ route('users.edit', $user) }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </x-button>
            @endcan
        </div>
    </div>

    <!-- Información Principal -->
    <x-card>
        <div class="flex items-start space-x-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($user->photo_url)
                    <img src="{{ $user->photo_url }}" alt="{{ $user->full_name }}" class="w-24 h-24 rounded-full">
                @else
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-3xl">
                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Información -->
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h3>
                    @if($user->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            Inactivo
                        </span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">DNI</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->dni }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? 'No registrado' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Último Login</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Roles -->
    <x-card title="Roles Asignados">
        @if($user->roles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($user->roles as $role)
                    <div class="flex items-start p-4 border border-gray-200 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">{{ $role->name }}</h4>
                            @if($role->description)
                                <p class="mt-1 text-sm text-gray-500">{{ $role->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="mt-2">No tiene roles asignados</p>
            </div>
        @endif
    </x-card>

    <!-- Perfil Adicional -->
    @if($user->profile)
    <x-card title="Información de Perfil">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $user->profile->birth_date ? $user->profile->birth_date->format('d/m/Y') : 'No registrado' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Género</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->profile->gender ?? 'No registrado' }}</dd>
            </div>

            @if($user->profile->address)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->profile->address }}</dd>
            </div>
            @endif

            @if($user->profile->district || $user->profile->province || $user->profile->department)
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Ubicación</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ collect([$user->profile->district, $user->profile->province, $user->profile->department])->filter()->implode(', ') }}
                </dd>
            </div>
            @endif
        </div>
    </x-card>
    @endif

    <!-- Unidades Organizacionales -->
    @if($user->organizationUnits->isNotEmpty())
    <x-card title="Unidades Organizacionales">
        <div class="space-y-3">
            @foreach($user->organizationUnits as $assignment)
                @if($assignment->organizationalUnit)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $assignment->organizationalUnit->name }}</p>
                        <p class="text-sm text-gray-500">{{ $assignment->organizationalUnit->code }}</p>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <p>Desde: {{ $assignment->start_date->format('d/m/Y') }}</p>
                        @if($assignment->end_date)
                            <p>Hasta: {{ $assignment->end_date->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Acciones -->
    @can('user.delete.user')
    @if(auth()->id() != $user->id)
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-medium text-red-900">Zona de Peligro</h4>
                <p class="text-sm text-gray-600">Las siguientes acciones son irreversibles</p>
            </div>
            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar Usuario
                </x-button>
            </form>
        </div>
    </x-card>
    @endif
    @endcan
</div>
@endsection
