<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Evaluar Entrevista</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">

<div class="p-6" x-data="interviewEvaluationApp()" x-init="init()">

    <!-- Header Compacto -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 border-b">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Evaluación de Entrevista Personal</h3>
                    <p class="text-sm text-purple-100 mt-1">
                        {{ $application->full_name ?? 'N/A' }} - DNI: {{ $application->dni ?? 'N/A' }}
                    </p>
                </div>
                <div class="text-right">
                    <div x-show="!isDisqualified" class="text-2xl font-bold text-white" x-text="totalScore.toFixed(2)">0.00</div>
                    <div x-show="isDisqualified" class="text-2xl font-bold text-red-200">DESCALIFICADO</div>
                    <p x-show="!isDisqualified" class="text-xs text-purple-100">de {{ number_format($maxTotalScore, 2) }} pts</p>
                </div>
            </div>
        </div>

        <!-- Información del Perfil Compacta -->
        <div class="px-6 py-3 bg-purple-50 border-b">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Puesto:</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $jobProfile->positionCode->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Unidad:</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ $jobProfile->requestingUnit->name ?? 'N/A' }}</span>
                </div>
                @if($application->curriculum_score)
                <div>
                    <span class="text-gray-500">Puntaje CV:</span>
                    <span class="font-semibold text-gray-900 ml-2">{{ number_format($application->curriculum_score, 2) }} pts</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Formulario de Evaluación -->
    <form id="interviewEvaluationForm">

        <!-- Criterios de Evaluación -->
        <div class="bg-white rounded-lg shadow-sm border mb-4">
            <div class="px-6 py-3 bg-gray-50 border-b">
                <h4 class="font-semibold text-gray-900">Criterios de Evaluación</h4>
                <p class="text-xs text-gray-600 mt-1">Escala de 0 a 20 puntos</p>
            </div>

            <div class="p-6 space-y-4">
                @foreach($criteria as $criterion)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-purple-300 transition-colors"
                     x-data="{
                         criterionId: {{ $criterion->id }},
                         score: {{ ($details[$criterion->id]->score ?? 0) > 0 ? number_format(($details[$criterion->id]->score / 12.5) * 20, 1, '.', '') : 0 }},
                         maxScore: 20,
                         maxScoreDB: {{ $criterion->max_score }},
                         comment: '{{ $details[$criterion->id]->comments ?? '' }}'
                     }">

                    <!-- Header del Criterio -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900 mb-1">
                                {{ $loop->iteration }}. {{ $criterion->name }}
                            </h5>
                            <p class="text-xs text-gray-600">{{ $criterion->description }}</p>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-xs text-gray-500">Máx</div>
                            <div class="text-lg font-bold text-purple-600">20.0</div>
                        </div>
                    </div>

                    <!-- Input de Puntaje -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Puntaje Obtenido (0-20) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    :max="maxScore"
                                    x-model.number="score"
                                    @input="updateScore(criterionId, $event.target.value, comment)"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-base font-semibold"
                                    :class="score > maxScore ? 'border-red-500 bg-red-50' : ''"
                                    placeholder="0.0"
                                    required>
                                <span class="text-gray-500 text-sm">/ 20</span>
                            </div>
                        </div>

                        <!-- Barra de Progreso -->
                        <div class="flex items-end">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-600">Porcentaje</span>
                                    <span class="text-sm font-semibold text-purple-600" x-text="((score / maxScore) * 100).toFixed(0) + '%'">0%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600 transition-all duration-300"
                                         :style="`width: ${Math.min((score / maxScore) * 100, 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comentarios -->
                    @if($criterion->requires_comment)
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Comentarios <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            x-model="comment"
                            @input="updateScore(criterionId, score, $event.target.value)"
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Observaciones..."
                            required></textarea>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>

        <!-- Comentarios Generales -->
        <div class="bg-white rounded-lg shadow-sm border mb-4">
            <div class="px-6 py-3 bg-gray-50 border-b">
                <h4 class="font-semibold text-gray-900">Comentarios Generales</h4>
            </div>
            <div class="p-6">
                <textarea
                    x-model="generalComments"
                    rows="3"
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Observaciones generales sobre la entrevista..."></textarea>
            </div>
        </div>

        <!-- Opción de Descalificación -->
        <div class="bg-white rounded-lg shadow-sm border mb-4">
            <div class="px-6 py-3 bg-red-50 border-b">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        x-model="isDisqualified"
                        @change="if(isDisqualified) { clearAllScores(); }"
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="text-sm font-semibold text-red-900">Descalificar postulante</span>
                </label>
            </div>
            <div x-show="isDisqualified" class="p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo de Descalificación <span class="text-red-500">*</span>
                </label>
                <textarea
                    x-model="disqualificationReason"
                    rows="3"
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    placeholder="Describa el motivo de descalificación..."
                    :required="isDisqualified"></textarea>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end items-center gap-3 sticky bottom-0 bg-white border-t border-gray-200 p-4 rounded-lg shadow-lg">
            <button
                type="button"
                @click="saveAsDraft()"
                class="px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium text-sm">
                <i class="fas fa-save mr-2"></i>
                Guardar Borrador
            </button>

            <button
                type="button"
                @click="submitEvaluation()"
                :disabled="!canSubmit()"
                :class="canSubmit() ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed'"
                class="px-5 py-2 text-white rounded-lg font-medium text-sm">
                <i class="fas fa-paper-plane mr-2"></i>
                Enviar Evaluación
            </button>
        </div>

    </form>
</div>

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
                    // Notificar al padre (ventana principal) para que cierre el modal
                    if (window.parent) {
                        window.parent.postMessage('evaluation-submitted', '*');
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

</body>
</html>
