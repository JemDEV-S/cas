@extends('evaluation::layouts.master')

@section('title', 'Criterios de Evaluación')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Criterios de Evaluación</h2>
            <p class="text-muted mb-0">Gestiona los criterios para cada fase de evaluación</p>
        </div>
        @can('manage-criteria')
        <a href="{{ route('evaluation-criteria.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Criterio
        </a>
        @endcan
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('evaluation-criteria.index') }}" class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="active_only" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('active_only') == '1' ? 'selected' : '' }}>Solo activos</option>
                        <option value="0" {{ request('active_only') == '0' ? 'selected' : '' }}>Solo inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="system_only" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('system_only') == '1' ? 'selected' : '' }}>Solo sistema</option>
                        <option value="0" {{ request('system_only') == '0' ? 'selected' : '' }}>Solo personalizados</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Agrupado por Fase -->
    @forelse($criteriaByPhase as $phaseName => $criteria)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-check me-2"></i>{{ $phaseName }}
                <span class="badge bg-white text-primary ms-2">{{ $criteria->count() }} criterios</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">Orden</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th style="width: 100px" class="text-center">Puntaje</th>
                            <th style="width: 80px" class="text-center">Peso</th>
                            <th style="width: 100px" class="text-center">Tipo</th>
                            <th style="width: 100px" class="text-center">Estado</th>
                            <th style="width: 120px" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criteria as $criterion)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $criterion->order }}</span>
                            </td>
                            <td>
                                <code class="text-primary">{{ $criterion->code }}</code>
                                @if($criterion->is_system)
                                    <span class="badge bg-info ms-1" title="Criterio del sistema">
                                        <i class="fas fa-shield-alt"></i>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $criterion->name }}</strong>
                                    @if($criterion->requires_comment)
                                        <i class="fas fa-comment text-warning ms-1" title="Requiere comentario"></i>
                                    @endif
                                    @if($criterion->requires_evidence)
                                        <i class="fas fa-paperclip text-info ms-1" title="Requiere evidencia"></i>
                                    @endif
                                </div>
                                @if($criterion->description)
                                    <small class="text-muted">{{ Str::limit($criterion->description, 60) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    {{ $criterion->min_score }} - {{ $criterion->max_score }} pts
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $criterion->weight }}x</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $typeColors = [
                                        'NUMERIC' => 'success',
                                        'PERCENTAGE' => 'info',
                                        'QUALITATIVE' => 'warning'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$criterion->score_type->value] ?? 'secondary' }}">
                                    {{ $criterion->score_type->value }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($criterion->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('evaluation-criteria.show', $criterion->id) }}" 
                                       class="btn btn-outline-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('manage-criteria')
                                        @if(!$criterion->is_system)
                                        <a href="{{ route('evaluation-criteria.edit', $criterion->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">No hay criterios de evaluación</h4>
        <p class="text-muted">Los criterios configurados aparecerán aquí</p>
        @can('manage-criteria')
        <a href="{{ route('evaluation-criteria.create') }}" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear Primer Criterio
        </a>
        @endcan
    </div>
    @endforelse

    <!-- Totales por fase -->
    @if($criteriaByPhase->isNotEmpty())
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Puntajes</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($criteriaByPhase as $phaseName => $criteria)
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3">
                        <h6 class="text-primary mb-2">{{ $phaseName }}</h6>
                        <div class="d-flex justify-content-between">
                            <span>Total criterios:</span>
                            <strong>{{ $criteria->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Puntaje máximo:</span>
                            <strong>{{ $criteria->sum('max_score') }} pts</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Puntaje ponderado:</span>
                            <strong class="text-success">
                                {{ $criteria->sum(fn($c) => $c->max_score * $c->weight) }} pts
                            </strong>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endsection