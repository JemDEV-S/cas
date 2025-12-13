@extends('layouts.app')

@section('title', 'Criterios de Evaluación')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Criterios de Evaluación</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestiona los criterios para cada fase de evaluación</p>
                </div>
                @can('manage-criteria')
                <div class="flex space-x-3">
                    <a href="{{ route('evaluation-criteria.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Criterio
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Búsqueda</h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('evaluation-criteria.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                        <select name="phase_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todas las fases</option>
                            @foreach($phases as $phase)
                                <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="active_only" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="1" {{ request('active_only') == '1' ? 'selected' : '' }}>Solo activos</option>
                            <option value="0" {{ request('active_only') == '0' ? 'selected' : '' }}>Solo inactivos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="system_only" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="1" {{ request('system_only') == '1' ? 'selected' : '' }}>Solo sistema</option>
                            <option value="0" {{ request('system_only') == '0' ? 'selected' : '' }}>Solo personalizados</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="space-y-6">
            @forelse($criteriaByPhase as $phaseName => $criteria)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <i class="fas fa-clipboard-check text-white text-xl mr-3"></i>
                            <h2 class="text-xl font-semibold text-white">{{ $phaseName }}</h2>
                        </div>
                        <span class="bg-white text-indigo-600 px-3 py-1 rounded-full text-sm font-medium">
                            {{ $criteria->count() }} criterios
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 60px">
                                        Orden
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px">
                                        Puntaje
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 100px">
                                        Peso
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px">
                                        Tipo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 140px">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($criteria as $criterion)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $criterion->order }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <code class="text-indigo-600 font-mono">{{ $criterion->code }}</code>
                                            @if($criterion->is_system)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-shield-alt mr-1"></i>
                                                    Sistema
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $criterion->name }}</div>
                                            <div class="mt-1 flex items-center space-x-2">
                                                @if($criterion->requires_comment)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-comment mr-1"></i>
                                                        Comentario
                                                    </span>
                                                @endif
                                                @if($criterion->requires_evidence)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-100 text-cyan-800">
                                                        <i class="fas fa-paperclip mr-1"></i>
                                                        Evidencia
                                                    </span>
                                                @endif
                                            </div>
                                            @if($criterion->description)
                                                <p class="mt-1 text-sm text-gray-500">{{ Str::limit($criterion->description, 60) }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $criterion->min_score }} - {{ $criterion->max_score }} pts
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $criterion->weight }}x
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $typeColors = [
                                                'NUMERIC' => 'bg-green-100 text-green-800',
                                                'PERCENTAGE' => 'bg-blue-100 text-blue-800',
                                                'QUALITATIVE' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$criterion->score_type->value] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $criterion->score_type->value }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($criterion->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('evaluation-criteria.show', $criterion->id) }}"
                                               class="text-indigo-600 hover:text-indigo-900"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('manage-criteria')
                                                @if(!$criterion->is_system)
                                                <a href="{{ route('evaluation-criteria.edit', $criterion->id) }}"
                                                   class="text-indigo-600 hover:text-indigo-900"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-clipboard-list text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay criterios de evaluación</h3>
                    <p class="text-gray-500 mb-6">Los criterios configurados aparecerán aquí</p>
                    @can('manage-criteria')
                    <a href="{{ route('evaluation-criteria.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Primer Criterio
                    </a>
                    @endcan
                </div>
            </div>
            @endforelse

            <!-- Resumen de Puntajes -->
            @if($criteriaByPhase->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Resumen de Puntajes</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($criteriaByPhase as $phaseName => $criteria)
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-6 border border-indigo-100">
                            <h4 class="text-lg font-semibold text-indigo-900 mb-4">{{ $phaseName }}</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Total criterios:</span>
                                    <span class="font-semibold text-gray-900">{{ $criteria->count() }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Puntaje máximo:</span>
                                    <span class="font-semibold text-gray-900">{{ $criteria->sum('max_score') }} pts</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Puntaje ponderado:</span>
                                    <span class="font-semibold text-green-600">
                                        {{ $criteria->sum(fn($c) => $c->max_score * $c->weight) }} pts
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Animaciones suaves */
.transition-colors {
    transition: background-color 0.2s ease-in-out;
}

/* Hover effects */
.hover\:bg-gray-50:hover {
    background-color: #f9fafb;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endsection
