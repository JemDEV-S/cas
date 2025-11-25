@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo y Título -->
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-600">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sistema CAS - MDSJ
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Inicia sesión con tu DNI o correo electrónico
            </p>
        </div>

        <!-- Alertas -->
        @include('layouts.partials.alerts')

        <!-- Formulario de Login -->
        <x-card>
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <x-form.input
                    label="DNI o Correo Electrónico"
                    name="login"
                    type="text"
                    :value="old('login')"
                    placeholder="12345678 o correo@example.com"
                    required
                    autofocus
                />

                <x-form.input
                    label="Contraseña"
                    name="password"
                    type="password"
                    required
                />

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <x-button type="submit" variant="primary" class="w-full">
                    Iniciar Sesión
                </x-button>
            </form>
        </x-card>

        <!-- Link de Registro -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Regístrate aquí
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
