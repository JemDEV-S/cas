@extends('layouts.app')

@section('title', 'Crear Rol')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Crear Nuevo Rol</h2>
        <p class="mt-1 text-sm text-gray-600">Define un nuevo rol y sus permisos asociados</p>
    </div>

    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Información Básica -->
        <x-card title="Información del Rol">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                        placeholder="Ej: Administrador"
                    >
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">
                        Identificador (Slug) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        value="{{ old('slug') }}"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('slug') border-red-300 @enderror"
                        placeholder="Ej: administrador"
                        pattern="[a-z0-9\-]+"
                    >
                    <p class="mt-1 text-xs text-gray-500">Solo letras minúsculas, números y guiones</p>
                    @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Descripción
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('description') border-red-300 @enderror"
                        placeholder="Describe las responsabilidades de este rol..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Rol activo
                        </label>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Permisos -->
        <x-card title="Permisos del Rol">
            <div class="space-y-4">
                @if($permissions->count() > 0)
                    @foreach($permissions as $module => $modulePermissions)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3 uppercase">{{ $module }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($modulePermissions as $permission)
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    id="permission_{{ $permission->id }}"
                                    value="{{ $permission->id }}"
                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <label for="permission_{{ $permission->id }}" class="ml-2 block text-sm text-gray-700">
                                    {{ $permission->name }}
                                    @if($permission->description)
                                    <span class="text-xs text-gray-500 block">{{ $permission->description }}</span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No hay permisos disponibles</p>
                @endif

                @error('permissions')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <!-- Acciones -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Crear Rol
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Auto-generar slug desde el nombre
    document.getElementById('name').addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });
</script>
@endpush
@endsection
