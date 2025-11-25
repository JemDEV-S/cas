@extends('layouts.app')

@section('title', 'Árbol Organizacional')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Árbol Organizacional</h2>
            <p class="mt-1 text-sm text-gray-600">Visualización jerárquica de la estructura organizacional</p>
        </div>
        <div class="flex space-x-2">
            <x-button variant="secondary" onclick="window.location='{{ route('organizational-units.index') }}'">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Ver Lista
            </x-button>

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

    <!-- Controles -->
    <x-card>
        <div class="flex items-center space-x-4">
            <button onclick="expandAll()" class="text-sm text-blue-600 hover:text-blue-900 font-medium">
                Expandir Todo
            </button>
            <span class="text-gray-300">|</span>
            <button onclick="collapseAll()" class="text-sm text-blue-600 hover:text-blue-900 font-medium">
                Contraer Todo
            </button>
            <span class="text-gray-300">|</span>
            <label class="flex items-center">
                <input type="checkbox" id="showInactive" onchange="toggleInactive()" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Mostrar inactivos</span>
            </label>
        </div>
    </x-card>

    <!-- Árbol -->
    <x-card>
        <div class="org-tree">
            @forelse($rootUnits as $root)
                @include('organization::partials.tree-node', ['unit' => $root, 'level' => 0])
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay unidades organizacionales</h3>
                    <p class="mt-1 text-sm text-gray-500">Comience creando una unidad raíz</p>
                    @can('organization.create.unit')
                    <div class="mt-6">
                        <x-button variant="primary" onclick="window.location='{{ route('organizational-units.create') }}'">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Crear Primera Unidad
                        </x-button>
                    </div>
                    @endcan
                </div>
            @endforelse
        </div>
    </x-card>
</div>

@push('styles')
<style>
.org-tree {
    font-family: system-ui, -apple-system, sans-serif;
}

.tree-node {
    margin-left: 1.5rem;
    border-left: 2px solid #e5e7eb;
    padding-left: 1rem;
    margin-top: 0.5rem;
}

.tree-node-header {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.tree-node-header:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tree-node-header.inactive {
    opacity: 0.5;
    background: #f3f4f6;
}

.tree-children {
    margin-top: 0.5rem;
}

.tree-children.collapsed {
    display: none;
}

.expand-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    transition: transform 0.2s;
}

.expand-icon.expanded {
    transform: rotate(90deg);
}

.unit-icon {
    width: 2rem;
    height: 2rem;
    flex-shrink: 0;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.unit-icon.organo {
    background: #dbeafe;
    color: #1e40af;
}

.unit-icon.area {
    background: #fef3c7;
    color: #92400e;
}

.unit-icon.sub_unidad {
    background: #d1fae5;
    color: #065f46;
}
</style>
@endpush

@push('scripts')
<script>
function toggleNode(nodeId) {
    const children = document.getElementById('children-' + nodeId);
    const icon = document.getElementById('icon-' + nodeId);

    if (children) {
        children.classList.toggle('collapsed');
        icon.classList.toggle('expanded');
    }
}

function expandAll() {
    document.querySelectorAll('.tree-children').forEach(el => {
        el.classList.remove('collapsed');
    });
    document.querySelectorAll('.expand-icon').forEach(el => {
        el.classList.add('expanded');
    });
}

function collapseAll() {
    document.querySelectorAll('.tree-children').forEach(el => {
        el.classList.add('collapsed');
    });
    document.querySelectorAll('.expand-icon').forEach(el => {
        el.classList.remove('expanded');
    });
}

function toggleInactive() {
    const showInactive = document.getElementById('showInactive').checked;
    document.querySelectorAll('.tree-node-header.inactive').forEach(el => {
        el.closest('.tree-node').style.display = showInactive ? 'block' : 'none';
    });
}

// Inicializar: contraer todos excepto el primer nivel
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tree-children').forEach((el, index) => {
        if (index > 0) {
            el.classList.add('collapsed');
        }
    });
});
</script>
@endpush
@endsection
