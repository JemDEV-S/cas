@extends('layouts.app')

@section('title', 'Nueva Asignación')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Nueva Asignación Organizacional</h1>
                    <p class="mt-1 text-sm text-gray-600">Asignar usuario a una unidad organizacional</p>
                </div>
                <a href="{{ route('assignments.index') }}">
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
                        <h3 class="text-sm font-medium text-red-800">Por favor corrija los siguientes errores:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('assignments.store') }}">
            @csrf

            <x-card>
                <div class="p-6">
                    <!-- Usuario -->
                    <div class="mb-6">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario <span class="text-red-500">*</span>
                        </label>
                        <select name="user_id" id="user_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('user_id') border-red-300 @enderror"
                                required>
                            <option value="">Seleccione un usuario</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }} - DNI: {{ $user->dni }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Seleccione el usuario que será asignado</p>
                    </div>

                    <!-- Unidad Organizacional -->
                    <div class="mb-6">
                        <label for="organization_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Unidad Organizacional <span class="text-red-500">*</span>
                        </label>
                        <select name="organization_unit_id" id="organization_unit_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('organization_unit_id') border-red-300 @enderror"
                                required>
                            <option value="">Seleccione una unidad</option>
                            @foreach($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}" {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('organization_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Unidad organizacional a la que se asignará el usuario</p>
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-300 @enderror"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Fecha de inicio de la asignación</p>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Fin (Opcional)
                            </label>
                            <input type="date" name="end_date" id="end_date"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 @enderror"
                                   value="{{ old('end_date') }}">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Dejar vacío para asignación indefinida</p>
                        </div>
                    </div>

                    <!-- Tipo de asignación -->
                    <div class="mb-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_primary" id="is_primary"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       {{ old('is_primary') ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3">
                                <label for="is_primary" class="font-medium text-gray-700">Asignación Principal</label>
                                <p class="text-sm text-gray-500">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marque esta opción si esta será la unidad organizacional principal del usuario.
                                    Si ya tiene una asignación principal, esta será marcada como secundaria automáticamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Información</h3>
                                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                    <li>El usuario será notificado de su asignación por correo electrónico</li>
                                    <li>Puede tener múltiples asignaciones activas simultáneamente</li>
                                    <li>Solo puede tener una asignación principal a la vez</li>
                                    <li>Las fechas pueden modificarse posteriormente si es necesario</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between rounded-b-lg">
                    <a href="{{ route('assignments.index') }}">
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
                        Guardar Asignación
                    </x-button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    // Validar que fecha fin sea posterior a fecha inicio
    endDate.addEventListener('change', function() {
        if (this.value && startDate.value) {
            if (new Date(this.value) <= new Date(startDate.value)) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        }
    });

    // Actualizar fecha mínima de fecha fin cuando cambia fecha inicio
    startDate.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            endDate.min = nextDay.toISOString().split('T')[0];

            // Limpiar fecha fin si es menor que nueva fecha inicio
            if (endDate.value && new Date(endDate.value) <= new Date(this.value)) {
                endDate.value = '';
            }
        }
    });
});
</script>
@endpush
