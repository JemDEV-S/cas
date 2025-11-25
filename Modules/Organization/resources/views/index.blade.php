@extends('layouts.app')

@section('title', 'Unidades Organizacionales')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Unidades Organizacionales</h2>
            <p class="mt-1 text-sm text-gray-600">Gestión de la estructura organizacional</p>
        </div>
        <div class="flex space-x-2">
            @can('organization.view.tree')
            <x-button variant="secondary" onclick="window.location='{{ route('organizational-units.tree') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
                Ver Árbol
            </x-button>
            @endcan

            @can('organization.create.unit')
            <x-button variant="primary" onclick="window.location='{{ route('organizational-units.create') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Unidad
            </x-button>
            @endcan
        </div>
    </div>

    <!-- Filtros -->
    <x-card>
        <form method="GET" action="{{ route('organizational-units.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-form.input
                    label="Buscar"
                    name="search"
                    type="text"
                    :value="request('search')"
                    placeholder="Código, nombre..."
                />

                <x-form.select
                    label="Tipo"
                    name="type"
                    :value="request('type')"
                >
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select
                    label="Estado"
                    name="status"
                    :value="request('status')"
                >
                    <option value="">Todos</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </x-form.select>

                <div class="flex items-end space-x-2">
                    <x-button type="submit" variant="primary">Filtrar</x-button>
                    <x-button type="button" variant="secondary" onclick="window.location='{{ route('organizational-units.index') }}'">
                        Limpiar
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Tabla de Unidades -->
    <x-card>
        <x-table :headers="['Código', 'Nombre', 'Tipo', 'Padre', 'Nivel', 'Estado', 'Acciones']">
            @forelse($units as $unit)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-4 text-sm font-medium text-gray-900">{{ $unit->code }}</td>
                <td class="px-3 py-4 text-sm text-gray-900">{{ $unit->name }}</td>
                <td class="px-3 py-4 text-sm text-gray-600">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($unit->type) }}
                    </span>
                </td>
                <td class="px-3 py-4 text-sm text-gray-600">{{ $unit->parent?->name ?? '-' }}</td>
                <td class="px-3 py-4 text-sm text-gray-600">{{ $unit->level }}</td>
                <td class="px-3 py-4 text-sm">
                    @if($unit->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Inactivo
                        </span>
                    @endif
                </td>
                <td class="px-3 py-4 text-sm text-right space-x-2">
                    @can('organization.view.unit')
                    <a href="{{ route('organizational-units.show', $unit) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                    @endcan
                    @can('organization.update.unit')
                    <a href="{{ route('organizational-units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                    No se encontraron unidades organizacionales
                </td>
            </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $units->links() }}
        </div>
    </x-card>
</div>
@endsection
