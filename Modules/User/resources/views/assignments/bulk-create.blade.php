@extends('layouts.app')

@section('title', 'Asignación Masiva')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Asignación Masiva de Usuarios</h1>
                    <p class="mt-1 text-sm text-gray-600">Asignar múltiples usuarios a una unidad organizacional</p>
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
                        <h3 class="text-sm font-medium text-red-800">Errores:</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Info -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-blue-700">
                        <strong>Asignación Masiva:</strong> Permite asignar múltiples usuarios a una unidad organizacional en una sola operación.
                        Límite máximo: 100 usuarios por operación.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" action="{{ route('assignments.bulk.store') }}" id="bulkAssignForm" x-data="bulkAssignment()">
            @csrf

            <x-card>
                <div class="p-6">
                    <!-- Unidad Organizacional -->
                    <div class="mb-6">
                        <label for="organization_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Unidad Organizacional Destino <span class="text-red-500">*</span>
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
                    </div>

                    <!-- Selección de Usuarios -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Usuarios a Asignar <span class="text-red-500">*</span>
                        </label>

                        <x-card class="overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex gap-2">
                                        <button type="button" @click="selectAll()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Seleccionar Todos
                                        </button>
                                        <button type="button" @click="deselectAll()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Deseleccionar Todos
                                        </button>
                                    </div>
                                    <span x-text="selectedCount + ' seleccionados'" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        0 seleccionados
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 max-h-96 overflow-y-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($users as $user)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox"
                                                       name="user_ids[]"
                                                       value="{{ $user->id }}"
                                                       id="user_{{ $user->id }}"
                                                       @change="updateCount()"
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                       {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                            </div>
                                            <div class="ml-3">
                                                <label for="user_{{ $user->id }}" class="font-medium text-gray-700 cursor-pointer">
                                                    {{ $user->full_name }}
                                                </label>
                                                <p class="text-sm text-gray-500">DNI: {{ $user->dni }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </x-card>
                        @error('user_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                        </div>
                    </div>

                    <!-- Advertencia -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-yellow-800 mb-2">Atención:</h3>
                                <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                    <li>Todas las asignaciones serán creadas como <strong>secundarias</strong></li>
                                    <li>Los usuarios serán notificados por correo electrónico</li>
                                    <li>Si algún usuario ya tiene asignación en esa unidad, se omitirá</li>
                                    <li>El proceso puede tardar varios segundos según la cantidad de usuarios</li>
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
                    <button type="submit"
                            id="submitBtn"
                            :disabled="selectedCount === 0"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                            :class="selectedCount === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Asignar Usuarios
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bulkAssignment() {
    return {
        selectedCount: 0,

        init() {
            this.updateCount();
        },

        updateCount() {
            this.selectedCount = document.querySelectorAll('input[name="user_ids[]"]:checked').length;
        },

        selectAll() {
            document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = true);
            this.updateCount();
        },

        deselectAll() {
            document.querySelectorAll('input[name="user_ids[]"]').forEach(cb => cb.checked = false);
            this.updateCount();
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkAssignForm');

    form.addEventListener('submit', function(e) {
        const count = document.querySelectorAll('input[name="user_ids[]"]:checked').length;

        if (count === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un usuario');
            return false;
        }

        if (count > 100) {
            e.preventDefault();
            alert('No puede seleccionar más de 100 usuarios a la vez');
            return false;
        }

        if (!confirm(`¿Está seguro que desea asignar ${count} usuarios?`)) {
            e.preventDefault();
            return false;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Procesando...';
        submitBtn.disabled = true;
    });
});
</script>
@endpush
