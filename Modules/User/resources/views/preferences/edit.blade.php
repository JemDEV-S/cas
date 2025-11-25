@extends('layouts.app')

@section('title', 'Preferencias')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Preferencias</h2>
                <p class="mt-1 text-sm text-gray-600">Configura tu experiencia en el sistema</p>
            </div>
            <x-button variant="secondary" onclick="window.location='{{ route('profile.show') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('profile.preferences.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Interfaz -->
        <x-card title="Interfaz">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.select
                    label="Idioma"
                    name="language"
                    :value="old('language', $user->preference?->language ?? 'es')"
                >
                    <option value="es">Español</option>
                    <option value="en">English</option>
                </x-form.select>

                <x-form.select
                    label="Tema"
                    name="theme"
                    :value="old('theme', $user->preference?->theme ?? 'light')"
                >
                    <option value="light">Claro</option>
                    <option value="dark">Oscuro</option>
                    <option value="auto">Automático</option>
                </x-form.select>

                <x-form.select
                    label="Zona Horaria"
                    name="timezone"
                    :value="old('timezone', $user->preference?->timezone ?? 'America/Lima')"
                >
                    <option value="America/Lima">Lima (GMT-5)</option>
                    <option value="America/New_York">New York (GMT-5)</option>
                    <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                    <option value="America/Buenos_Aires">Buenos Aires (GMT-3)</option>
                    <option value="Europe/Madrid">Madrid (GMT+1)</option>
                </x-form.select>

                <x-form.input
                    label="Elementos por Página"
                    name="items_per_page"
                    type="number"
                    min="10"
                    max="100"
                    :value="old('items_per_page', $user->preference?->items_per_page ?? 15)"
                />
            </div>
        </x-card>

        <!-- Notificaciones -->
        <x-card title="Notificaciones">
            <div class="space-y-4">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="notifications_enabled"
                        id="notifications_enabled"
                        value="1"
                        {{ old('notifications_enabled', $user->preference?->notifications_enabled ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <label for="notifications_enabled" class="ml-3">
                        <span class="text-sm font-medium text-gray-700">Habilitar notificaciones</span>
                        <p class="text-sm text-gray-500">Recibir notificaciones en el sistema</p>
                    </label>
                </div>

                <div class="flex items-center">
                    <input
                        type="checkbox"
                        name="email_notifications"
                        id="email_notifications"
                        value="1"
                        {{ old('email_notifications', $user->preference?->email_notifications ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <label for="email_notifications" class="ml-3">
                        <span class="text-sm font-medium text-gray-700">Notificaciones por correo</span>
                        <p class="text-sm text-gray-500">Recibir notificaciones importantes por email</p>
                    </label>
                </div>
            </div>
        </x-card>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('profile.show') }}'">
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Preferencias
            </x-button>
        </div>
    </form>
</div>
@endsection
