<div class="tree-node" data-unit-id="{{ $unit->id }}">
    <div class="tree-node-header {{ !$unit->is_active ? 'inactive' : '' }}" onclick="toggleNode('{{ $unit->id }}')">
        <!-- Icono de expandir/contraer -->
        @if($unit->children->isNotEmpty())
            <svg id="icon-{{ $unit->id }}" class="expand-icon expanded text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        @else
            <div class="w-5 h-5 mr-2"></div>
        @endif

        <!-- Icono de tipo de unidad -->
        <div class="unit-icon {{ $unit->type }} mr-3">
            @if($unit->type === 'organo')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            @elseif($unit->type === 'area')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            @endif
        </div>

        <!-- Información de la unidad -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $unit->name }}</h4>
                @if(!$unit->is_active)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        Inactivo
                    </span>
                @endif
            </div>
            <div class="flex items-center mt-1 space-x-3 text-xs text-gray-500">
                <span class="font-mono">{{ $unit->code }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                    {{ ucfirst($unit->type) }}
                </span>
                @if($unit->children->isNotEmpty())
                    <span>{{ $unit->children->count() }} sub-unidad(es)</span>
                @endif
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex items-center space-x-2 ml-4" onclick="event.stopPropagation()">
            @can('organization.view.unit')
            <a href="{{ route('organizational-units.show', $unit) }}" class="p-1 text-gray-400 hover:text-blue-600" title="Ver detalles">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </a>
            @endcan

            @can('organization.update.unit')
            <a href="{{ route('organizational-units.edit', $unit) }}" class="p-1 text-gray-400 hover:text-indigo-600" title="Editar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            @endcan
        </div>
    </div>

    <!-- Hijos -->
    @if($unit->children->isNotEmpty())
        <div id="children-{{ $unit->id }}" class="tree-children">
            @foreach($unit->children as $child)
                @include('organization::partials.tree-node', ['unit' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
