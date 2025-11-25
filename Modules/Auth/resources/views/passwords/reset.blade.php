@extends('layouts.guest')

@section('title', 'Restablecer Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo y Título -->
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-600">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Restablecer Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu nueva contraseña
            </p>
        </div>

        <!-- Alertas -->
        @include('layouts.partials.alerts')

        <!-- Formulario -->
        <x-card>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <x-form.input
                    label="Correo Electrónico"
                    name="email"
                    type="email"
                    :value="old('email', $email)"
                    readonly
                />

                <x-form.input
                    label="Nueva Contraseña"
                    name="password"
                    type="password"
                    required
                    autofocus
                />

                <x-form.input
                    label="Confirmar Nueva Contraseña"
                    name="password_confirmation"
                    type="password"
                    required
                />

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
                    Restablecer Contraseña
                </x-button>
            </form>
        </x-card>

        <!-- Link de Login -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Volver a iniciar sesión
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
