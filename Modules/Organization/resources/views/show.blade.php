@extends('layouts.app')

@section('title', 'Detalle de Unidad Organizacional')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Detalle de Unidad Organizacional</h2>
            <p class="mt-1 text-sm text-gray-600">Información completa de la unidad</p>
        </div>
        <div class="flex space-x-2">
            <x-button variant="secondary" onclick="window.location='{{ route('organizational-units.index') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </x-button>

            @can('organization.update.unit')
            <x-button variant="primary" onclick="window.location='{{ route('organizational-units.edit', $organizationalUnit) }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </x-button>
            @endcan
        </div>
    </div>

    <!-- Información Principal -->
    <x-card>
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $organizationalUnit->name }}</h3>
                    @if($organizationalUnit->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Inactivo
                        </span>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $organizationalUnit->code }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($organizationalUnit->type) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Unidad Padre</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($organizationalUnit->parent)
                                <a href="{{ route('organizational-units.show', $organizationalUnit->parent) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $organizationalUnit->parent->name }}
                                </a>
                            @else
                                Raíz
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nivel</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $organizationalUnit->level }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Orden</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $organizationalUnit->order }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Path</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $organizationalUnit->path }}</dd>
                    </div>
                </div>

                @if($organizationalUnit->description)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $organizationalUnit->description }}</dd>
                </div>
                @endif
            </div>
        </div>
    </x-card>

    <!-- Unidades Hijas -->
    @if($organizationalUnit->children->isNotEmpty())
    <x-card title="Unidades Hijas">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($organizationalUnit->children as $child)
                <div class="flex items-start p-4 border border-gray-200 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <a href="{{ route('organizational-units.show', $child) }}" class="text-lg font-medium text-gray-900 hover:text-blue-600">
                            {{ $child->name }}
                        </a>
                        <p class="mt-1 text-sm text-gray-500">{{ $child->code }} - {{ ucfirst($child->type) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <!-- Jerarquía -->
    @if($organizationalUnit->ancestors->isNotEmpty())
    <x-card title="Jerarquía">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                @foreach($organizationalUnit->getAllAncestors() as $ancestor)
                    <li>
                        <div class="flex items-center">
                            <a href="{{ route('organizational-units.show', $ancestor) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                {{ $ancestor->name }}
                            </a>
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </li>
                @endforeach
                <li>
                    <span class="text-sm font-medium text-gray-900">{{ $organizationalUnit->name }}</span>
                </li>
            </ol>
        </nav>
    </x-card>
    @endif

    <!-- Acciones Peligrosas -->
    @can('organization.delete.unit')
    @if(!$organizationalUnit->hasChildren())
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-lg font-medium text-red-900">Zona de Peligro</h4>
                <p class="text-sm text-gray-600">Las siguientes acciones son irreversibles</p>
            </div>
            <form method="POST" action="{{ route('organizational-units.destroy', $organizationalUnit) }}" onsubmit="return confirm('¿Está seguro de eliminar esta unidad organizacional?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar Unidad
                </x-button>
            </form>
        </div>
    </x-card>
    @endif
    @endcan
</div>
@endsection
