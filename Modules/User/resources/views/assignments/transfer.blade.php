@extends('layouts.app')

@section('title', 'Transferir Usuarios')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Transferir Usuarios entre Unidades</h1>
                    <p class="mt-1 text-sm text-gray-600">Transferir todos los usuarios activos de una unidad a otra</p>
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
                        <strong>Transferencia de Usuarios:</strong> Esta operación transferirá todos los usuarios activos de una unidad a otra.
                        Las asignaciones antiguas serán finalizadas y se crearán nuevas asignaciones.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" action="{{ route('assignments.transfer.store') }}" id="transferForm" x-data="transferData()">
            @csrf

            <x-card>
                <div class="p-6">
                    <!-- Unidad Origen -->
                    <div class="mb-6">
                        <label for="from_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline text-red-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Unidad Organizacional Origen <span class="text-red-500">*</span>
                        </label>
                        <select name="from_unit_id" id="from_unit_id"
                                @change="updateSummary()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('from_unit_id') border-red-300 @enderror"
                                required>
                            <option value="">Seleccione unidad origen</option>
                            @foreach($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}"
                                        data-name="{{ $unit->name }}"
                                        {{ old('from_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('from_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Unidad desde donde se transferirán los usuarios</p>
                    </div>

                    <!-- Icono de transferencia -->
                    <div class="flex justify-center mb-6">
                        <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Unidad Destino -->
                    <div class="mb-6">
                        <label for="to_unit_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Unidad Organizacional Destino <span class="text-red-500">*</span>
                        </label>
                        <select name="to_unit_id" id="to_unit_id"
                                @change="updateSummary()"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('to_unit_id') border-red-300 @enderror"
                                required>
                            <option value="">Seleccione unidad destino</option>
                            @foreach($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}"
                                        data-name="{{ $unit->name }}"
                                        {{ old('to_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('to_unit_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Unidad hacia donde se transferirán los usuarios</p>
                    </div>

                    <!-- Fecha de transferencia -->
                    <div class="mb-6">
                        <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Fecha de Transferencia <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="transfer_date" id="transfer_date"
                               @change="updateSummary()"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('transfer_date') border-red-300 @enderror"
                               value="{{ old('transfer_date', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               required>
                        @error('transfer_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Fecha en que se hará efectiva la transferencia</p>
                    </div>

                    <!-- Resumen de la transferencia -->
                    <div x-show="showSummary" x-transition class="mb-6" style="display: none;">
                        <x-card class="bg-gray-50">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <h3 class="font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Resumen de Transferencia
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <span class="block text-sm font-medium text-red-700 mb-1">Desde:</span>
                                        <p x-text="fromUnitName" class="text-gray-900"></p>
                                    </div>
                                    <div>
                                        <span class="block text-sm font-medium text-green-700 mb-1">Hacia:</span>
                                        <p x-text="toUnitName" class="text-gray-900"></p>
                                    </div>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-700 mb-1">Fecha efectiva:</span>
                                    <p x-text="transferDateText" class="text-gray-900"></p>
                                </div>
                            </div>
                        </x-card>
                    </div>

                    <!-- Advertencia -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-yellow-800 mb-2">Importante:</h3>
                                <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                                    <li>Se transferirán <strong>todos los usuarios activos</strong> de la unidad origen</li>
                                    <li>Las asignaciones en la unidad origen terminarán el día anterior a la fecha de transferencia</li>
                                    <li>Se crearán nuevas asignaciones en la unidad destino con la fecha especificada</li>
                                    <li>Se mantendrá el tipo de asignación (principal o secundaria) de cada usuario</li>
                                    <li>Los usuarios serán notificados del cambio</li>
                                    <li><strong>Esta operación no se puede deshacer</strong></li>
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
                    <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        Transferir Usuarios
                    </button>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function transferData() {
    return {
        showSummary: false,
        fromUnitName: '',
        toUnitName: '',
        transferDateText: '',

        init() {
            this.updateSummary();
        },

        updateSummary() {
            const fromUnit = document.getElementById('from_unit_id');
            const toUnit = document.getElementById('to_unit_id');
            const transferDate = document.getElementById('transfer_date');

            if (fromUnit.value && toUnit.value && transferDate.value) {
                // Validar que no sean la misma unidad
                if (fromUnit.value === toUnit.value) {
                    alert('La unidad origen y destino deben ser diferentes');
                    toUnit.value = '';
                    this.showSummary = false;
                    return;
                }

                const fromOption = fromUnit.options[fromUnit.selectedIndex];
                const toOption = toUnit.options[toUnit.selectedIndex];

                this.fromUnitName = fromOption.dataset.name;
                this.toUnitName = toOption.dataset.name;

                const date = new Date(transferDate.value);
                this.transferDateText = date.toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                this.showSummary = true;
            } else {
                this.showSummary = false;
            }
        }
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('transferForm');

    form.addEventListener('submit', function(e) {
        const fromUnit = document.getElementById('from_unit_id');
        const toUnit = document.getElementById('to_unit_id');

        if (fromUnit.value === toUnit.value) {
            e.preventDefault();
            alert('La unidad origen y destino deben ser diferentes');
            return false;
        }

        if (!confirm('¿Está seguro que desea transferir todos los usuarios de una unidad a otra? Esta operación no se puede deshacer.')) {
            e.preventDefault();
            return false;
        }

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 inline animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Procesando transferencia...';
        submitBtn.disabled = true;
    });
});
</script>
@endpush
