@extends('layouts.app')

@section('title', 'Detalle de Asignación')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('jury-assignments.index') }}" class="text-decoration-none">Asignaciones de Jurados</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                </ol>
            </nav>

            <!-- Header con acciones -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-primary">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Detalle de Asignación
                    </h1>
                    <p class="text-muted mb-0">Información completa de la asignación del jurado</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('jury-assignments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    @if($assignment->status->value === 'ACTIVE')
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-2"></i>Acciones
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#replaceModal">
                                    <i class="fas fa-exchange-alt me-2 text-warning"></i>Reemplazar Jurado
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#excuseModal">
                                    <i class="fas fa-user-times me-2 text-danger"></i>Excusar Jurado
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="removeAssignment('{{ $assignment->id }}')">
                                    <i class="fas fa-trash me-2"></i>Eliminar Asignación
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Columna Principal -->
                <div class="col-lg-8">
                    <!-- Información del Jurado -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-primary text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>
                                Información del Jurado
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Nombre Completo</label>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-3">
                                            {{ strtoupper(substr($assignment->juryMember->user->first_name ?? 'J', 0, 1)) }}{{ strtoupper(substr($assignment->juryMember->user->last_name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $assignment->juryMember->user->full_name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $assignment->juryMember->user->email }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Especialidad</label>
                                    <p class="mb-0 fw-semibold">
                                        <i class="fas fa-tag text-info me-2"></i>
                                        {{ $assignment->juryMember->specialty ?? 'No especificada' }}
                                    </p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Título Profesional</label>
                                    <p class="mb-0">
                                        <i class="fas fa-certificate text-success me-2"></i>
                                        {{ $assignment->juryMember->professional_title ?? 'No especificado' }}
                                    </p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small mb-1">Años de Experiencia</label>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar-alt text-warning me-2"></i>
                                        {{ $assignment->juryMember->years_of_experience ?? 0 }} años
                                    </p>
                                </div>

                                <div class="col-12">
                                    <a href="{{ route('jury-members.show', $assignment->jury_member_id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Ver Perfil Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Convocatoria -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-success text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-briefcase me-2"></i>
                                Convocatoria Asignada
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="text-muted small mb-1">Título de la Convocatoria</label>
                                    <h6 class="mb-0">{{ $assignment->jobPosting->title ?? 'N/A' }}</h6>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small mb-1">Código</label>
                                    <p class="mb-0">
                                        <span class="badge bg-secondary">{{ $assignment->jobPosting->code ?? 'N/A' }}</span>
                                    </p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small mb-1">Estado</label>
                                    <p class="mb-0">
                                        <span class="badge bg-{{ $assignment->jobPosting->status->color() ?? 'secondary' }}">
                                            {{ $assignment->jobPosting->status->label() ?? 'N/A' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small mb-1">Postulantes</label>
                                    <p class="mb-0 fw-semibold text-primary">
                                        <i class="fas fa-users me-1"></i>
                                        {{ $assignment->jobPosting->applications_count ?? 0 }}
                                    </p>
                                </div>

                                <div class="col-12">
                                    <a href="{{ route('jobposting.show', $assignment->job_posting_id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-external-link-alt me-2"></i>Ver Convocatoria
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carga de Trabajo -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-warning text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-tasks me-2"></i>
                                Carga de Trabajo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3 mb-md-0">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-clipboard-list text-primary fa-2x mb-2"></i>
                                        <p class="text-muted small mb-1">Evaluaciones Máximas</p>
                                        <h3 class="mb-0 text-primary">{{ $assignment->max_evaluations ?? 0 }}</h3>
                                    </div>
                                </div>

                                <div class="col-md-4 text-center mb-3 mb-md-0">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-hourglass-half text-warning fa-2x mb-2"></i>
                                        <p class="text-muted small mb-1">En Progreso</p>
                                        <h3 class="mb-0 text-warning">{{ $assignment->current_evaluations ?? 0 }}</h3>
                                    </div>
                                </div>

                                <div class="col-md-4 text-center">
                                    <div class="p-3 bg-light rounded">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <p class="text-muted small mb-1">Completadas</p>
                                        <h3 class="mb-0 text-success">{{ $assignment->completed_evaluations ?? 0 }}</h3>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <label class="text-muted small mb-2">Progreso de Carga</label>
                                    <div class="progress" style="height: 25px;">
                                        @php
                                            $percentage = $assignment->workload_percentage ?? 0;
                                            $color = $percentage < 50 ? 'success' : ($percentage < 80 ? 'warning' : 'danger');
                                        @endphp
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                    @if($percentage >= 80)
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        El jurado está cerca o ha alcanzado su capacidad máxima
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Cambios -->
                    @if($assignment->status->value !== 'ACTIVE' || $assignment->replaced_by || $assignment->excuse_reason)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-danger text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Historial de Cambios
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @if($assignment->replaced_by)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content ms-3">
                                            <h6 class="mb-1">Jurado Reemplazado</h6>
                                            <p class="mb-1 small">
                                                <strong>Fecha:</strong> {{ $assignment->replacement_date?->format('d/m/Y H:i') ?? 'N/A' }}
                                            </p>
                                            <p class="mb-1 small">
                                                <strong>Razón:</strong> {{ $assignment->replacement_reason ?? 'No especificada' }}
                                            </p>
                                            @if($assignment->replacementApprovedBy)
                                            <p class="mb-0 small text-muted">
                                                Aprobado por: {{ $assignment->replacementApprovedBy->full_name }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($assignment->excuse_reason)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-danger"></div>
                                        <div class="timeline-content ms-3">
                                            <h6 class="mb-1">Jurado Excusado</h6>
                                            <p class="mb-1 small">
                                                <strong>Fecha:</strong> {{ $assignment->excused_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                            </p>
                                            <p class="mb-1 small">
                                                <strong>Razón:</strong> {{ $assignment->excuse_reason }}
                                            </p>
                                            @if($assignment->excusedBy)
                                            <p class="mb-0 small text-muted">
                                                Excusado por: {{ $assignment->excusedBy->full_name }}
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Columna Lateral -->
                <div class="col-lg-4">
                    <!-- Información de la Asignación -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-info text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Detalles de Asignación
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Tipo de Miembro</label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $assignment->member_type->value === 'TITULAR' ? 'primary' : 'secondary' }} fs-6">
                                        <i class="fas fa-{{ $assignment->member_type->value === 'TITULAR' ? 'star' : 'user' }} me-1"></i>
                                        {{ $assignment->member_type->label() }}
                                    </span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Rol en el Jurado</label>
                                <p class="mb-0">
                                    <span class="badge bg-dark fs-6">
                                        <i class="fas fa-user-tag me-1"></i>
                                        {{ $assignment->role_in_jury->label() }}
                                    </span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Estado</label>
                                <p class="mb-0">
                                    @php
                                        $statusColors = [
                                            'ACTIVE' => 'success',
                                            'REPLACED' => 'warning',
                                            'EXCUSED' => 'danger',
                                            'REMOVED' => 'dark',
                                            'SUSPENDED' => 'secondary'
                                        ];
                                        $statusColor = $statusColors[$assignment->status->value] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }} fs-6">
                                        {{ $assignment->status->label() }}
                                    </span>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Orden</label>
                                <p class="mb-0 fw-semibold">#{{ $assignment->order ?? 'N/A' }}</p>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="text-muted small mb-1">Fecha de Asignación</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                </p>
                            </div>

                            @if($assignment->assignment_resolution)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Resolución de Asignación</label>
                                <p class="mb-0">
                                    <i class="fas fa-file-alt me-2 text-success"></i>
                                    {{ $assignment->assignment_resolution }}
                                </p>
                            </div>
                            @endif

                            @if($assignment->resolution_date)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Fecha de Resolución</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>
                                    {{ $assignment->resolution_date?->format('d/m/Y') }}
                                </p>
                            </div>
                            @endif

                            @if($assignment->assignedBy)
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Asignado Por</label>
                                <p class="mb-0">
                                    <i class="fas fa-user me-2 text-info"></i>
                                    {{ $assignment->assignedBy->full_name }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notificaciones y Aceptación -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-secondary text-white py-3">
                            <h5 class="mb-0">
                                <i class="fas fa-bell me-2"></i>
                                Notificación y Aceptación
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Notificado</span>
                                    @if($assignment->notified)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Sí
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>No
                                        </span>
                                    @endif
                                </div>
                                @if($assignment->notified_at)
                                <small class="text-muted">{{ $assignment->notified_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Aceptado</span>
                                    @if($assignment->accepted)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Sí
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </div>
                                @if($assignment->accepted_at)
                                <small class="text-muted">{{ $assignment->accepted_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>

                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Declaración de Conflictos</span>
                                    @if($assignment->has_declared_conflicts)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Declarado
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Pendiente
                                        </span>
                                    @endif
                                </div>
                                @if($assignment->conflict_declared_at)
                                <small class="text-muted">{{ $assignment->conflict_declared_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Disponibilidad -->
                    @if($assignment->available_from || $assignment->available_until)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Periodo de Disponibilidad
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($assignment->available_from)
                            <div class="mb-2">
                                <label class="text-muted small mb-1">Desde</label>
                                <p class="mb-0">{{ $assignment->available_from->format('d/m/Y') }}</p>
                            </div>
                            @endif

                            @if($assignment->available_until)
                            <div class="mb-0">
                                <label class="text-muted small mb-1">Hasta</label>
                                <p class="mb-0">{{ $assignment->available_until->format('d/m/Y') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reemplazar Jurado -->
<div class="modal fade" id="replaceModal" tabindex="-1" aria-labelledby="replaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="replaceModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i>Reemplazar Jurado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="replaceForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta acción marcará la asignación actual como reemplazada y creará una nueva asignación.
                    </div>

                    <div class="mb-3">
                        <label for="new_jury_member_id" class="form-label">Nuevo Jurado *</label>
                        <select name="new_jury_member_id" id="new_jury_member_id" class="form-select" required>
                            <option value="">Seleccione un jurado...</option>
                            <!-- Aquí se cargarían los jurados disponibles mediante AJAX -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="replacement_reason" class="form-label">Razón del Reemplazo *</label>
                        <textarea name="reason" id="replacement_reason" class="form-control" rows="3" required
                                  placeholder="Describa la razón del reemplazo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-exchange-alt me-2"></i>Reemplazar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excusar Jurado -->
<div class="modal fade" id="excuseModal" tabindex="-1" aria-labelledby="excuseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="excuseModalLabel">
                    <i class="fas fa-user-times me-2"></i>Excusar Jurado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="excuseForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta acción marcará al jurado como excusado de esta asignación.
                    </div>

                    <div class="mb-3">
                        <label for="excuse_reason" class="form-label">Razón de la Excusa *</label>
                        <textarea name="reason" id="excuse_reason" class="form-control" rows="3" required
                                  placeholder="Describa la razón de la excusa..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-user-times me-2"></i>Excusar
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

.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de reemplazo
    document.getElementById('replaceForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route('jury-assignments.replace', $assignment->id) }}', {
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
                alert('Jurado reemplazado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo reemplazar el jurado'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });

    // Formulario de excusa
    document.getElementById('excuseForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route('jury-assignments.excuse', $assignment->id) }}', {
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
                alert('Jurado excusado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo excusar al jurado'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    });
});

function removeAssignment(id) {
    if (!confirm('¿Está seguro de eliminar esta asignación? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`/jury-assignments/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Asignación eliminada exitosamente');
            window.location.href = '{{ route('jury-assignments.index') }}';
        } else {
            alert('Error: ' + (data.message || 'No se pudo eliminar la asignación'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}
</script>
@endsection
