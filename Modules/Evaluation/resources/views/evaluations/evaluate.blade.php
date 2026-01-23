@extends('layouts.app')

@section('title', 'Evaluar Postulante')

@section('page-title')
    Evaluación - {{ $evaluation->evaluatorAssignment->application->full_name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evaluation.my-evaluations') }}">Mis Evaluaciones</a></li>
    <li class="breadcrumb-item active">Evaluar</li>
@endsection

@section('content')
<div class="flex h-screen bg-gray-50" x-data="evaluationApp()" x-init="init()">
    <!-- Panel Izquierdo: CV -->
    <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header del CV -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Curriculum Vitae</h3>
                    <p class="text-sm text-blue-100 mt-1">
                        {{ $evaluation->evaluatorAssignment->application->full_name ?? 'N/A' }}
                    </p>
                </div>
                @if($cvDocument)
                <a href="{{ route('application.documents.download', $cvDocument->id) }}"
                   class="px-4 py-2 bg-white text-blue-700 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar CV
                </a>
                @endif
            </div>
        </div>

        <!-- Visor de PDF -->
        <div class="flex-1 overflow-hidden bg-gray-100">
            @if($cvDocument && $cvDocument->fileExists())
                <iframe
                    src="{{ route('evaluation.view-cv', $evaluation->id) }}"
                    class="w-full h-full border-0"
                    title="Curriculum Vitae">
                </iframe>
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">CV no disponible</p>
                        <p class="text-sm mt-1">El postulante no ha cargado su CV</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel Derecho: Formulario de Evaluación -->
    <div class="w-1/2 flex flex-col bg-gray-50">
        <!-- Header del formulario -->
        <div class="px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Formulario de Evaluación</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $evaluation->evaluatorAssignment->application->jobProfile->jobPosting->title ?? 'Convocatoria' }} -
                        {{ $evaluation->phase->name }}
                    </p>
                    @if($positionCode)
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            Código de Puesto: {{ $positionCode }}
                        </span>
                    </p>
                    @endif
                </div>

                <!-- Resumen de progreso -->
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-600" x-text="totalScore.toFixed(2)">0.00</div>
                    <p class="text-xs text-gray-500">de {{ number_format($maxTotalScore, 2) }} pts</p>
                    <div class="mt-1 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido del formulario con scroll -->
        <div class="flex-1 overflow-y-auto px-6 py-6">
            <form id="evaluationForm">
                <!-- Criterios de evaluación -->
                <div class="space-y-4">
                    @foreach($criteria as $criterion)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm"
                         data-criterion-id="{{ $criterion->id }}">
                        <!-- Header del criterio -->
                        <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $criterion->order }}. {{ $criterion->name }}
                                    </h4>
                                    @if($criterion->description)
                                        <p class="text-sm text-gray-600 mt-1">{{ $criterion->description }}</p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    Peso: {{ $criterion->weight }}x
                                </span>
                            </div>
                        </div>

                        <!-- Cuerpo del criterio -->
                        <div class="px-5 py-4 space-y-4">
                            <!-- Guía de evaluación -->
                            @if($criterion->evaluation_guide)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h5 class="text-sm font-semibold text-blue-900 mb-1">Guía de evaluación:</h5>
                                        <pre class="text-sm text-blue-800 whitespace-pre-line font-sans">{{ $criterion->evaluation_guide }}</pre>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Criterios con escalas (opciones múltiples) -->
                            @if($criterion->score_scales && count($criterion->score_scales) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Seleccione las opciones que cumple el postulante
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($criterion->score_scales as $scale)
                                        <label class="flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg cursor-pointer transition-colors">
                                            <input type="checkbox"
                                                   class="scale-checkbox mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                   name="criteria[{{ $criterion->id }}][options][]"
                                                   value="{{ $scale['puntos'] }}"
                                                   data-criterion-id="{{ $criterion->id }}"
                                                   data-max-score="{{ $criterion->max_score }}"
                                                   @change="handleCheckboxChange($event)">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-900">{{ $scale['descripcion'] }}</span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                                        +{{ $scale['puntos'] }} pts
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>

                                    <input type="hidden"
                                           class="score-input"
                                           name="criteria[{{ $criterion->id }}][score]"
                                           data-min="{{ $criterion->min_score }}"
                                           data-max="{{ $criterion->max_score }}"
                                           value="{{ $details[$criterion->id]->score ?? 0 }}">

                                    <div class="mt-3 flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <span class="text-sm font-medium text-gray-700">Puntaje calculado:</span>
                                        <div>
                                            <span class="calculated-score text-xl font-bold text-blue-600">{{ $details[$criterion->id]->score ?? 0 }}</span>
                                            <span class="text-sm text-gray-600"> / {{ number_format($criterion->max_score, 2) }} pts</span>
                                        </div>
                                    </div>
                                </div>

                            <!-- Criterios numéricos (sin escalas) -->
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Puntaje
                                        <span class="text-red-500">*</span>
                                        <span class="text-gray-500 font-normal">(Rango: {{ $criterion->min_score }} - {{ $criterion->max_score }})</span>
                                    </label>
                                    <input type="number"
                                           class="score-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           name="criteria[{{ $criterion->id }}][score]"
                                           data-min="{{ $criterion->min_score }}"
                                           data-max="{{ $criterion->max_score }}"
                                           min="{{ $criterion->min_score }}"
                                           max="{{ $criterion->max_score }}"
                                           step="0.5"
                                           value="{{ $details[$criterion->id]->score ?? '' }}"
                                           @change="handleScoreChange($event)"
                                           {{ $criterion->requires_comment ? 'required' : '' }}>
                                    <p class="invalid-feedback text-red-500 text-sm mt-1 hidden">
                                        El puntaje debe estar entre {{ $criterion->min_score }} y {{ $criterion->max_score }}
                                    </p>

                                    @if(isset($criterion->metadata['puntaje_minimo_aprobatorio']))
                                    <div class="mt-2 flex items-center gap-2 text-red-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm">Puntaje mínimo aprobatorio: {{ $criterion->metadata['puntaje_minimo_aprobatorio'] }} puntos</span>
                                    </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Comentarios -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Comentarios
                                    @if($criterion->requires_comment)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          name="criteria[{{ $criterion->id }}][comments]"
                                          rows="3"
                                          placeholder="Observaciones sobre este criterio..."
                                          {{ $criterion->requires_comment ? 'required' : '' }}>{{ $details[$criterion->id]->comments ?? '' }}</textarea>
                            </div>

                            <!-- Evidencia -->
                            @if($criterion->requires_evidence)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Evidencia
                                </label>
                                <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          name="criteria[{{ $criterion->id }}][evidence]"
                                          rows="2"
                                          placeholder="Describa la evidencia encontrada...">{{ $details[$criterion->id]->evidence ?? '' }}</textarea>
                            </div>
                            @endif

                            <!-- Puntaje ponderado -->
                            <div class="flex items-center justify-end pt-2 border-t border-gray-100">
                                <span class="text-sm text-gray-600">Puntaje ponderado: </span>
                                <span class="ml-2 text-base font-semibold text-gray-900 weighted-score">0.00</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Comentarios Generales -->
                <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h4 class="text-base font-semibold text-gray-900">Comentarios Generales</h4>
                    </div>
                    <div class="px-5 py-4">
                        <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  name="general_comments"
                                  rows="4"
                                  placeholder="Observaciones generales sobre la evaluación...">{{ $evaluation->general_comments }}</textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer con botones de acción -->
        <div class="px-6 py-4 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span>Auto-guardado activo</span>
                    </div>
                    @if($evaluation->deadline_at)
                    <div class="flex items-center gap-2 {{ $evaluation->isOverdue() ? 'text-red-600' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Vence: {{ $evaluation->deadline_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <button type="button"
                            @click="saveDraft()"
                            :disabled="isSaving"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span x-text="isSaving ? 'Guardando...' : 'Guardar Borrador'">Guardar Borrador</span>
                    </button>

                    <a href="{{ route('evaluation.index') }}"
                       class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg border border-gray-300 transition-colors">
                        Cancelar
                    </a>

                    <button type="button"
                            @click="submitEvaluation()"
                            :disabled="!canSubmit || isSubmitting"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="isSubmitting ? 'Enviando...' : 'Enviar Evaluación'">Enviar Evaluación</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Configurar CSRF token para axios (por si se usa en otros lugares)
if (typeof axios !== 'undefined') {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    } else {
        console.error('CSRF token meta tag not found!');
    }
}

function evaluationApp() {
    return {
        evaluationId: {{ $evaluation->id }},
        criteria: @json($criteria),
        totalScore: 0,
        progress: 0,
        evaluatedCount: 0,
        isSaving: false,
        isSubmitting: false,
        canSubmit: {{ $evaluation->canEdit() ? 'true' : 'false' }},
        saveTimeout: null,
        csrfTokenExpired: false,

        init() {
            // Verificar CSRF token al inicio
            this.verifyCsrfToken();
            // Inicializar checkboxes guardados
            this.initializeCheckboxes();
            // Calcular puntajes
            this.calculateWeightedScores();
        },

        verifyCsrfToken() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found in meta tag');
                alert('Error de configuración: Token CSRF no encontrado. Por favor, recarga la página.');
                this.csrfTokenExpired = true;
            }
        },

        initializeCheckboxes() {
            // Para criterios con checkboxes, restaurar el puntaje calculado y marcar checkboxes
            document.querySelectorAll('[data-criterion-id]').forEach(card => {
                const scoreInput = card.querySelector('input.score-input[type="hidden"]');
                if (scoreInput) {
                    const savedScore = parseFloat(scoreInput.value) || 0;
                    const calculatedSpan = card.querySelector('.calculated-score');
                    if (calculatedSpan) {
                        calculatedSpan.textContent = savedScore.toFixed(2);
                    }

                    // Si hay checkboxes y un score guardado, intentar marcar los checkboxes correspondientes
                    // Nota: esto es una aproximación, idealmente deberíamos guardar qué checkboxes fueron marcados
                    if (savedScore > 0) {
                        const checkboxes = card.querySelectorAll('.scale-checkbox');
                        if (checkboxes.length > 0) {
                            // Marcar checkboxes hasta alcanzar el score guardado
                            let accumulated = 0;
                            checkboxes.forEach(cb => {
                                const points = parseFloat(cb.value);
                                if (accumulated + points <= savedScore) {
                                    cb.checked = true;
                                    accumulated += points;
                                }
                            });
                        }
                    }
                }
            });
        },

        calculateWeightedScores() {
            let total = 0;
            let count = 0;

            document.querySelectorAll('[data-criterion-id]').forEach(card => {
                const criterionId = card.dataset.criterionId;
                const criterion = this.criteria.find(c => c.id == criterionId);

                if (!criterion) {
                    console.warn('Criterion not found:', criterionId);
                    return;
                }

                // Buscar el input de score dentro de la card del criterio
                // Usando clase porque el selector por name con corchetes puede fallar
                const scoreInput = card.querySelector('input.score-input');

                if (!scoreInput) {
                    console.warn('Score input not found for criterion:', criterionId);
                    return;
                }

                // Obtener el valor actualizado del input
                const rawValue = scoreInput.value;
                const score = parseFloat(rawValue) || 0;

                // Actualizar puntaje ponderado individual (siempre, incluso si es 0)
                const weighted = score * (parseFloat(criterion.weight) || 1);
                const weightedSpan = card.querySelector('.weighted-score');
                if (weightedSpan) {
                    weightedSpan.textContent = weighted.toFixed(2);
                }

                // Sumar al total y contar si hay puntaje
                if (score > 0) {
                    count++;
                }
                total += weighted;
            });

            this.totalScore = total;
            this.evaluatedCount = count;
            this.progress = this.criteria.length > 0 ? (count / this.criteria.length) * 100 : 0;
        },

        handleCheckboxChange(event) {
            const checkbox = event.target;
            // Buscar el div contenedor del criterio (no el checkbox)
            const card = checkbox.closest('div.bg-white[data-criterion-id]');
            const criterionId = checkbox.dataset.criterionId;
            const maxScore = parseFloat(checkbox.dataset.maxScore);

            if (!card) {
                console.error('Criterion card not found for checkbox');
                return;
            }

            const checkboxes = card.querySelectorAll('.scale-checkbox:checked');
            let total = 0;
            checkboxes.forEach(cb => {
                total += parseFloat(cb.value);
            });

            if (total > maxScore) {
                checkbox.checked = false;
                alert(`El puntaje máximo para este criterio es ${maxScore} puntos`);
                return;
            }

            // Buscar el input de score dentro de la card del criterio
            const scoreInput = card.querySelector('input.score-input');
            if (!scoreInput) {
                console.error('Score input not found for criterion:', criterionId);
                return;
            }

            // Actualizar el valor del input hidden
            scoreInput.value = total;

            // Actualizar el puntaje calculado mostrado
            const calculatedSpan = card.querySelector('.calculated-score');
            if (calculatedSpan) {
                calculatedSpan.textContent = total.toFixed(2);
            }

            // Recalcular todos los puntajes ponderados y el total
            this.calculateWeightedScores();

            // Guardar automáticamente
            this.autoSave(card, criterionId);
        },

        handleScoreChange(event) {
            const input = event.target;
            const card = input.closest('[data-criterion-id]');
            const criterionId = card.dataset.criterionId;
            const score = parseFloat(input.value);
            const min = parseFloat(input.dataset.min);
            const max = parseFloat(input.dataset.max);

            // Validar rango solo si hay un valor
            if (input.value && (isNaN(score) || score < min || score > max)) {
                input.classList.add('border-red-500');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.classList.remove('hidden');
                }
                return;
            } else {
                input.classList.remove('border-red-500');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.classList.add('hidden');
                }
            }

            this.calculateWeightedScores();
            this.autoSave(card, criterionId);
        },

        autoSave(card, criterionId) {
            clearTimeout(this.saveTimeout);

            this.saveTimeout = setTimeout(() => {
                const scoreInput = card.querySelector('input.score-input');
                const score = scoreInput ? parseFloat(scoreInput.value) || 0 : 0;
                const comments = card.querySelector('textarea[name*="comments"]')?.value || '';
                const evidenceInput = card.querySelector('textarea[name*="evidence"]');
                const evidence = evidenceInput ? evidenceInput.value : null;

                this.saveCriterionDetail(criterionId, score, comments, evidence);
            }, 1000);
        },

        async saveCriterionDetail(criterionId, score, comments, evidence) {
            // No intentar guardar si el token CSRF ya expiró
            if (this.csrfTokenExpired) {
                console.warn('Guardado omitido: CSRF token expirado');
                return;
            }

            // Validar que el criterio tenga los datos requeridos
            const criterion = this.criteria.find(c => c.id == criterionId);
            if (criterion) {
                // Evidencia ahora es opcional
                // if (criterion.requires_evidence && !evidence) {
                //     console.warn('Guardado omitido: El criterio requiere evidencia obligatoria');
                //     return;
                // }
                if (criterion.requires_comment && !comments) {
                    console.warn('Guardado omitido: El criterio requiere comentarios obligatorios');
                    return;
                }
            }

            try {
                // Obtener el token CSRF para esta petición específica
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    console.error('No se pudo obtener el token CSRF');
                    this.csrfTokenExpired = true;
                    alert('Error: No se pudo obtener el token de seguridad. Por favor, recarga la página.');
                    return;
                }

                // Validar que el score esté dentro del rango del criterio
                const minScore = parseFloat(criterion.min_score) || 0;
                const maxScore = parseFloat(criterion.max_score) || 100;

                if (score < minScore || score > maxScore) {
                    console.log('Guardado omitido: Score fuera del rango permitido');
                    return;
                }

                console.log('Guardando criterio:', criterionId, 'Score:', score);

                // Preparar datos para enviar
                const bodyData = {
                    criterion_id: criterionId,
                    score: score,
                    comments: comments || null,
                    evidence: evidence || null
                };

                console.log('Datos a enviar:', bodyData);

                // Usar la ruta de Laravel
                const response = await fetch('{{ route("evaluation.save-detail", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(bodyData)
                });

                if (!response.ok) {
                    if (response.status === 419) {
                        this.csrfTokenExpired = true;
                        console.warn('CSRF token expirado. La sesión ha caducado.');
                        alert('Tu sesión ha expirado. Por favor, recarga la página para continuar.');
                        return;
                    }

                    if (response.status === 422) {
                        const errorData = await response.json();
                        console.error('Error de validación 422:', errorData);
                        console.error('Datos enviados:', {
                            criterion_id: criterionId,
                            score: score,
                            comments: comments ? comments.substring(0, 50) + '...' : '(vacío)',
                            evidence: evidence ? evidence.substring(0, 50) + '...' : '(vacío/null)'
                        });
                        // No mostrar alert para errores de validación en auto-guardado
                        return;
                    }

                    const errorData = await response.json().catch(() => ({}));
                    console.error('Error del servidor:', errorData);
                    return;
                }

                const data = await response.json();
                console.log('✓ Criterio guardado:', criterionId);
                return data;
            } catch (error) {
                console.error('Error guardando criterio:', error);
            }
        },

        async saveDraft() {
            this.isSaving = true;

            try {
                const promises = [];
                document.querySelectorAll('[data-criterion-id]').forEach(card => {
                    const criterionId = card.dataset.criterionId;
                    const scoreInput = card.querySelector('input.score-input');
                    const score = scoreInput ? parseFloat(scoreInput.value) || 0 : 0;

                    // Guardar todos los criterios, incluso los que tienen score 0
                    // La validación de rango se hace en saveCriterionDetail
                    const comments = card.querySelector('textarea[name*="comments"]')?.value || '';
                    const evidenceInput = card.querySelector('textarea[name*="evidence"]');
                    const evidence = evidenceInput ? evidenceInput.value : null;

                    promises.push(this.saveCriterionDetail(criterionId, score, comments, evidence));
                });

                await Promise.all(promises);

                this.$dispatch('notify', {
                    type: 'success',
                    message: 'Evaluación guardada como borrador'
                });
            } catch (error) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Error al guardar: ' + (error.response?.data?.message || 'Error desconocido')
                });
            } finally {
                this.isSaving = false;
            }
        },

        async submitEvaluation() {
            if (!confirm('¿Está seguro de enviar la evaluación? No podrá modificarla después.')) {
                return;
            }

            this.isSubmitting = true;

            try {
                // Obtener el token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    alert('Error: No se pudo obtener el token de seguridad. Por favor, recarga la página.');
                    this.isSubmitting = false;
                    return;
                }

                const generalComments = document.querySelector('textarea[name="general_comments"]').value;

                const response = await fetch('{{ route("evaluation.submit", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        confirm: true,
                        general_comments: generalComments
                    })
                });

                if (!response.ok) {
                    if (response.status === 419) {
                        alert('Tu sesión ha expirado. Por favor, recarga la página para continuar.');
                        this.isSubmitting = false;
                        return;
                    }

                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || errorData.message || 'Error desconocido');
                }

                alert('Evaluación enviada exitosamente');
                window.location.href = '{{ route("evaluation.index") }}';
            } catch (error) {
                alert('Error: ' + error.message);
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
