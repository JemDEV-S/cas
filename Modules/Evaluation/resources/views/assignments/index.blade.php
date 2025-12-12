@extends('evaluation::layouts.master')

@section('title', 'Asignaciones de Evaluadores')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Asignaciones de Evaluadores</h2>
            <p class="text-muted mb-0">Gestiona las asignaciones de jurados a postulaciones</p>
        </div>
        @can('assign-evaluators')
        <div class="btn-group">
            <a href="{{ route('evaluator-assignments.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Asignar Manual
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#autoAssignModal">
                <i class="fas fa-magic me-2"></i>Asignar Automático
            </button>
        </div>
        @endcan
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Asignaciones</h6>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pendientes</h6>
                            <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completadas</h6>
                            <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Vencidas</h6>
                            <h3 class="mb-0">{{ $stats['overdue'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('evaluator-assignments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Convocatoria</label>
                    <select name="job_posting_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($jobPostings as $posting)
                            <option value="{{ $posting->id }}" {{ request('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                {{ $posting->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fase</label>
                    <select name="phase_id" class="form-select">
                        <option value="">Todas las fases</option>
                        @foreach($phases as $phase)
                            <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                {{ $phase->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                        <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Proceso</option>
                        <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Evaluador</label>
                    <input type="text" name="evaluator" class="form-control" placeholder="Buscar..." value="{{ request('evaluator') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Asignaciones -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Evaluador</th>
                            <th>Convocatoria</th>
                            <th>Fase</th>
                            <th>Postulaciones</th>
                            <th>Estado</th>
                            <th>Fecha Límite</th>
                            <th>Progreso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $assignment->evaluator->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->evaluator->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $assignment->jobPosting->title ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $assignment->jobPosting->code ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-purple">
                                    {{ $assignment->phase->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    {{ $assignment->application_count ?? 1 }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'PENDING' => 'warning',
                                        'IN_PROGRESS' => 'primary',
                                        'COMPLETED' => 'success',
                                        'CANCELLED' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$assignment->status->value] ?? 'secondary' }}">
                                    {{ $assignment->status->value }}
                                </span>
                            </td>
                            <td>
                                @if($assignment->deadline_at)
                                    <span class="{{ $assignment->deadline_at->isPast() ? 'text-danger' : '' }}">
                                        <i class="far fa-calendar me-1"></i>
                                        {{ $assignment->deadline_at->format('d/m/Y') }}
                                    </span>
                                    @if($assignment->deadline_at->isPast() && $assignment->status->value != 'COMPLETED')
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vencida</small>
                                    @endif
                                @else
                                    <span class="text-muted">Sin límite</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $progress = $assignment->progress_percentage ?? 0;
                                @endphp
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%">
                                        {{ $progress }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('evaluator-assignments.show', $assignment->id) }}" 
                                       class="btn btn-outline-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('assign-evaluators')
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $assignment->id }})"
                                            title="Cancelar asignación">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay asignaciones</h5>
                                <p class="text-muted">Las asignaciones de evaluadores aparecerán aquí</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($assignments->hasPages())
        <div class="card-footer">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>

    <!-- Carga de Trabajo por Evaluador -->
    @if($workloadStats ?? false)
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Carga de Trabajo por Evaluador</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Evaluador</th>
                            <th class="text-center">Asignadas</th>
                            <th class="text-center">Pendientes</th>
                            <th class="text-center">Completadas</th>
                            <th>Distribución</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workloadStats as $stat)
                        <tr>
                            <td>{{ $stat->evaluator_name }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $stat->total }}</span></td>
                            <td class="text-center"><span class="badge bg-warning">{{ $stat->pending }}</span></td>
                            <td class="text-center"><span class="badge bg-success">{{ $stat->completed }}</span></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-warning" style="width: {{ ($stat->pending / $stat->total) * 100 }}%">
                                        {{ $stat->pending }}
                                    </div>
                                    <div class="progress-bar bg-success" style="width: {{ ($stat->completed / $stat->total) * 100 }}%">
                                        {{ $stat->completed }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Asignación Automática -->
<div class="modal fade" id="autoAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>Asignación Automática</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('evaluator-assignments.auto-assign') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Convocatoria</label>
                        <select name="job_posting_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($jobPostings as $posting)
                                <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fase</label>
                        <select name="phase_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            @foreach($phases as $phase)
                                <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        El sistema distribuirá automáticamente las postulaciones entre los jurados disponibles de forma equitativa.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic me-2"></i>Asignar Automáticamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}
.bg-purple {
    background-color: #9333ea !important;
}
</style>
@endsection