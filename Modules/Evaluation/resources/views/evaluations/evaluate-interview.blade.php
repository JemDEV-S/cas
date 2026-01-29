@extends('layouts.app')

@section('title', 'Evaluar Entrevista Personal')

@section('page-title')
    Evaluación de Entrevista - {{ $application->full_name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evaluation.my-evaluations') }}">Mis Evaluaciones</a></li>
    <li class="breadcrumb-item active">Evaluar Entrevista</li>
@endsection

@section('content')
<div class="w-full px-4 py-6" x-data="interviewEvaluationApp()" x-init="init()">

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 border-b border-purple-300">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-xl font-semibold">Evaluación de Entrevista Personal</h3>
                    <p class="text-sm text-purple-100 mt-1">
                        {{ $application->full_name ?? 'N/A' }} - DNI: {{ $application->dni ?? 'N/A' }}
                    </p>
                </div>
                <div class="text-right">
                    <div x-show="!isDisqualified" class="text-3xl font-bold text-white" x-text="totalScore.toFixed(2)">0.00</div>
                    <div x-show="isDisqualified" class="text-3xl font-bold text-red-200">DESCALIFICADO</div>
                    <p x-show="!isDisqualified" class="text-xs text-purple-100">de {{ number_format($maxTotalScore, 2) }} pts</p>
                    <div x-show="!isDisqualified" class="mt-3 w-48 bg-purple-900 rounded-full overflow-hidden border-2 border-purple-300 shadow-lg">
                        <div class="h-5 bg-gradient-to-r from-green-400 to-green-300 transition-all duration-300 flex items-center justify-end pr-2" :style="`width: ${Math.max(progress, 2)}%`">
                            <span class="text-xs font-semibold text-purple-900" x-show="progress > 10" x-text="progress.toFixed(0) + '%'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Información del Perfil --}}
        <div class="px-6 py-4 bg-gradient-to-br from-purple-50 to-indigo-50 border-b">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-briefcase text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Puesto</p>
                        <p class="font-semibold text-gray-900">{{ $jobProfile->positionCode->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Unidad Orgánica</p>
                        <p class="font-semibold text-gray-900">{{ $jobProfile->requestingUnit->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-id-card text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Código de Postulación</p>
                        <p class="font-semibold text-gray-900">{{ $application->code ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Información Adicional del Postulante --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Puntaje CV --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Puntaje CV</p>
                        <p class="font-semibold text-gray-900">
                            {{ $application->curriculum_score ? number_format($application->curriculum_score, 2) . ' pts' : 'N/A' }}
                        </p>
                    </div>
                </div>

                {{-- Edad (solo si es menor de 29 años) --}}
                @php
                    $age = $application->birth_date ? \Carbon\Carbon::parse($application->birth_date)->age : null;
                @endphp
                @if($age && $age < 29)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-birthday-cake text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Edad (Bonificación)</p>
                        <p class="font-semibold text-gray-900">{{ $age }} años</p>
                    </div>
                </div>
                @endif

                {{-- Condición Especial --}}
                @if($application->special_condition_bonus && $application->special_condition_bonus > 0)
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 font-semibold">⚠️ Condición Especial</p>
                        <p class="text-xs text-red-700">Bonificación: {{ number_format($application->special_condition_bonus, 2) }}%</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Formulario de Evaluación --}}
    <form id="interviewEvaluationForm">

        {{-- Criterios de Evaluación --}}
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h4 class="text-lg font-semibold text-gray-900">Criterios de Evaluación</h4>
                <p class="text-sm text-gray-600 mt-1">Cada criterio se evalúa en escala de 0 a 20 puntos</p>
            </div>

            <div class="p-6 space-y-6">
                @foreach($criteria as $criterion)
                <div class="border border-gray-200 rounded-lg p-5 hover:border-purple-300 transition-colors"
                     x-data="{
                         criterionId: {{ $criterion->id }},
                         score: {{ ($details[$criterion->id]->score ?? 0) > 0 ? number_format(($details[$criterion->id]->score / 12.5) * 20, 1, '.', '') : 0 }},
                         maxScore: 20,
                         maxScoreDB: {{ $criterion->max_score }},
                         comment: '{{ $details[$criterion->id]->comments ?? '' }}'
                     }">

                    {{-- Header del Criterio --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h5 class="text-base font-semibold text-gray-900 mb-1">
                                {{ $loop->iteration }}. {{ $criterion->name }}
                            </h5>
                            <p class="text-sm text-gray-600">{{ $criterion->description }}</p>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-xs text-gray-500 mb-1">Puntaje máximo</div>
                            <div class="text-2xl font-bold text-purple-600">20.0</div>
                        </div>
                    </div>

                    {{-- Input de Puntaje --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Puntaje Obtenido (escala 0-20)
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    :max="maxScore"
                                    x-model.number="score"
                                    @input="updateScore(criterionId, $event.target.value, comment)"
                                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg font-semibold"
                                    :class="score > maxScore ? 'border-red-500 bg-red-50' : ''"
                                    placeholder="0.0"
                                    required>
                                <span class="text-gray-500 font-medium">/ 20.0</span>
                            </div>
                            <p x-show="score > maxScore" class="text-xs text-red-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                El puntaje no puede exceder 20.0
                            </p>
                        </div>

                        {{-- Barra de Progreso Visual --}}
                        <div class="flex items-end">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-600">Porcentaje</span>
                                    <span class="text-sm font-semibold text-purple-600" x-text="((score / maxScore) * 100).toFixed(0) + '%'">0%</span>
                                </div>
                                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 transition-all duration-300"
                                         :style="`width: ${Math.min((score / maxScore) * 100, 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Comentarios --}}
                    @if($criterion->requires_comment)
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Comentarios y Observaciones
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            x-model="comment"
                            @input="updateScore(criterionId, score, $event.target.value)"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Describa el desempeño del postulante en este criterio..."
                            required></textarea>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>

        {{-- Comentarios Generales --}}
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h4 class="text-lg font-semibold text-gray-900">Comentarios Generales</h4>
            </div>
            <div class="p-6">
                <textarea
                    x-model="generalComments"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Observaciones generales sobre la entrevista, aspectos destacados o áreas de mejora..."></textarea>
            </div>
        </div>

        {{-- Opción de Descalificación --}}
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        x-model="isDisqualified"
                        @change="if(isDisqualified) { clearAllScores(); }"
                        class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <div>
                        <span class="text-base font-semibold text-red-900">Descalificar postulante</span>
                        <p class="text-sm text-red-700">Marque esta opción si el postulante no cumple con los requisitos mínimos</p>
                    </div>
                </label>
            </div>
            <div x-show="isDisqualified" class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo de Descalificación <span class="text-red-500">*</span>
                </label>
                <textarea
                    x-model="disqualificationReason"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Describa claramente el motivo por el cual se descalifica al postulante..."
                    :required="isDisqualified"></textarea>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="flex justify-between items-center gap-4">
            <a href="{{ route('evaluation.index') }}"
               class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>

            <div class="flex gap-3">
                <button
                    type="button"
                    @click="saveAsDraft()"
                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Borrador
                </button>

                <button
                    type="button"
                    @click="submitEvaluation()"
                    :disabled="!canSubmit()"
                    :class="canSubmit() ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="px-6 py-3 text-white rounded-lg font-medium">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Enviar Evaluación
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function interviewEvaluationApp() {
    return {
        evaluationId: {{ $evaluation->id }},
        totalScore: {{ $evaluation->total_score ?? 0 }},
        maxTotalScore: {{ $maxTotalScore }},
        generalComments: '{{ $evaluation->general_comments ?? '' }}',
        isDisqualified: false,
        disqualificationReason: '',
        criteriaScores: {},

        init() {
            // Inicializar TODOS los criterios (incluso si no tienen puntaje aún)
            @foreach($criteria as $criterion)
                this.criteriaScores[{{ $criterion->id }}] = {
                    score: {{ $details[$criterion->id]->score ?? 0 }},
                    comment: '{{ $details[$criterion->id]->comments ?? '' }}'
                };
            @endforeach

            this.calculateTotal();
        },

        get progress() {
            return (this.totalScore / this.maxTotalScore) * 100;
        },

        updateScore(criterionId, score, comment) {
            // Convertir de escala visual (0-20) a escala BD (0-12.5)
            const visualScore = parseFloat(score) || 0;
            const dbScore = (visualScore / 20) * 12.5;

            this.criteriaScores[criterionId] = {
                score: dbScore,
                comment: comment
            };
            this.calculateTotal();
            this.autoSave(criterionId, dbScore, comment);
        },

        calculateTotal() {
            this.totalScore = Object.values(this.criteriaScores).reduce((sum, item) => {
                return sum + (parseFloat(item.score) || 0);
            }, 0);
        },

        clearAllScores() {
            Object.keys(this.criteriaScores).forEach(key => {
                this.criteriaScores[key].score = 0;
            });
            this.totalScore = 0;
        },

        canSubmit() {
            if (this.isDisqualified) {
                return this.disqualificationReason.trim() !== '';
            }
            return this.totalScore > 0;
        },

        autoSave(criterionId, score, comment) {
            fetch('{{ route("evaluation.save-detail", $evaluation->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    criterion_id: criterionId,
                    score: parseFloat(score) || 0,
                    comments: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Error al guardar:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        },

        saveAsDraft() {
            alert('Guardado automático activado. Los cambios se guardan automáticamente.');
        },

        async submitEvaluation() {
            if (!this.canSubmit()) {
                alert('Complete todos los campos requeridos antes de enviar');
                return;
            }

            if (!confirm('¿Está seguro de enviar esta evaluación? Una vez enviada no podrá modificarla.')) {
                return;
            }

            const payload = this.isDisqualified ? {
                confirm: true,
                disqualified: true,
                disqualification_reason: this.disqualificationReason,
                general_comments: this.disqualificationReason
            } : {
                confirm: true,
                general_comments: this.generalComments
            };

            try {
                const response = await fetch('{{ route("evaluation.submit", $evaluation->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                // Verificar el content-type de la respuesta
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no JSON:', text.substring(0, 500));
                    alert('Error: El servidor devolvió una respuesta inválida');
                    return;
                }

                const data = await response.json();

                if (!response.ok) {
                    alert('Error: ' + (data.message || data.error || 'Error desconocido'));
                    return;
                }

                if (data.success) {
                    alert('Evaluación enviada exitosamente');

                    // Intentar obtener la URL de retorno desde sessionStorage
                    const savedState = sessionStorage.getItem('evaluationListState');
                    let returnUrl = null;

                    console.log('Estado guardado en sessionStorage:', savedState);

                    if (savedState) {
                        try {
                            const state = JSON.parse(savedState);
                            console.log('Estado parseado:', state);

                            if (state.url) {
                                returnUrl = state.url;
                                console.log('URL de retorno desde sessionStorage:', returnUrl);
                            }
                        } catch (e) {
                            console.error('Error al parsear estado guardado:', e);
                        }
                    }

                    // Fallback a la URL guardada en la sesión del servidor
                    if (!returnUrl) {
                        const serverReturnUrl = '{{ session("evaluation_return_url") }}';
                        if (serverReturnUrl && serverReturnUrl !== '') {
                            returnUrl = serverReturnUrl;
                            console.log('URL de retorno desde sesión del servidor:', returnUrl);
                        }
                    }

                    // Si hay URL de retorno, ir ahí; sino, ir al index
                    if (returnUrl && returnUrl !== '') {
                        console.log('Redirigiendo a:', returnUrl);
                        window.location.href = returnUrl;
                    } else {
                        console.log('No hay URL guardada, redirigiendo al dashboard');
                        window.location.href = '{{ route("evaluation.index") }}';
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error completo:', error);
                alert('Error al enviar la evaluación: ' + error.message);
            }
        }
    }
}
</script>
@endpush
@endsection
