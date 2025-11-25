@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Crear Nuevo Usuario</h2>
                <p class="mt-1 text-sm text-gray-600">Complete la información del nuevo usuario</p>
            </div>
            <x-button variant="secondary" onclick="window.location='{{ route('users.index') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf

        <!-- Información Personal -->
        <x-card title="Información Personal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    label="DNI"
                    name="dni"
                    type="text"
                    maxlength="8"
                    :value="old('dni')"
                    placeholder="12345678"
                    required
                />

                <x-form.input
                    label="Correo Electrónico"
                    name="email"
                    type="email"
                    :value="old('email')"
                    placeholder="usuario@example.com"
                    required
                />

                <x-form.input
                    label="Nombres"
                    name="first_name"
                    type="text"
                    :value="old('first_name')"
                    placeholder="Juan Carlos"
                    required
                />

                <x-form.input
                    label="Apellidos"
                    name="last_name"
                    type="text"
                    :value="old('last_name')"
                    placeholder="Pérez García"
                    required
                />

                <x-form.input
                    label="Teléfono"
                    name="phone"
                    type="text"
                    :value="old('phone')"
                    placeholder="987654321"
                />

                <div class="flex items-center h-full pt-7">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Usuario Activo</span>
                    </label>
                </div>
            </div>
        </x-card>

        <!-- Contraseña -->
        <x-card title="Seguridad">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    label="Contraseña"
                    name="password"
                    type="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                />

                <x-form.input
                    label="Confirmar Contraseña"
                    name="password_confirmation"
                    type="password"
                    placeholder="Repita la contraseña"
                    required
                />
            </div>

            <div class="mt-4 p-4 bg-blue-50 rounded-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            La contraseña debe tener al menos 8 caracteres y contener letras y números.
                        </p>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Roles -->
        <x-card title="Roles y Permisos">
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Seleccione los roles que tendrá el usuario:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($roles as $role)
                        <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ in_array($role->id, old('roles', [])) ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <div class="flex items-center h-5">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->id }}"
                                    {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-900">{{ $role->name }}</span>
                                @if($role->description)
                                    <p class="text-gray-500">{{ $role->description }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </x-card>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('users.index') }}'">
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Crear Usuario
            </x-button>
        </div>
    </form>
</div>
@endsection
