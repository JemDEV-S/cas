@extends('layouts.app')

@section('title', 'Detalle del Conflicto')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('jury-conflicts.index') }}" class="text-decoration-none">Conflictos de Interés</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="h3 mb-1 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Conflicto de Interés #{{ substr($conflict->id, 0, 8) }}
                    </h1>
                    <p class="text-muted mb-0">Gestión y resolución de conflicto de interés</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('jury-conflicts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>

                    <!-- Acciones según estado -->
                    @php
                        $statusValue = $conflict->status->value;
                    @endphp

                    @if($statusValue === 'REPORTED')
                        <button type="button" class="btn btn-warning" onclick="moveToReview()">
                            <i class="fas fa-eye me-2"></i>Mover a Revisión
                        </button>
                    @endif

                    @if(in_array($statusValue, ['REPORTED', 'UNDER_REVIEW']))
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-2"></i>Acciones
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($statusValue === 'UNDER_REVIEW')
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                        <i class="fas fa-check-circle me-2 text-success"></i>Confirmar Conflicto
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#dismissModal">
                                        <i class="fas fa-times-circle me-2 text-secondary"></i>Desestimar Conflicto
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resolveModal">
                                        <i class="fas fa-clipboard-check me-2 text-primary"></i>Resolver Conflicto
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif

                    @if($statusValue === 'CONFIRMED')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
                            <i class="fas fa-clipboard-check me-2"></i>Resolver Ahora
                        </button>
                    @endif
                </div>
            </div>

            <!-- Alerta de prioridad -->
            @if($conflict->requiresImmediateAction())
            <div class="alert alert-danger border-danger d-flex align-items-center mb-4" role="alert">
                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">¡Atención Inmediata Requerida!</h5>
                    <p class="mb-0">Este conflicto es de <strong>{{ $conflict->severity->label() }}</strong> y debe ser atendido con urgencia.</p>
                </div>
            </div>
            @endif

            <div class="row">
                <!-- Columna Principal -->
                <div class="col-lg-8">
                    <!-- Información del Conflicto -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-danger text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Información del Conflicto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Tipo de Conflicto</label>
                                    <p class="mb-0">
                                        <i class="fas fa-{{ $conflict->type_icon }} text-danger me-2"></i>
                                        <strong>{{ $conflict->type_label }}</strong>
                                    </p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Severidad</label>
                                    <p class="mb-0">
                                        @php
                                            $severityColors = [
                                                'LOW' => 'info',
                                                'MEDIUM' => 'warning',
                                                'HIGH' => 'orange',
                                                'CRITICAL' => 'danger'
                                            ];
                                            $severityColor = $severityColors[$conflict->severity->value] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $severityColor }} fs-6">
                                            {{ $conflict->severity->label() }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="text-muted small mb-1">Descripción</label>
                                    <div class="alert alert-light border mb-0">
                                        {{ $conflict->description ?? 'No especificada' }}
                                    </div>
                                </div>

                                @if($conflict->additional_details && is_array($conflict->additional_details) && count($conflict->additional_details) > 0)
                                <div class="col-12 mb-3">
                                    <label class="text-muted small mb-1">Detalles Adicionales</label>
                                    <div class="alert alert-info border mb-0">
                                        <ul class="mb-0">
                                            @foreach($conflict->additional_details as $key => $value)
                                                <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @endif

                                @if($conflict->evidence_path)
                                <div class="col-12">
                                    <label class="text-muted small mb-1">Evidencia</label>
                                    <p class="mb-0">
                                        <a href="{{ $conflict->evidence_path }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-paperclip me-2"></i>Ver Archivo Adjunto
                                        </a>
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información del Jurado -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-primary text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>
                                Jurado Involucrado
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-3">
                                            {{ strtoupper(substr($conflict->juryMember->user->first_name ?? 'J', 0, 1)) }}{{ strtoupper(substr($conflict->juryMember->user->last_name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $conflict->juryMember->user->full_name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $conflict->juryMember->user->email }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <p class="mb-0 small">
                                        <i class="fas fa-tag text-info me-2"></i>
                                        <strong>Especialidad:</strong> {{ $conflict->juryMember->specialty ?? 'No especificada' }}
                                    </p>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <p class="mb-0 small">
                                        <i class="fas fa-certificate text-success me-2"></i>
                                        <strong>Título:</strong> {{ $conflict->juryMember->professional_title ?? 'No especificado' }}
                                    </p>
                                </div>

                                <div class="col-12 mt-2">
                                    <a href="{{ route('jury-members.show', $conflict->jury_member_id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Ver Perfil del Jurado
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Postulante -->
                    @if($conflict->application)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-info text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                Postulante Afectado
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <h6 class="mb-1">{{ $conflict->applicant_name }}</h6>
                                    <p class="text-muted mb-0 small">
                                        Postulación #{{ $conflict->application->application_number ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <p class="mb-0 small">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        {{ $conflict->applicant->email ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <p class="mb-0 small">
                                        <span class="badge bg-{{ $conflict->application->status->color() ?? 'secondary' }}">
                                            {{ $conflict->application->status->label() ?? 'N/A' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-12 mt-3">
                                    <a href="{{ route('application.show', $conflict->application_id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-external-link-alt me-2"></i>Ver Postulación
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Resolución -->
                    @if($conflict->resolution || $conflict->action_taken)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-success text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Resolución del Conflicto
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($conflict->resolution)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Resolución</label>
                                <div class="alert alert-success border mb-0">
                                    {{ $conflict->resolution }}
                                </div>
                            </div>
                            @endif

                            @if($conflict->action_taken)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Acción Tomada</label>
                                <p class="mb-0">
                                    @php
                                        $actionLabels = [
                                            'EXCUSED' => ['label' => 'Jurado Excusado', 'color' => 'warning', 'icon' => 'user-times'],
                                            'REASSIGNED' => ['label' => 'Evaluación Reasignada', 'color' => 'info', 'icon' => 'exchange-alt'],
                                            'APPLICANT_REMOVED' => ['label' => 'Postulante Removido', 'color' => 'danger', 'icon' => 'user-slash'],
                                            'NO_ACTION' => ['label' => 'Sin Acción Requerida', 'color' => 'secondary', 'icon' => 'ban'],
                                            'OTHER' => ['label' => 'Otra Acción', 'color' => 'dark', 'icon' => 'cog']
                                        ];
                                        $action = $actionLabels[$conflict->action_taken] ?? $actionLabels['OTHER'];
                                    @endphp
                                    <span class="badge bg-{{ $action['color'] }} fs-6">
                                        <i class="fas fa-{{ $action['icon'] }} me-1"></i>
                                        {{ $action['label'] }}
                                    </span>
                                </p>
                            </div>
                            @endif

                            @if($conflict->action_notes)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Notas de la Acción</label>
                                <p class="mb-0">{{ $conflict->action_notes }}</p>
                            </div>
                            @endif

                            @if($conflict->resolvedBy)
                            <div class="mb-0">
                                <label class="text-muted small mb-1">Resuelto Por</label>
                                <p class="mb-0">
                                    <i class="fas fa-user me-2 text-success"></i>
                                    {{ $conflict->resolvedBy->full_name }}
                                    <small class="text-muted">({{ $conflict->resolved_at?->format('d/m/Y H:i') }})</small>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Columna Lateral -->
                <div class="col-lg-4">
                    <!-- Estado del Conflicto -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Estado del Conflicto
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                @php
                                    $statusColors = [
                                        'REPORTED' => 'warning',
                                        'UNDER_REVIEW' => 'info',
                                        'CONFIRMED' => 'danger',
                                        'DISMISSED' => 'secondary',
                                        'RESOLVED' => 'success'
                                    ];
                                    $statusColor = $statusColors[$conflict->status->value] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }} fs-5 px-4 py-2">
                                    {{ $conflict->status->label() }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Reportado</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    {{ $conflict->reported_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Días Abierto</label>
                                <p class="mb-0">
                                    <i class="fas fa-clock me-2 text-warning"></i>
                                    {{ $conflict->days_open }} días
                                </p>
                            </div>

                            @if($conflict->is_self_reported)
                            <div class="alert alert-info border-info mb-0">
                                <i class="fas fa-user-check me-2"></i>
                                <strong>Auto-reportado</strong>
                                <p class="mb-0 small">Este conflicto fue reportado por el mismo jurado.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Información de la Convocatoria -->
                    @if($conflict->jobPosting)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-briefcase me-2"></i>
                                Convocatoria
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-2">{{ $conflict->jobPosting->title ?? 'N/A' }}</h6>
                            <p class="text-muted small mb-2">
                                <strong>Código:</strong> {{ $conflict->jobPosting->code ?? 'N/A' }}
                            </p>
                            <p class="mb-3">
                                <span class="badge bg-{{ $conflict->jobPosting->status->color() ?? 'secondary' }}">
                                    {{ $conflict->jobPosting->status->label() ?? 'N/A' }}
                                </span>
                            </p>
                            <a href="{{ route('jobposting.show', $conflict->job_posting_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Ver Convocatoria
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Reportado Por -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-user-edit me-2"></i>
                                Reporte y Revisión
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Reportado Por</label>
                                <p class="mb-0">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    {{ $conflict->reporter_name }}
                                </p>
                            </div>

                            @if($conflict->reviewedBy)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Revisado Por</label>
                                <p class="mb-0">
                                    <i class="fas fa-user-check me-2 text-info"></i>
                                    {{ $conflict->reviewedBy->full_name }}
                                </p>
                                @if($conflict->reviewed_at)
                                <small class="text-muted">{{ $conflict->reviewed_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                            @endif

                            @if($conflict->review_notes)
                            <div class="mb-0">
                                <label class="text-muted small mb-1">Notas de Revisión</label>
                                <div class="alert alert-light border mb-0">
                                    <small>{{ $conflict->review_notes }}</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Historial -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Historial
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @if($conflict->resolved_at)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content ms-3">
                                            <small class="text-muted">{{ $conflict->resolved_at->format('d/m/Y H:i') }}</small>
                                            <p class="mb-0 small"><strong>Conflicto Resuelto</strong></p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($conflict->reviewed_at)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-info"></div>
                                        <div class="timeline-content ms-3">
                                            <small class="text-muted">{{ $conflict->reviewed_at->format('d/m/Y H:i') }}</small>
                                            <p class="mb-0 small"><strong>En Revisión</strong></p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="timeline-item">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content ms-3">
                                            <small class="text-muted">{{ $conflict->reported_at->format('d/m/Y H:i') }}</small>
                                            <p class="mb-0 small"><strong>Conflicto Reportado</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirmar Conflicto -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Confirmar Conflicto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="confirmForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Al confirmar, este conflicto será marcado como válido y requerirá una acción de resolución.
                    </div>

                    <div class="mb-3">
                        <label for="confirm_notes" class="form-label">Notas de Confirmación</label>
                        <textarea name="notes" id="confirm_notes" class="form-control" rows="3"
                                  placeholder="Agregue notas sobre por qué se confirma el conflicto..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Desestimar Conflicto -->
<div class="modal fade" id="dismissModal" tabindex="-1" aria-labelledby="dismissModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="dismissModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Desestimar Conflicto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dismissForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Al desestimar, este conflicto será marcado como no válido y se cerrará sin acción adicional.
                    </div>

                    <div class="mb-3">
                        <label for="dismiss_resolution" class="form-label">Razón del Desestimiento *</label>
                        <textarea name="resolution" id="dismiss_resolution" class="form-control" rows="3" required
                                  placeholder="Explique por qué se desestima este conflicto..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-times-circle me-2"></i>Desestimar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Resolver Conflicto -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="resolveModalLabel">
                    <i class="fas fa-clipboard-check me-2"></i>Resolver Conflicto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resolveForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Complete la información para resolver este conflicto de interés.
                    </div>

                    <div class="mb-3">
                        <label for="action_taken" class="form-label">Acción Tomada *</label>
                        <select name="action_taken" id="action_taken" class="form-select" required>
                            <option value="">Seleccione una acción...</option>
                            <option value="EXCUSED">Excusar al Jurado</option>
                            <option value="REASSIGNED">Reasignar Evaluación</option>
                            <option value="APPLICANT_REMOVED">Remover Postulante</option>
                            <option value="NO_ACTION">Sin Acción Requerida</option>
                            <option value="OTHER">Otra Acción</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="resolution" class="form-label">Descripción de la Resolución *</label>
                        <textarea name="resolution" id="resolution" class="form-control" rows="4" required
                                  placeholder="Describa cómo se resuelve el conflicto..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="action_notes" class="form-label">Notas Adicionales</label>
                        <textarea name="action_notes" id="action_notes" class="form-control" rows="3"
                                  placeholder="Notas adicionales sobre la acción tomada (opcional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-clipboard-check me-2"></i>Resolver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 5px;
}

.timeline-content {
    flex: 1;
}

.bg-orange {
    background-color: #fd7e14 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mover a revisión
    window.moveToReview = function() {
        if (!confirm('¿Está seguro de mover este conflicto a revisión?')) {
            return;
        }

        fetch('{{ route('jury-conflicts.move-to-review', $conflict->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Conflicto movido a revisión exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo mover a revisión'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    };

    // Confirmar conflicto
    document.getElementById('confirmForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route('jury-conflicts.confirm', $conflict->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Conflicto confirmado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo confirmar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    // Desestimar conflicto
    document.getElementById('dismissForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route('jury-conflicts.dismiss', $conflict->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Conflicto desestimado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo desestimar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    // Resolver conflicto
    document.getElementById('resolveForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route('jury-conflicts.resolve', $conflict->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Conflicto resuelto exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo resolver'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });
});
</script>
@endsection
