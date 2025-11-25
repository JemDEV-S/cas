@extends('layouts.app')

@section('title', 'Crear Unidad Organizacional')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Crear Unidad Organizacional</h2>
                <p class="mt-1 text-sm text-gray-600">Complete el formulario para crear una nueva unidad</p>
            </div>
            <x-button variant="secondary" onclick="window.location='{{ route('organizational-units.index') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>
        </div>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('organizational-units.store') }}" class="space-y-6">
        @csrf

        <!-- Información Básica -->
        <x-card title="Información Básica">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    label="Código"
                    name="code"
                    type="text"
                    :value="old('code')"
                    placeholder="UO-001"
                    required
                />

                <x-form.input
                    label="Nombre"
                    name="name"
                    type="text"
                    :value="old('name')"
                    placeholder="Dirección General"
                    required
                />

                <x-form.input
                    label="Tipo"
                    name="type"
                    type="text"
                    :value="old('type')"
                    placeholder="direccion, gerencia, departamento..."
                    required
                />

                <x-form.select
                    label="Unidad Padre"
                    name="parent_id"
                    :value="old('parent_id')"
                >
                    <option value="">Sin padre (Raíz)</option>
                    @foreach($parentUnits as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->code }})</option>
                    @endforeach
                </x-form.select>

                <x-form.input
                    label="Orden"
                    name="order"
                    type="number"
                    :value="old('order', 0)"
                    min="0"
                />

                <div class="flex items-center h-full pt-7">
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <span class="ml-2 text-sm text-gray-700">Unidad Activa</span>
                    </label>
                </div>
            </div>

            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Descripción de la unidad organizacional"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <x-button type="button" variant="secondary" onclick="window.location='{{ route('organizational-units.index') }}'">
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Crear Unidad
            </x-button>
        </div>
    </form>
</div>
@endsection
