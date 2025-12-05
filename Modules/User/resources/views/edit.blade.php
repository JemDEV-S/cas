@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Editar Usuario</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $user->full_name }}</p>
            </div>
            <x-button variant="secondary" onclick="window.location='{{ route('users.show', $user) }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Información Personal -->
        <x-card title="Información Personal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    label="DNI"
                    name="dni"
                    type="text"
                    maxlength="8"
                    :value="old('dni', $user->dni)"
                    placeholder="12345678"
                    required
                />

                <x-form.input
                    label="Correo Electrónico"
                    name="email"
                    type="email"
                    :value="old('email', $user->email)"
                    placeholder="usuario@example.com"
                    required
                />

                <x-form.input
                    label="Nombres"
                    name="first_name"
                    type="text"
                    :value="old('first_name', $user->first_name)"
                    placeholder="Juan Carlos"
                    required
                />

                <x-form.input
                    label="Apellidos"
                    name="last_name"
                    type="text"
                    :value="old('last_name', $user->last_name)"
                    placeholder="Pérez García"
                    required
                />

                <x-form.input
                    label="Teléfono"
                    name="phone"
                    type="text"
                    :value="old('phone', $user->phone)"
                    placeholder="987654321"
                />

                <div class="flex items-center h-full pt-7">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Usuario Activo</span>
                    </label>
                </div>
            </div>
        </x-card>

        <!-- Cambiar Contraseña -->
        <x-card title="Cambiar Contraseña">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(auth()->id() === $user->id)
                    <!-- Si el usuario está editando su propio perfil, requiere contraseña actual -->
                    <div class="md:col-span-2">
                        <x-form.input
                            label="Contraseña Actual"
                            name="current_password"
                            type="password"
                            placeholder="Ingrese su contraseña actual"
                        />
                        <p class="mt-1 text-xs text-gray-500">Requerida solo si desea cambiar su contraseña</p>
                    </div>
                @endif

                <x-form.input
                    label="Nueva Contraseña"
                    name="password"
                    type="password"
                    placeholder="Dejar en blanco para no cambiar"
                />

                <x-form.input
                    label="Confirmar Nueva Contraseña"
                    name="password_confirmation"
                    type="password"
                    placeholder="Repita la nueva contraseña"
                />
            </div>

            <div class="mt-4 p-4 bg-yellow-50 rounded-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            @if(auth()->id() === $user->id)
                                Solo complete estos campos si desea cambiar su contraseña. Debe ingresar su contraseña actual para cambiarla.
                            @else
                                Solo complete estos campos si desea cambiar la contraseña del usuario.
                            @endif
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
                        @php
                            $isChecked = in_array($role->id, old('roles', $user->roles->pluck('id')->toArray()));
                        @endphp
                        <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $isChecked ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                            <div class="flex items-center h-5">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
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
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('users.show', $user) }}'">
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Cambios
            </x-button>
        </div>
    </form>
</div>
@endsection
