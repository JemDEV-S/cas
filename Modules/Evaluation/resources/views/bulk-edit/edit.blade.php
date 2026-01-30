@extends('layouts.app')

@section('title', 'Edición Masiva - ' . $jobPosting->title)

@section('content')
<div class="container-fluid px-4 py-6" x-data="bulkEditTable()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edición Masiva de Evaluaciones</h1>
                <p class="mt-1 text-sm text-gray-600">
                    <strong>Convocatoria:</strong> {{ $jobPosting->title }} ({{ $jobPosting->code }}) |
                    <strong>Fase:</strong> {{ $phase->name }}
                </p>
            </div>
            <div>
                <a href="{{ route('evaluation.bulk-edit.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Búsqueda por nombre/DNI -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Postulante</label>
                <input
                    type="text"
                    x-model="filters.search"
                    @input.debounce.500ms="applyFilters()"
                    placeholder="Nombre o DNI..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <!-- Filtro por rango de puntaje -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puntaje Mínimo</label>
                <input
                    type="number"
                    x-model="filters.score_min"
                    @input.debounce.500ms="applyFilters()"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Puntaje Máximo</label>
                <input
                    type="number"
                    x-model="filters.score_max"
                    @input.debounce.500ms="applyFilters()"
                    step="0.01"
                    min="0"
                    placeholder="100.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                />
            </div>

            <!-- Filtro por estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select
                    x-model="filters.status"
                    @change="applyFilters()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                >
                    <option value="">Todos</option>
                    <option value="SUBMITTED">Enviado</option>
                    <option value="MODIFIED">Modificado</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <button
                @click="clearFilters()"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
            >
                Limpiar filtros
            </button>
            <div class="text-sm text-gray-600">
                Mostrando <span x-text="filteredEvaluations.length"></span> evaluación(es)
            </div>
        </div>
    </div>

    <!-- Indicador de guardado global -->
    <div x-show="saving" class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center z-50">
        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
    </div>

    <!-- Tabla editable -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">#</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Postulante</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">DNI</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Cargo</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Estado</th>

                        <!-- Columnas dinámicas por criterio -->
                        @foreach($criteria as $criterion)
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-indigo-50" title="{{ $criterion->description }}">
                            {{ $criterion->name }}
                            <br>
                            <span class="text-xs font-normal text-gray-400">({{ $criterion->min_score }}-{{ $criterion->max_score }})</span>
                        </th>
                        @endforeach

                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap bg-green-50">Puntaje Total</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(evaluation, index) in filteredEvaluations" :key="evaluation.id">
                        <tr :class="{'bg-gray-50': index % 2 === 0}">
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900" x-text="evaluation.application.full_name"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500" x-text="evaluation.application.dni"></td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="evaluation.application.position_code"></span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-green-100 text-green-800': evaluation.status === 'SUBMITTED',
                                        'bg-yellow-100 text-yellow-800': evaluation.status === 'MODIFIED'
                                    }"
                                    x-text="evaluation.status_label"
                                ></span>
                            </td>

                            <!-- Inputs editables por criterio -->
                            @foreach($criteria as $criterion)
                            <td class="px-3 py-2 text-center bg-indigo-50">
                                <div class="relative">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="{{ $criterion->min_score }}"
                                        max="{{ $criterion->max_score }}"
                                        :value="getScore(evaluation, {{ $criterion->id }})"
                                        @blur="updateScore(evaluation.id, {{ $criterion->id }}, $event.target.value, $event.target, {{ $criterion->min_score }}, {{ $criterion->max_score }})"
                                        @keydown.enter="$event.target.blur()"
                                        class="w-20 px-2 py-1 text-center border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                        :disabled="!evaluation.can_edit"
                                    />
                                    <!-- Indicadores de guardado por campo -->
                                    <div class="absolute -right-6 top-1/2 transform -translate-y-1/2">
                                        <div :id="`indicator-${evaluation.id}-{{ $criterion->id }}`" class="hidden">
                                            <!-- Spinner -->
                                            <svg class="saving-spinner animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <!-- Check -->
                                            <svg class="success-icon h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <!-- Error -->
                                            <svg class="error-icon h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endforeach

                            <!-- Puntaje Total -->
                            <td class="px-3 py-2 text-center font-semibold bg-green-50">
                                <span x-text="evaluation.total_score ? parseFloat(evaluation.total_score).toFixed(2) : '0.00'"></span>
                                <span class="text-xs text-gray-500 block" x-text="'(' + (evaluation.percentage ? parseFloat(evaluation.percentage).toFixed(1) : '0.0') + '%)'"></span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-3 py-2 whitespace-nowrap text-center text-sm">
                                <a :href="`{{ url('evaluations') }}/${evaluation.id}`" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                    <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    </template>

                    <!-- Mensaje cuando no hay resultados -->
                    <tr x-show="filteredEvaluations.length === 0">
                        <td :colspan="{{ 6 + count($criteria) }}" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">No se encontraron evaluaciones con los filtros aplicados</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Instrucciones:</h4>
        <ul class="text-sm text-gray-600 space-y-1">
            <li>• Haga clic en cualquier puntaje para editarlo</li>
            <li>• Los cambios se guardan automáticamente al salir del campo (presione Enter o haga clic fuera)</li>
            <li>• Los puntajes deben estar dentro del rango indicado entre paréntesis</li>
            <li>• El puntaje total se actualiza automáticamente después de cada cambio</li>
            <li>• Todos los cambios quedan registrados en el historial de la evaluación</li>
        </ul>
    </div>
</div>

<script>
function bulkEditTable() {
    return {
        evaluations: @json($transformedEvaluations),
        criteria: @json($criteria),
        filters: {
            search: '{{ $filters["search"] }}',
            score_min: '{{ $filters["score_min"] }}',
            score_max: '{{ $filters["score_max"] }}',
            status: '',
        },
        saving: false,
        filteredEvaluations: [],

        init() {
            this.applyFilters();
        },

        applyFilters() {
            let filtered = [...this.evaluations];

            // Filtro por búsqueda
            if (this.filters.search) {
                const search = this.filters.search.toLowerCase();
                filtered = filtered.filter(e =>
                    e.application.full_name.toLowerCase().includes(search) ||
                    e.application.dni.includes(search)
                );
            }

            // Filtro por puntaje mínimo
            if (this.filters.score_min) {
                const min = parseFloat(this.filters.score_min);
                filtered = filtered.filter(e => (e.total_score || 0) >= min);
            }

            // Filtro por puntaje máximo
            if (this.filters.score_max) {
                const max = parseFloat(this.filters.score_max);
                filtered = filtered.filter(e => (e.total_score || 0) <= max);
            }

            // Filtro por estado
            if (this.filters.status) {
                filtered = filtered.filter(e => e.status === this.filters.status);
            }

            this.filteredEvaluations = filtered;
        },

        clearFilters() {
            this.filters = {
                search: '',
                score_min: '',
                score_max: '',
                status: '',
            };
            this.applyFilters();
        },

        getScore(evaluation, criterionId) {
            const key = `criterion_${criterionId}`;
            return evaluation.details[key]?.score || '';
        },

        async updateScore(evaluationId, criterionId, newScore, inputElement, minScore, maxScore) {
            // Validar que el valor no esté vacío
            if (newScore === '' || newScore === null) {
                this.showError(inputElement, evaluationId, criterionId, 'El puntaje no puede estar vacío');
                return;
            }

            const score = parseFloat(newScore);

            // Validar rango
            if (score < minScore || score > maxScore) {
                this.showError(inputElement, evaluationId, criterionId, `El puntaje debe estar entre ${minScore} y ${maxScore}`);
                return;
            }

            // Mostrar indicador de guardando
            this.showSaving(evaluationId, criterionId);
            this.saving = true;

            try {
                const response = await fetch('{{ route("evaluation.bulk-edit.update-score") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        evaluation_id: evaluationId,
                        criterion_id: criterionId,
                        score: score,
                    }),
                });

                const result = await response.json();

                if (result.success) {
                    // Actualizar datos en el frontend
                    this.updateEvaluationData(evaluationId, criterionId, result.data);

                    // Mostrar indicador de éxito
                    this.showSuccess(evaluationId, criterionId);
                } else {
                    this.showError(inputElement, evaluationId, criterionId, result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError(inputElement, evaluationId, criterionId, 'Error de conexión. Intente nuevamente.');
            } finally {
                this.saving = false;
            }
        },

        updateEvaluationData(evaluationId, criterionId, data) {
            // Buscar la evaluación en el array
            const evaluation = this.evaluations.find(e => e.id === evaluationId);
            if (evaluation) {
                const key = `criterion_${criterionId}`;

                // Actualizar o crear el detalle del criterio
                if (!evaluation.details[key]) {
                    evaluation.details[key] = {};
                }

                evaluation.details[key].score = data.score;
                evaluation.details[key].weighted_score = data.weighted_score;
                evaluation.details[key].detail_id = data.detail_id;
                evaluation.details[key].version = data.version;

                // Actualizar puntaje total
                evaluation.total_score = data.total_score;
                evaluation.percentage = data.percentage;

                // Actualizar estado a MODIFIED
                if (evaluation.status === 'SUBMITTED') {
                    evaluation.status = 'MODIFIED';
                    evaluation.status_label = 'Modificado';
                }
            }

            // Re-aplicar filtros para actualizar la vista
            this.applyFilters();
        },

        showSaving(evaluationId, criterionId) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.classList.remove('hidden');
                indicator.querySelector('.saving-spinner').classList.remove('hidden');
                indicator.querySelector('.success-icon').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.add('hidden');
            }
        },

        showSuccess(evaluationId, criterionId) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.querySelector('.saving-spinner').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.add('hidden');
                indicator.querySelector('.success-icon').classList.remove('hidden');

                // Ocultar después de 2 segundos
                setTimeout(() => {
                    indicator.classList.add('hidden');
                }, 2000);
            }
        },

        showError(inputElement, evaluationId, criterionId, message) {
            const indicator = document.getElementById(`indicator-${evaluationId}-${criterionId}`);
            if (indicator) {
                indicator.querySelector('.saving-spinner').classList.add('hidden');
                indicator.querySelector('.success-icon').classList.add('hidden');
                indicator.querySelector('.error-icon').classList.remove('hidden');
            }

            // Mostrar error visualmente en el input
            inputElement.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            // Mostrar tooltip con el error
            alert(message);

            // Restaurar border después de 3 segundos
            setTimeout(() => {
                inputElement.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                if (indicator) {
                    indicator.classList.add('hidden');
                }
            }, 3000);
        }
    };
}
</script>
@endsection
