@extends('layouts.app')

@section('title', 'Revisar CV - ' . ($application->full_name ?? 'Postulante'))

@section('page-title')
    Revisar CV - {{ $application->full_name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.eligibility-override.index', $application->jobProfile->job_posting_id) }}">Reevaluación de Elegibilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.eligibility-override.show', $application->id) }}">{{ $application->code }}</a></li>
    <li class="breadcrumb-item active">Revisar CV</li>
@endsection

@section('content')
<div class="flex h-screen bg-gray-50" x-data="cvReviewApp()" x-init="init()">
    <!-- Panel Izquierdo: CV -->
    <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header del CV -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Curriculum Vitae</h3>
                    <p class="text-sm text-blue-100 mt-1">
                        {{ $application->full_name ?? 'N/A' }}
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
                    src="{{ route('admin.eligibility-override.view-cv', $application->id) }}"
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

    <!-- Panel Derecho: Formulario de Revisión -->
    <div class="w-1/2 flex flex-col bg-gray-50">
        <form id="reviewForm" action="{{ route('admin.eligibility-override.update-cv-review', $application->id) }}" method="POST" @submit.prevent="submitReview">
            @csrf

            <!-- Header del formulario -->
            <div class="px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Revisión de Evaluación de CV</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $jobProfile->jobPosting->title ?? 'Convocatoria' }} - {{ $cvEvaluation->phase->name ?? 'Fase 06' }}
                        </p>
                    </div>

                    <!-- Puntaje total -->
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600" x-text="totalScore.toFixed(2)">{{ number_format($cvEvaluation->total_score, 2) }}</div>
                        <p class="text-xs text-gray-500">de {{ number_format($criteria->sum('max_score'), 2) }} pts</p>
                        <div class="mt-1 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido con scroll -->
            <div class="flex-1 overflow-y-auto px-6 py-6">

                <!-- Información de la postulación -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-md mb-6 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 border-b border-blue-300">
                        <h4 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Información de la Postulación
                        </h4>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Postulación</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->code ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Postulante</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @if($positionCode)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Puesto</p>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800">
                                    {{ $positionCode }}
                                    @if($jobProfile && $jobProfile->positionCode && $jobProfile->positionCode->name)
                                    <span class="ml-2 text-blue-600">- {{ $jobProfile->positionCode->name }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Evaluador Original</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $cvEvaluation->evaluator->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Criterios de evaluación -->
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg px-5 py-3 shadow-md">
                        <h4 class="text-base font-bold text-white">Criterios de Evaluación</h4>
                        <p class="text-sm text-blue-100 mt-1">Modifica los puntajes según corresponda</p>
                    </div>

                    @foreach($criteria as $criterion)
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm" data-criterion-id="{{ $criterion->id }}">
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
                                                   @change="handleCheckboxChange($event)"
                                                   @checked(isset($details[$criterion->id]) && in_array($scale['puntos'], explode(',', $details[$criterion->id]->metadata['selected_options'] ?? '')))>
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

                            <!-- Criterios sin escalas (UN SOLO CHECKBOX CON VALOR FIJO = MAX_SCORE) -->
                            @else
                                <div>
                                    <label class="flex items-start gap-3 p-4 bg-gray-50 hover:bg-gray-100 border-2 border-gray-200 rounded-lg cursor-pointer transition-colors">
                                        <input type="checkbox"
                                               class="scale-checkbox mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                               name="criteria[{{ $criterion->id }}][options][]"
                                               value="{{ $criterion->max_score }}"
                                               data-criterion-id="{{ $criterion->id }}"
                                               data-max-score="{{ $criterion->max_score }}"
                                               @change="handleCheckboxChange($event)"
                                               @checked(isset($details[$criterion->id]) && $details[$criterion->id]->score == $criterion->max_score)>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-semibold text-gray-900">Cumple este criterio</span>
                                                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-bold rounded">
                                                    {{ number_format($criterion->max_score, 2) }} pts
                                                </span>
                                            </div>
                                        </div>
                                    </label>

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

                <!-- Comentarios generales -->
                <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h4 class="text-base font-semibold text-gray-900">Comentarios Generales</h4>
                    </div>
                    <div class="px-5 py-4">
                        <textarea
                            name="general_comments"
                            rows="3"
                            x-model="generalComments"
                            placeholder="Observaciones adicionales sobre la evaluación..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ $cvEvaluation->general_comments ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Decisión del reclamo -->
                <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 bg-amber-50">
                        <h4 class="text-base font-semibold text-gray-900">Decisión del Reclamo</h4>
                    </div>
                    <div class="px-5 py-4 space-y-4">
                        <!-- Decision -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Decisión *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="decision" value="APPROVED" x-model="decision" required class="w-4 h-4 text-green-600 focus:ring-green-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Aprobar (cambiar a APTO)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="decision" value="REJECTED" x-model="decision" required class="w-4 h-4 text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Rechazar (mantener NO APTO)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Resolution Summary -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Resumen de Resolución * (máx 500 caracteres)</label>
                            <input
                                type="text"
                                name="resolution_summary"
                                x-model="resolutionSummary"
                                maxlength="500"
                                required
                                placeholder="Breve resumen de la decisión tomada"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Resolution Detail -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Detalle de Resolución *</label>
                            <textarea
                                name="resolution_detail"
                                x-model="resolutionDetail"
                                rows="4"
                                required
                                placeholder="Fundamento detallado de la decisión, incluyendo análisis de la documentación y criterios evaluados..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer con botones de acción -->
            <div class="px-6 py-4 bg-white border-t border-gray-200 shadow-lg">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('admin.eligibility-override.show', $application->id) }}"
                       class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                        Cancelar
                    </a>

                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                        <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Guardando...' : 'Guardar y Resolver Reclamo'">Guardar y Resolver Reclamo</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function cvReviewApp() {
    return {
        criteria: @json($criteria),
        totalScore: {{ $cvEvaluation->total_score }},
        progress: 0,
        generalComments: @json($cvEvaluation->general_comments ?? ''),
        decision: '',
        resolutionSummary: '',
        resolutionDetail: '',
        submitting: false,

        init() {
            this.initializeCheckboxes();
            this.calculateWeightedScores();
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

                    if (savedScore > 0) {
                        const checkboxes = card.querySelectorAll('.scale-checkbox');
                        if (checkboxes.length > 0) {
                            // Para criterios sin escala (un solo checkbox), marcar si el score coincide con el max
                            if (checkboxes.length === 1) {
                                const maxScore = parseFloat(checkboxes[0].dataset.maxScore);
                                if (savedScore === maxScore) {
                                    checkboxes[0].checked = true;
                                }
                            } else {
                                // Para criterios con múltiples checkboxes (escalas)
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
                }
            });
        },

        calculateWeightedScores() {
            let total = 0;

            document.querySelectorAll('[data-criterion-id]').forEach(card => {
                const criterionId = card.dataset.criterionId;
                const criterion = this.criteria.find(c => c.id == criterionId);

                if (!criterion) {
                    console.warn('Criterion not found:', criterionId);
                    return;
                }

                const scoreInput = card.querySelector('input.score-input');

                if (!scoreInput) {
                    console.warn('Score input not found for criterion:', criterionId);
                    return;
                }

                const rawValue = scoreInput.value;
                const score = parseFloat(rawValue) || 0;

                const weighted = score * (parseFloat(criterion.weight) || 1);
                const weightedSpan = card.querySelector('.weighted-score');
                if (weightedSpan) {
                    weightedSpan.textContent = weighted.toFixed(2);
                }

                total += weighted;
            });

            this.totalScore = total;

            // Calcular progreso sobre la suma de max_score (no ponderado)
            const maxScore = {{ $criteria->sum('max_score') }};
            const currentScore = this.totalScore;
            const maxTotalScore = {{ floatval($criteria->sum(fn($c) => $c->max_score * $c->weight)) }};
            this.progress = maxTotalScore > 0 ? (currentScore / maxTotalScore) * 100 : 0;
        },

        handleCheckboxChange(event) {
            const checkbox = event.target;
            const card = checkbox.closest('div.bg-white[data-criterion-id]');
            const criterionId = checkbox.dataset.criterionId;
            const maxScore = parseFloat(checkbox.dataset.maxScore);

            if (!card) {
                console.error('Criterion card not found for checkbox');
                return;
            }

            const checkboxes = card.querySelectorAll('.scale-checkbox');

            // Si es un solo checkbox (criterio sin escala)
            if (checkboxes.length === 1) {
                const scoreInput = card.querySelector('input.score-input');
                if (!scoreInput) {
                    console.error('Score input not found for criterion:', criterionId);
                    return;
                }

                // Si está marcado, asignar max_score; si no, asignar 0
                const newScore = checkbox.checked ? maxScore : 0;
                scoreInput.value = newScore;

                const calculatedSpan = card.querySelector('.calculated-score');
                if (calculatedSpan) {
                    calculatedSpan.textContent = newScore.toFixed(2);
                }

                this.calculateWeightedScores();
                return;
            }

            // Para múltiples checkboxes (criterios con escala)
            const checkedBoxes = card.querySelectorAll('.scale-checkbox:checked');
            let total = 0;
            checkedBoxes.forEach(cb => {
                total += parseFloat(cb.value);
            });

            if (total > maxScore) {
                checkbox.checked = false;
                alert(`El puntaje máximo para este criterio es ${maxScore} puntos`);
                return;
            }

            const scoreInput = card.querySelector('input.score-input');
            if (!scoreInput) {
                console.error('Score input not found for criterion:', criterionId);
                return;
            }

            scoreInput.value = total;

            const calculatedSpan = card.querySelector('.calculated-score');
            if (calculatedSpan) {
                calculatedSpan.textContent = total.toFixed(2);
            }

            this.calculateWeightedScores();
        },

        submitReview(e) {
            if (this.submitting) return;

            if (!this.decision) {
                alert('Debe seleccionar una decisión (Aprobar o Rechazar)');
                return;
            }

            if (!this.resolutionSummary.trim()) {
                alert('Debe ingresar un resumen de resolución');
                return;
            }

            if (!this.resolutionDetail.trim()) {
                alert('Debe ingresar el detalle de la resolución');
                return;
            }

            if (confirm('¿Está seguro de guardar los cambios y resolver el reclamo? Esta acción no se puede deshacer.')) {
                this.submitting = true;
                e.target.submit();
            }
        }
    }
}
</script>
@endpush
@endsection
