@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Mi Perfil</h2>
            <p class="mt-1 text-sm text-gray-600">Información personal y configuración</p>
        </div>
        <x-button variant="primary" onclick="window.location='{{ route('profile.edit') }}'">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar Perfil
        </x-button>
    </div>

    <!-- Información Principal -->
    <x-card>
        <div class="flex items-start space-x-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($user->profile && $user->profile->photo_url)
                    <img src="{{ asset('storage/' . $user->profile->photo_url) }}" alt="{{ $user->full_name }}" class="w-24 h-24 rounded-full">
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
                <h3 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h3>
                <p class="text-sm text-gray-600">{{ $user->email }}</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">DNI</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->dni }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? 'No registrado' }}</dd>
                    </div>

                    @if($user->profile)
                        @if($user->profile->birth_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->profile->birth_date->format('d/m/Y') }}</dd>
                        </div>
                        @endif

                        @if($user->profile->gender)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Género</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $user->profile->gender === 'M' ? 'Masculino' : ($user->profile->gender === 'F' ? 'Femenino' : 'Otro') }}
                            </dd>
                        </div>
                        @endif
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Último Login</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Nunca' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Miembro desde</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y') }}</dd>
                    </div>
                </div>

                @if($user->profile && $user->profile->address)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->profile->address }}</dd>
                    @if($user->profile->district || $user->profile->province || $user->profile->department)
                    <dd class="mt-1 text-sm text-gray-600">
                        {{ collect([$user->profile->district, $user->profile->province, $user->profile->department])->filter()->implode(', ') }}
                    </dd>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </x-card>

    <!-- Roles -->
    @if($user->roles->isNotEmpty())
    <x-card title="Mis Roles">
        <div class="flex flex-wrap gap-2">
            @foreach($user->roles as $role)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ $role->name }}
                </span>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Unidades Organizacionales -->
    @if($user->organizationUnits->isNotEmpty())
    <x-card title="Unidades Organizacionales">
        <div class="space-y-3">
            @foreach($user->organizationUnits as $unit)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $unit->name }}</p>
                        <p class="text-sm text-gray-500">{{ $unit->code }}</p>
                        @if($unit->pivot->is_primary)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                Principal
                            </span>
                        @endif
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        @if($unit->pivot->start_date)
                            <p>Desde: {{ \Carbon\Carbon::parse($unit->pivot->start_date)->format('d/m/Y') }}</p>
                        @endif
                        @if($unit->pivot->end_date)
                            <p>Hasta: {{ \Carbon\Carbon::parse($unit->pivot->end_date)->format('d/m/Y') }}</p>
                        @endif
                        @if($unit->pivot->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                Activa
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                                Inactiva
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Link a Preferencias -->
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-medium text-gray-900">Preferencias</h4>
                <p class="text-sm text-gray-600">Configura tus preferencias de notificaciones e interfaz</p>
            </div>
            <x-button variant="secondary" onclick="window.location='{{ route('profile.preferences') }}'">
                Configurar
            </x-button>
        </div>
    </x-card>
</div>
@endsection
