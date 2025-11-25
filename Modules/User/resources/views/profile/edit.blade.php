@extends('layouts.app')

@section('title', 'Editar Perfil')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Editar Perfil</h2>
                <p class="mt-1 text-sm text-gray-600">Actualiza tu información personal</p>
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
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Información Personal -->
        <x-card title="Información Personal">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input
                    label="Nombres"
                    name="first_name"
                    type="text"
                    :value="old('first_name', $user->first_name)"
                    required
                />

                <x-form.input
                    label="Apellidos"
                    name="last_name"
                    type="text"
                    :value="old('last_name', $user->last_name)"
                    required
                />

                <x-form.input
                    label="Teléfono"
                    name="phone"
                    type="text"
                    :value="old('phone', $user->phone)"
                    placeholder="987654321"
                />

                <x-form.input
                    label="Fecha de Nacimiento"
                    name="birth_date"
                    type="date"
                    :value="old('birth_date', $user->profile?->birth_date?->format('Y-m-d'))"
                />

                <x-form.select
                    label="Género"
                    name="gender"
                    :value="old('gender', $user->profile?->gender)"
                >
                    <option value="">Seleccionar</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="O">Otro</option>
                </x-form.select>

                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700">Foto de Perfil</label>
                    <input
                        type="file"
                        name="photo"
                        id="photo"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    @error('photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Dirección -->
        <x-card title="Dirección">
            <div class="grid grid-cols-1 gap-6">
                <x-form.input
                    label="Dirección"
                    name="address"
                    type="text"
                    :value="old('address', $user->profile?->address)"
                    placeholder="Av. Principal 123"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form.input
                        label="Distrito"
                        name="district"
                        type="text"
                        :value="old('district', $user->profile?->district)"
                    />

                    <x-form.input
                        label="Provincia"
                        name="province"
                        type="text"
                        :value="old('province', $user->profile?->province)"
                    />

                    <x-form.input
                        label="Departamento"
                        name="department"
                        type="text"
                        :value="old('department', $user->profile?->department)"
                    />
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
                Guardar Cambios
            </x-button>
        </div>
    </form>
</div>
@endsection
