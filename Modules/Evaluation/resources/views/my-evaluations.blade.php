@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            Mis Evaluaciones Completadas
        </h1>
        <p class="text-gray-600">Consulta y gestiona tus evaluaciones finalizadas</p>
    </div>

    <!-- Alert for Problematic Evaluations -->
    @if(isset($stats['problematic']) && $stats['problematic'] > 0)
    <div class="bg-red-50 border-l-4 border-red-500 p-6 mb-8 rounded-lg shadow-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-semibold text-red-800 mb-2">
                    Atención: {{ $stats['problematic'] }} {{ $stats['problematic'] == 1 ? 'evaluación requiere' : 'evaluaciones requieren' }} tu atención
                </h3>
                <p class="text-red-700 mb-3">
                    {{ $stats['problematic'] == 1 ? 'Hay una evaluación' : 'Hay evaluaciones' }} con puntaje menor a 35 puntos que no {{ $stats['problematic'] == 1 ? 'tiene' : 'tienen' }} comentarios.
                    Es <strong>obligatorio</strong> agregar comentarios explicativos cuando el puntaje es menor a 35 puntos para justificar la calificación.
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('evaluation.my-evaluations', ['problematic' => '1']) }}"
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        <i class="fas fa-filter mr-2"></i>
                        Ver Evaluaciones Problemáticas
                    </a>
                    <button type="button"
                            onclick="document.getElementById('helpProblematic').classList.toggle('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                        <i class="fas fa-question-circle mr-2"></i>
                        ¿Por qué es importante?
                    </button>
                </div>
                <div id="helpProblematic" class="hidden mt-4 p-4 bg-white rounded-lg border border-red-200">
                    <h4 class="font-semibold text-gray-900 mb-2">¿Por qué debo agregar comentarios cuando el puntaje es menor a 35 puntos?</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        <li><strong>Es obligatorio:</strong> Los comentarios son requeridos por normativa para puntajes menores a 35 puntos</li>
                        <li>Los comentarios justifican y documentan la calificación otorgada</li>
                        <li>Ayudan al postulante a entender las razones específicas de su puntuación baja</li>
                        <li>Proporcionan transparencia y trazabilidad en el proceso de evaluación</li>
                        <li>Protegen al evaluador ante posibles reclamos, impugnaciones o recursos administrativos</li>
                        <li>Demuestran objetividad y fundamentación técnica en la evaluación</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Completadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Enviadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['submitted'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-paper-plane text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Modificadas</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['modified'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Promedio</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['average_score'] ?? 0, 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Con Problemas</p>
                    <p class="text-3xl font-bold text-red-600">{{ $stats['problematic'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Puntaje &lt;35 pts sin comentarios</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
            </h3>
            <button type="button"
                    onclick="toggleAdvancedFilters()"
                    class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                <i class="fas fa-cog mr-1"></i>Filtros Avanzados
            </button>
        </div>

        <form method="GET" action="{{ route('evaluation.my-evaluations') }}" class="space-y-4">
            <!-- Barra de Búsqueda -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i> Buscar
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por nombre del postulante, DNI o unidad orgánica..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>

            <!-- Filtros Básicos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                    <select name="phase_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Todas las fases</option>
                        @foreach($phases ?? [] as $phase)
                            <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                {{ $phase->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unidad Orgánica</label>
                    <select name="requesting_unit_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Todas las unidades</option>
                        @foreach($organizationalUnits ?? [] as $unit)
                            <option value="{{ $unit->id }}" {{ request('requesting_unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="">Todos los estados</option>
                        <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Enviadas</option>
                        <option value="MODIFIED" {{ request('status') == 'MODIFIED' ? 'selected' : '' }}>Modificadas</option>
                    </select>
                </div>
            </div>

            <!-- Filtros Avanzados (Ocultos por defecto) -->
            <div id="advancedFilters" class="hidden space-y-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Puntaje Mínimo (%)</label>
                        <input type="number"
                               name="min_score"
                               value="{{ request('min_score') }}"
                               min="0"
                               max="100"
                               step="0.1"
                               placeholder="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Puntaje Máximo (%)</label>
                        <input type="number"
                               name="max_score"
                               value="{{ request('max_score') }}"
                               min="0"
                               max="100"
                               step="0.1"
                               placeholder="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comentarios</label>
                        <select name="has_comments" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">Todas</option>
                            <option value="1" {{ request('has_comments') == '1' ? 'selected' : '' }}>Con comentarios</option>
                            <option value="0" {{ request('has_comments') == '0' ? 'selected' : '' }}>Sin comentarios</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Evaluaciones Problemáticas</label>
                        <select name="problematic" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="">Todas</option>
                            <option value="1" {{ request('problematic') == '1' ? 'selected' : '' }}>Solo problemáticas</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Envío Desde</label>
                        <input type="date"
                               name="date_from"
                               value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Envío Hasta</label>
                        <input type="date"
                               name="date_to"
                               value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-between pt-4">
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center">
                        <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                    </button>
                    <a href="{{ route('evaluation.my-evaluations') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="per_page" class="text-sm text-gray-600">Mostrar:</label>
                        <select name="per_page"
                                id="per_page"
                                onchange="this.form.submit()"
                                class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="text-sm text-gray-600">
                        Mostrando {{ $evaluations->count() }} de {{ $evaluations->total() }} evaluaciones
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Actions -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2">
            <button type="button"
                    onclick="window.print()"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
                <i class="fas fa-print mr-2"></i>
                Imprimir
            </button>
        </div>
        <div class="text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            Las evaluaciones resaltadas en rojo requieren comentarios
        </div>
    </div>

    <!-- Evaluations Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perfil</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidad Orgánica</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntaje</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Envío</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modificación</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($evaluations as $evaluation)
                    @php
                        // Detectar si es problemática (puntaje absoluto < 35)
                        $isProblematic = ($evaluation->total_score !== null && $evaluation->total_score < 35) &&
                                       (empty($evaluation->general_comments) || trim($evaluation->general_comments) === '');
                        $rowClass = $isProblematic ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50';
                    @endphp
                    <tr class="{{ $rowClass }} transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                @php
                                    $statusColors = [
                                        'SUBMITTED' => 'bg-green-100 text-green-800',
                                        'MODIFIED' => 'bg-blue-100 text-blue-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$evaluation->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $evaluation->status->label() }}
                                </span>
                                @if($isProblematic)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 flex items-center gap-1"
                                          title="Evaluación con puntaje menor a 35 puntos sin comentarios - Comentarios obligatorios">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Requiere Atención
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->jobPosting->title ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->jobPosting->code ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->profile_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->positionCode->code ?? '' }}
                                    @if($evaluation->evaluatorAssignment->application->jobProfile->positionCode->name ?? null)
                                        - {{ $evaluation->evaluatorAssignment->application->jobProfile->positionCode->name }}
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application && $evaluation->evaluatorAssignment->application->jobProfile)
                                <div class="text-sm text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->requestingUnit->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->evaluatorAssignment->application->jobProfile->requestingUnit->code ?? '' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->is_anonymous || ($evaluation->metadata['is_anonymous'] ?? false))
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-user-secret text-gray-400 mr-2"></i> Anónimo
                                </div>
                            @elseif($evaluation->evaluatorAssignment && $evaluation->evaluatorAssignment->application)
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluatorAssignment->application->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    DNI: {{ $evaluation->evaluatorAssignment->application->dni ?? 'N/A' }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $evaluation->phase->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->total_score !== null)
                                <div class="flex flex-col">
                                    <div class="text-sm font-semibold {{ $evaluation->total_score < 35 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($evaluation->total_score, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">de {{ number_format($evaluation->max_possible_score, 2) }}</div>
                                    @if($evaluation->percentage !== null)
                                        <div class="mt-1">
                                            <span class="px-2 py-0.5 text-xs font-bold rounded {{ $evaluation->total_score < 35 ? 'bg-red-100 text-red-700' : ($evaluation->percentage >= 70 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700') }}">
                                                {{ number_format($evaluation->percentage, 1) }}%
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Sin puntaje</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(!empty($evaluation->general_comments) && trim($evaluation->general_comments) !== '')
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle"></i> Sí
                                    </span>
                                    <button type="button"
                                            onclick="showComments({{ $evaluation->id }})"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                            title="Ver comentarios">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div id="comments-{{ $evaluation->id }}" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs text-gray-700 border border-gray-200">
                                    {{ $evaluation->general_comments }}
                                </div>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    <i class="fas fa-times-circle"></i> No
                                </span>
                                @if($evaluation->total_score !== null && $evaluation->total_score < 35)
                                    <div class="mt-1 text-xs text-red-600 font-bold">
                                        <i class="fas fa-exclamation-triangle"></i> OBLIGATORIO
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->submitted_at)
                                <div class="text-sm text-gray-900">{{ $evaluation->submitted_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $evaluation->submitted_at->format('H:i') }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $evaluation->submitted_at->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Sin fecha</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($evaluation->modified_at)
                                <div class="text-xs text-blue-600 font-medium">
                                    <i class="fas fa-edit"></i> Modificada
                                </div>
                                <div class="text-xs text-gray-500">{{ $evaluation->modified_at->format('d/m/Y H:i') }}</div>
                                @if($evaluation->modifiedBy)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Por: {{ $evaluation->modifiedBy->name ?? 'N/A' }}
                                    </div>
                                @endif
                                @if($evaluation->modification_reason)
                                    <button type="button"
                                            onclick="showModificationReason({{ $evaluation->id }})"
                                            class="text-xs text-blue-600 hover:text-blue-800 mt-1"
                                            title="Ver razón de modificación">
                                        <i class="fas fa-info-circle"></i> Ver razón
                                    </button>
                                    <div id="mod-reason-{{ $evaluation->id }}" class="hidden mt-2 p-2 bg-blue-50 rounded text-xs text-gray-700 border border-blue-200">
                                        {{ $evaluation->modification_reason }}
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">Sin modificar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('evaluation.show', $evaluation->id) }}"
                                   class="inline-flex items-center justify-center px-3 py-1.5 border-2 border-blue-500 text-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-50 transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye mr-1"></i> Ver
                                </a>

                                <button type="button"
                                        onclick="confirmDelete({{ $evaluation->id }}, '{{ addslashes($evaluation->evaluatorAssignment->application->full_name ?? 'esta evaluación') }}')"
                                        class="inline-flex items-center justify-center px-3 py-1.5 border-2 border-red-500 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-50 transition-colors"
                                        title="Eliminar evaluación">
                                    <i class="fas fa-trash mr-1"></i> Eliminar
                                </button>

                                <form id="delete-form-{{ $evaluation->id }}"
                                      action="{{ route('evaluation.destroy', $evaluation->id) }}"
                                      method="POST"
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No se encontraron evaluaciones</p>
                                <p class="text-gray-400 text-sm mt-2">Intenta ajustar los filtros de búsqueda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($evaluations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $evaluations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-gray-50 rounded-xl p-6 border border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle mr-2"></i>Leyenda de Indicadores
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Estados -->
            <div>
                <h5 class="text-xs font-medium text-gray-700 mb-2">Estados:</h5>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Enviada</span>
                        <span class="text-xs text-gray-600">- Evaluación completada y enviada</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Modificada</span>
                        <span class="text-xs text-gray-600">- Evaluación modificada por un administrador</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-triangle"></i> Requiere Atención
                        </span>
                        <span class="text-xs text-gray-600">- Puntaje menor a 35 puntos sin comentarios (obligatorio)</span>
                    </div>
                </div>
            </div>

            <!-- Puntajes -->
            <div>
                <h5 class="text-xs font-medium text-gray-700 mb-2">Códigos de Puntaje:</h5>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-green-100 text-green-700">≥70%</span>
                        <span class="text-xs text-gray-600">- Puntaje alto (aprobatorio)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-yellow-100 text-yellow-700">35-69%</span>
                        <span class="text-xs text-gray-600">- Puntaje medio</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">&lt;35 pts</span>
                        <span class="text-xs text-gray-600">- Puntaje bajo (<strong>comentarios obligatorios</strong>)</span>
                    </div>
                </div>
            </div>

            <!-- Comentarios -->
            <div>
                <h5 class="text-xs font-medium text-gray-700 mb-2">Comentarios:</h5>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                            <i class="fas fa-check-circle"></i> Sí
                        </span>
                        <span class="text-xs text-gray-600">- Incluye comentarios</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                            <i class="fas fa-times-circle"></i> No
                        </span>
                        <span class="text-xs text-gray-600">- Sin comentarios</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-300">
            <p class="text-xs text-gray-600">
                <i class="fas fa-exclamation-circle text-red-500 mr-1"></i>
                <strong>Importante:</strong> Las evaluaciones resaltadas en color rojo indican que el puntaje es menor a 35 puntos y no tienen comentarios.
                Es <strong>OBLIGATORIO</strong> agregar comentarios explicativos cuando el puntaje es menor a 35 puntos para justificar la calificación y cumplir con los requisitos normativos.
            </p>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Eliminación</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Estás seguro de que deseas eliminar la evaluación de <strong id="evaluationName"></strong>?
                </p>
                <p class="text-sm text-red-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Esta acción permitirá que vuelvas a evaluar esta postulación desde cero.
                </p>
            </div>
            <div class="items-center px-4 py-3 space-x-3">
                <button id="confirmDeleteBtn"
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    <i class="fas fa-trash mr-2"></i> Eliminar
                </button>
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentDeleteId = null;

// Toggle Advanced Filters
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    if (advancedFilters.classList.contains('hidden')) {
        advancedFilters.classList.remove('hidden');
    } else {
        advancedFilters.classList.add('hidden');
    }
}

// Show/Hide Comments
function showComments(evaluationId) {
    const commentsDiv = document.getElementById('comments-' + evaluationId);
    if (commentsDiv.classList.contains('hidden')) {
        commentsDiv.classList.remove('hidden');
    } else {
        commentsDiv.classList.add('hidden');
    }
}

// Show/Hide Modification Reason
function showModificationReason(evaluationId) {
    const reasonDiv = document.getElementById('mod-reason-' + evaluationId);
    if (reasonDiv.classList.contains('hidden')) {
        reasonDiv.classList.remove('hidden');
    } else {
        reasonDiv.classList.add('hidden');
    }
}

// Delete Confirmation
function confirmDelete(evaluationId, evaluationName) {
    currentDeleteId = evaluationId;
    document.getElementById('evaluationName').textContent = evaluationName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentDeleteId = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentDeleteId) {
        document.getElementById('delete-form-' + currentDeleteId).submit();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Auto-abrir filtros avanzados si hay algún filtro activo
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const advancedParams = ['min_score', 'max_score', 'has_comments', 'problematic', 'date_from', 'date_to'];
    const hasAdvancedFilters = advancedParams.some(param => urlParams.has(param) && urlParams.get(param) !== '');

    if (hasAdvancedFilters) {
        document.getElementById('advancedFilters').classList.remove('hidden');
    }
});
</script>
@endpush
