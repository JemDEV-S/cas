@extends('layouts.app')

@section('title', 'Editar Asignación')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Editar Asignación</h1>
                    <p class="mt-1 text-sm text-gray-600">Modificar datos de la asignación organizacional</p>
                </div>
                <a href="{{ route('assignments.show', $assignment) }}">
                    <x-button variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </x-button>
                </a>
            </div>
        </div>

        <!-- Alertas de errores -->
        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-red-800">Errores de validación:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('assignments.update', $assignment) }}">
            @csrf
            @method('PUT')

            <x-card>
                <div class="p-6">
                    <!-- Usuario (solo lectura) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                @if($assignment->user->photo_url)
                                    <img src="{{ $assignment->user->photo_url }}" class="h-12 w-12 rounded-full object-cover mr-4" alt="">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold mr-4">
                                        {{ substr($assignment->user->first_name, 0, 1) }}{{ substr($assignment->user->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $assignment->user->full_name }}</p>
                                    <p class="text-sm text-gray-500">DNI: {{ $assignment->user->dni }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unidad Organizacional (solo lectura) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Unidad Organizacional</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-900">{{ $assignment->organizationUnit->name }}</p>
                            <p class="text-sm text-gray-500">Código: {{ $assignment->organizationUnit->code }}</p>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            No es posible cambiar la unidad organizacional. Debe crear una nueva asignación.
                        </p>
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-300 @enderror"
                                   value="{{ old('start_date', $assignment->start_date->format('Y-m-d')) }}"
                                   required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin (Opcional)
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 @enderror"
                                   value="{{ old('end_date', $assignment->end_date?->format('Y-m-d')) }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Dejar vacío para asignación indefinida</p>
                        </div>
                    </div>

                    <!-- Tipo y estado -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="is_primary" id="is_primary"
                                           value="1"
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           {{ old('is_primary', $assignment->is_primary) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3">
                                    <label for="is_primary" class="font-medium text-gray-700">Asignación Principal</label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="is_active" id="is_active"
                                           value="1"
                                           class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500"
                                           {{ old('is_active', $assignment->is_active) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3">
                                    <label for="is_active" class="font-medium text-gray-700">Asignación Activa</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advertencia -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Los cambios afectarán inmediatamente los permisos y accesos del usuario.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between rounded-b-lg">
                    <a href="{{ route('assignments.show', $assignment) }}">
                        <x-button type="button" variant="secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancelar
                        </x-button>
                    </a>
                    <x-button type="submit" variant="success">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardar Cambios
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
