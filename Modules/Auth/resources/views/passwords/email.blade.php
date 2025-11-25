@extends('layouts.guest')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo y Título -->
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-600">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña
            </p>
        </div>

        <!-- Alertas -->
        @include('layouts.partials.alerts')

        <!-- Formulario -->
        <x-card>
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <x-form.input
                    label="Correo Electrónico"
                    name="email"
                    type="email"
                    :value="old('email')"
                    placeholder="correo@example.com"
                    required
                    autofocus
                />

                <x-button type="submit" variant="primary" class="w-full">
                    Enviar Enlace de Restablecimiento
                </x-button>
            </form>
        </x-card>

        <!-- Link de Login -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                ¿Recordaste tu contraseña?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Volver a iniciar sesión
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
