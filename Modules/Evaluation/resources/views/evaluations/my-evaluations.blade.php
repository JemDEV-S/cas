@extends('evaluation::layouts.master')

@section('title', 'Mis Evaluaciones')

@section('page-title', 'Mis Evaluaciones')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Mis Evaluaciones</li>
@endsection

@section('content')
<div class="row mb-4">
    <!-- Estadísticas Rápidas -->
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Total Asignadas</div>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Pendientes</div>
                    <h3 class="mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon" style="background: #fffbeb; color: #f59e0b;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Completadas</div>
                    <h3 class="mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon" style="background: #f0fdf4; color: #10b981;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">Vencidas</div>
                    <h3 class="mb-0 text-danger">{{ $stats['overdue'] ?? 0 }}</h3>
                </div>
                <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('evaluation.my-evaluations') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="ASSIGNED" {{ request('status') == 'ASSIGNED' ? 'selected' : '' }}>Asignada</option>
                    <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>En Progreso</option>
                    <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>Enviada</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Fase</label>
                <select name="phase_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($phases ?? [] as $phase)
                        <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                            {{ $phase->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="pending_only" value="1" 
                           id="pendingOnly" {{ request('pending_only') ? 'checked' : '' }}>
                    <label class="form-check-label" for="pendingOnly">
                        Solo pendientes
                    </label>
                </div>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Evaluaciones -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Evaluaciones Asignadas</h5>
    </div>
    <div class="card-body p-0">
        @if($evaluations->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No tienes evaluaciones asignadas</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Convocatoria</th>
                            <th>Postulante</th>
                            <th>Fase</th>
                            <th>Estado</th>
                            <th>Puntaje</th>
                            <th>Fecha Límite</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluations as $evaluation)
                        <tr>
                            <td>
                                <strong>{{ $evaluation->jobPosting->title ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $evaluation->jobPosting->code ?? '' }}</small>
                            </td>
                            <td>
                                {{ $evaluation->application->user->name ?? 'Anónimo' }}
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $evaluation->phase->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($evaluation->status->value) {
                                        'ASSIGNED' => 'secondary',
                                        'IN_PROGRESS' => 'warning',
                                        'SUBMITTED' => 'success',
                                        'MODIFIED' => 'primary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $evaluation->status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($evaluation->isCompleted())
                                    <strong>{{ number_format($evaluation->total_score, 2) }}</strong>
                                    <small class="text-muted">
                                        ({{ number_format($evaluation->percentage, 1) }}%)
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($evaluation->deadline_at)
                                    <span class="{{ $evaluation->isOverdue() ? 'text-danger' : '' }}">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $evaluation->deadline_at->format('d/m/Y H:i') }}
                                    </span>
                                    @if($evaluation->isOverdue())
                                        <br><small class="text-danger"><strong>¡Vencida!</strong></small>
                                    @endif
                                @else
                                    <span class="text-muted">Sin límite</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('evaluation.show', $evaluation->id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($evaluation->canEdit())
                                        <a href="{{ route('evaluation.evaluate', $evaluation->id) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Evaluar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="card-footer">
                {{ $evaluations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh cada 5 minutos
    setTimeout(() => {
        location.reload();
    }, 300000);
</script>
@endpush