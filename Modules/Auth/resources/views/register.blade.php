@extends('layouts.guest')

@section('title', 'Registrarse')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <!-- Logo y Título -->
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-600">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Crear Nueva Cuenta
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Completa el formulario para registrarte en el sistema
            </p>
        </div>

        <!-- Alertas -->
        @include('layouts.partials.alerts')

        <!-- Formulario de Registro -->
        <x-card>
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input
                        label="DNI"
                        name="dni"
                        type="text"
                        maxlength="8"
                        :value="old('dni')"
                        placeholder="12345678"
                        required
                        autofocus
                    />

                    <x-form.input
                        label="Correo Electrónico"
                        name="email"
                        type="email"
                        :value="old('email')"
                        placeholder="correo@example.com"
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
                        label="Teléfono (Opcional)"
                        name="phone"
                        type="text"
                        :value="old('phone')"
                        placeholder="987654321"
                    />

                    <div></div>

                    <x-form.input
                        label="Contraseña"
                        name="password"
                        type="password"
                        required
                    />

                    <x-form.input
                        label="Confirmar Contraseña"
                        name="password_confirmation"
                        type="password"
                        required
                    />
                </div>

                <div class="bg-blue-50 p-4 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                La contraseña debe tener al menos 8 caracteres.
                            </p>
                        </div>
                    </div>
                </div>

                <x-button type="submit" variant="primary" class="w-full">
                    Crear Cuenta
                </x-button>
            </form>
        </x-card>

        <!-- Link de Login -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Inicia sesión aquí
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
