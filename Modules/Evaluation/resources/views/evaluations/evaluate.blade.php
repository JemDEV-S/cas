@extends('layouts.app')

@section('title', 'Evaluar Postulante')

@section('page-title')
    Evaluación - {{ $evaluation->application->user->name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('evaluation.my-evaluations') }}">Mis Evaluaciones</a></li>
    <li class="breadcrumb-item active">Evaluar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Información del Postulante -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Información del Postulante
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> {{ $evaluation->application->user->name ?? 'N/A' }}</p>
                        <p><strong>Convocatoria:</strong> {{ $evaluation->jobPosting->title }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Código:</strong> {{ $evaluation->application->code ?? 'N/A' }}</p>
                        <p><strong>Fase:</strong> {{ $evaluation->phase->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Evaluación -->
        <form id="evaluationForm">
            @foreach($criteria as $criterion)
            <div class="card mb-3 criterion-card" data-criterion-id="{{ $criterion->id }}">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            {{ $criterion->order }}. {{ $criterion->name }}
                        </h6>
                        <span class="badge bg-secondary">
                            Peso: {{ $criterion->weight }}x
                        </span>
                    </div>
                    @if($criterion->description)
                        <small class="text-muted">{{ $criterion->description }}</small>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Rango de puntaje -->
                    <div class="mb-3">
                        <label class="form-label">
                            Puntaje
                            <span class="text-danger">*</span>
                            <small class="text-muted">(Rango: {{ $criterion->min_score }} - {{ $criterion->max_score }})</small>
                        </label>
                        <input type="number"
                               class="form-control score-input"
                               name="criteria[{{ $criterion->id }}][score]"
                               data-min="{{ $criterion->min_score }}"
                               data-max="{{ $criterion->max_score }}"
                               min="{{ $criterion->min_score }}"
                               max="{{ $criterion->max_score }}"
                               step="0.5"
                               value="{{ $details[$criterion->id]->score ?? '' }}"
                               {{ $criterion->requires_comment ? 'required' : '' }}>
                        <div class="invalid-feedback">
                            El puntaje debe estar entre {{ $criterion->min_score }} y {{ $criterion->max_score }}
                        </div>
                    </div>

                    <!-- Guía de evaluación -->
                    @if($criterion->evaluation_guide)
                    <div class="alert alert-info small mb-3">
                        <strong>Guía de evaluación:</strong>
                        <pre class="mb-0 mt-2" style="white-space: pre-line; font-size: 0.85em;">{{ $criterion->evaluation_guide }}</pre>
                    </div>
                    @endif

                    <!-- Comentarios -->
                    <div class="mb-3">
                        <label class="form-label">
                            Comentarios
                            @if($criterion->requires_comment)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <textarea class="form-control"
                                  name="criteria[{{ $criterion->id }}][comments]"
                                  rows="3"
                                  {{ $criterion->requires_comment ? 'required' : '' }}>{{ $details[$criterion->id]->comments ?? '' }}</textarea>
                    </div>

                    <!-- Evidencia -->
                    @if($criterion->requires_evidence)
                    <div class="mb-3">
                        <label class="form-label">
                            Evidencia <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  name="criteria[{{ $criterion->id }}][evidence]"
                                  rows="2"
                                  required>{{ $details[$criterion->id]->evidence ?? '' }}</textarea>
                    </div>
                    @endif

                    <!-- Puntaje ponderado (calculado) -->
                    <div class="text-end">
                        <small class="text-muted">
                            Puntaje ponderado: <strong class="weighted-score">0.00</strong>
                        </small>
                    </div>
                </div>
            </div>
            @endforeach

            <!-- Comentarios Generales -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Comentarios Generales</h6>
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="general_comments" rows="4"
                              placeholder="Observaciones generales sobre la evaluación...">{{ $evaluation->general_comments }}</textarea>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="button" class="btn btn-secondary" id="saveBtn">
                                <i class="fas fa-save me-2"></i>Guardar Borrador
                            </button>
                        </div>
                        <div>
                            <a href="{{ route('evaluation.my-evaluations') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="button" class="btn btn-success" id="submitBtn"
                                    {{ !$evaluation->canEdit() ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane me-2"></i>Enviar Evaluación
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Sidebar - Resumen -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-light">
                <h6 class="mb-0">Resumen de Evaluación</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Criterios evaluados:</span>
                        <strong id="criteriaCount">0 / {{ $criteria->count() }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" id="progressBar" style="width: 0%"></div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Puntaje Total:</span>
                        <h4 class="mb-0" id="totalScore">0.00</h4>
                    </div>
                    <small class="text-muted">
                        Máximo posible: {{ number_format($maxScore, 2) }}
                    </small>
                </div>

                <div class="alert alert-warning small">
                    <i class="fas fa-info-circle me-2"></i>
                    Los puntajes se guardan automáticamente mientras trabajas.
                </div>

                @if($evaluation->deadline_at)
                <div class="alert {{ $evaluation->isOverdue() ? 'alert-danger' : 'alert-info' }} small">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Fecha límite:</strong><br>
                    {{ $evaluation->deadline_at->format('d/m/Y H:i') }}
                    @if($evaluation->isOverdue())
                        <br><span class="text-danger"><strong>¡VENCIDA!</strong></span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const evaluationId = {{ $evaluation->id }};
const criteria = @json($criteria);
let saveTimeout;

// Calcular puntajes ponderados
function calculateWeightedScores() {
    let total = 0;
    let count = 0;

    document.querySelectorAll('.criterion-card').forEach(card => {
        const criterionId = card.dataset.criterionId;
        const criterion = criteria.find(c => c.id == criterionId);
        const scoreInput = card.querySelector('.score-input');
        const score = parseFloat(scoreInput.value) || 0;

        if (score > 0) {
            count++;
            const weighted = score * criterion.weight;
            card.querySelector('.weighted-score').textContent = weighted.toFixed(2);
            total += weighted;
        }
    });

    // Actualizar resumen
    document.getElementById('totalScore').textContent = total.toFixed(2);
    document.getElementById('criteriaCount').textContent = `${count} / ${criteria.length}`;

    const progress = (count / criteria.length) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
}

// Guardar detalle de criterio
async function saveCriterionDetail(criterionId, score, comments, evidence) {
    try {
        const response = await axios.post(`/api/evaluation/evaluations/${evaluationId}/details`, {
            criterion_id: criterionId,
            score: score,
            comments: comments,
            evidence: evidence
        });

        return response.data;
    } catch (error) {
        console.error('Error guardando criterio:', error);
        throw error;
    }
}

// Auto-guardar al cambiar puntaje
document.querySelectorAll('.score-input').forEach(input => {
    input.addEventListener('change', function() {
        clearTimeout(saveTimeout);
        calculateWeightedScores();

        const card = this.closest('.criterion-card');
        const criterionId = card.dataset.criterionId;
        const score = parseFloat(this.value);
        const comments = card.querySelector('textarea[name*="comments"]').value;
        const evidenceInput = card.querySelector('textarea[name*="evidence"]');
        const evidence = evidenceInput ? evidenceInput.value : null;

        // Validar rango
        const min = parseFloat(this.dataset.min);
        const max = parseFloat(this.dataset.max);

        if (score < min || score > max) {
            this.classList.add('is-invalid');
            return;
        } else {
            this.classList.remove('is-invalid');
        }

        // Guardar después de 1 segundo
        saveTimeout = setTimeout(() => {
            saveCriterionDetail(criterionId, score, comments, evidence)
                .then(() => {
                    // Mostrar indicador de guardado
                    const indicator = document.createElement('small');
                    indicator.className = 'text-success ms-2';
                    indicator.innerHTML = '<i class="fas fa-check"></i> Guardado';
                    this.parentElement.appendChild(indicator);
                    setTimeout(() => indicator.remove(), 2000);
                })
                .catch(error => {
                    alert('Error al guardar: ' + (error.response?.data?.message || 'Error desconocido'));
                });
        }, 1000);
    });
});

// Guardar borrador
document.getElementById('saveBtn').addEventListener('click', async function() {
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

    try {
        // Guardar todos los criterios
        const promises = [];
        document.querySelectorAll('.criterion-card').forEach(card => {
            const criterionId = card.dataset.criterionId;
            const scoreInput = card.querySelector('.score-input');
            const score = parseFloat(scoreInput.value);

            if (score && score > 0) {
                const comments = card.querySelector('textarea[name*="comments"]').value;
                const evidenceInput = card.querySelector('textarea[name*="evidence"]');
                const evidence = evidenceInput ? evidenceInput.value : null;

                promises.push(saveCriterionDetail(criterionId, score, comments, evidence));
            }
        });

        await Promise.all(promises);

        alert('Evaluación guardada como borrador');
        this.innerHTML = '<i class="fas fa-check me-2"></i>Guardado';
    } catch (error) {
        alert('Error al guardar: ' + (error.response?.data?.message || 'Error desconocido'));
        this.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Borrador';
    } finally {
        this.disabled = false;
    }
});

// Enviar evaluación
document.getElementById('submitBtn').addEventListener('click', async function() {
    if (!confirm('¿Está seguro de enviar la evaluación? No podrá modificarla después.')) {
        return;
    }

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

    try {
        const generalComments = document.querySelector('textarea[name="general_comments"]').value;

        const response = await axios.post(`/api/evaluation/evaluations/${evaluationId}/submit`, {
            confirm: true,
            general_comments: generalComments
        });

        alert('Evaluación enviada exitosamente');
        window.location.href = '{{ route("evaluation.my-evaluations") }}';
    } catch (error) {
        alert('Error: ' + (error.response?.data?.error || 'Error desconocido'));
        this.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Enviar Evaluación';
        this.disabled = false;
    }
});

// Calcular al cargar
calculateWeightedScores();
</script>
@endpush
