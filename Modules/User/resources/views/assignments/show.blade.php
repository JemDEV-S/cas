@extends('layouts.app')

@section('title', 'Detalle de Asignación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            {{-- Alertas --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-2">Detalle de Asignación</h3>
                            <p class="text-muted mb-0">
                                Creada: {{ $assignment->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="text-end">
                            @if($assignment->is_active && $assignment->isCurrent())
                                <span class="badge bg-success fs-6 mb-2">
                                    <i class="bi bi-check-circle"></i> Activa
                                </span>
                            @elseif($assignment->is_active)
                                <span class="badge bg-warning fs-6 mb-2">
                                    <i class="bi bi-clock"></i> Programada
                                </span>
                            @else
                                <span class="badge bg-danger fs-6 mb-2">
                                    <i class="bi bi-x-circle"></i> Inactiva
                                </span>
                            @endif
                            <br>
                            @if($assignment->is_primary)
                                <span class="badge bg-primary fs-6">
                                    <i class="bi bi-star-fill"></i> Principal
                                </span>
                            @else
                                <span class="badge bg-secondary fs-6">Secundaria</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Información del Usuario --}}
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person"></i> Usuario Asignado
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                @if($assignment->user->photo_url)
                                    <img src="{{ $assignment->user->photo_url }}" 
                                         class="rounded-circle mb-3" 
                                         width="120" height="120">
                                @else
                                    <div class="avatar-placeholder-lg mb-3">
                                        {{ substr($assignment->user->first_name, 0, 1) }}
                                    </div>
                                @endif
                                <h4 class="mb-1">{{ $assignment->user->full_name }}</h4>
                                <p class="text-muted mb-0">{{ $assignment->user->email }}</p>
                            </div>

                            <hr>

                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">DNI</small>
                                    <strong>{{ $assignment->user->dni }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Teléfono</small>
                                    <strong>{{ $assignment->user->phone ?? 'No registrado' }}</strong>
                                </div>
                                @if($assignment->user->profile)
                                    <div class="col-12">
                                        <small class="text-muted d-block">Fecha de Nacimiento</small>
                                        <strong>
                                            {{ $assignment->user->profile->birth_date 
                                                ? $assignment->user->profile->birth_date->format('d/m/Y') 
                                                : 'No registrado' }}
                                        </strong>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('users.show', $assignment->user) }}" 
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> Ver Perfil Completo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Información de la Unidad Organizacional --}}
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-building"></i> Unidad Organizacional
                            </h5>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-3">{{ $assignment->organizationUnit->name }}</h4>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Código</small>
                                    <strong>{{ $assignment->organizationUnit->code }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Tipo</small>
                                    <strong>{{ $assignment->organizationUnit->type }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Nivel</small>
                                    <strong>Nivel {{ $assignment->organizationUnit->level }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Estado</small>
                                    @if($assignment->organizationUnit->is_active)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-danger">Inactiva</span>
                                    @endif
                                </div>
                            </div>

                            @if($assignment->organizationUnit->description)
                                <div class="mb-3">
                                    <small class="text-muted d-block">Descripción</small>
                                    <p class="mb-0">{{ $assignment->organizationUnit->description }}</p>
                                </div>
                            @endif

                            <div class="mt-3">
                                <a href="{{ route('organizational-units.show', $assignment->organizationUnit) }}" 
                                   class="btn btn-sm btn-outline-info w-100">
                                    <i class="bi bi-eye"></i> Ver Unidad Completa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información de la Asignación --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event"></i> Detalles de la Asignación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="border-start border-primary border-4 ps-3">
                                <small class="text-muted d-block">Fecha de Inicio</small>
                                <h5 class="mb-0">{{ $assignment->start_date->format('d/m/Y') }}</h5>
                                <small class="text-muted">
                                    {{ $assignment->start_date->diffForHumans() }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border-start border-danger border-4 ps-3">
                                <small class="text-muted d-block">Fecha de Fin</small>
                                @if($assignment->end_date)
                                    <h5 class="mb-0">{{ $assignment->end_date->format('d/m/Y') }}</h5>
                                    <small class="text-muted">
                                        {{ $assignment->end_date->diffForHumans() }}
                                    </small>
                                @else
                                    <h5 class="mb-0">Indefinido</h5>
                                    <small class="text-muted">Sin fecha de fin</small>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border-start border-success border-4 ps-3">
                                <small class="text-muted d-block">Duración</small>
                                @if($assignment->end_date)
                                    <h5 class="mb-0">
                                        {{ $assignment->start_date->diffInDays($assignment->end_date) }} días
                                    </h5>
                                @else
                                    <h5 class="mb-0">En curso</h5>
                                @endif
                                <small class="text-muted">
                                    Días transcurridos: {{ $assignment->start_date->diffInDays(now()) }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border-start border-warning border-4 ps-3">
                                <small class="text-muted d-block">Tipo de Asignación</small>
                                <h5 class="mb-0">
                                    @if($assignment->is_primary)
                                        <i class="bi bi-star-fill text-warning"></i> Principal
                                    @else
                                        Secundaria
                                    @endif
                                </h5>
                                <small class="text-muted">
                                    {{ $assignment->is_active ? 'Activa' : 'Inactiva' }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline de la asignación --}}
            @if($assignment->created_at || $assignment->updated_at)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Historial
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Asignación Creada</h6>
                                    <p class="text-muted mb-0">
                                        {{ $assignment->created_at->format('d/m/Y H:i') }}
                                        <small>({{ $assignment->created_at->diffForHumans() }})</small>
                                    </p>
                                </div>
                            </div>
                            
                            @if($assignment->updated_at && !$assignment->updated_at->eq($assignment->created_at))
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Última Actualización</h6>
                                        <p class="text-muted mb-0">
                                            {{ $assignment->updated_at->format('d/m/Y H:i') }}
                                            <small>({{ $assignment->updated_at->diffForHumans() }})</small>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Acciones --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>

                        @can('user.update.assignment')
                            <a href="{{ route('assignments.edit', $assignment) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Editar Asignación
                            </a>
                        @endcan

                        @can('user.unassign.organization')
                            @if($assignment->is_active)
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash"></i> Desasignar Usuario
                                </button>
                            @endif
                        @endcan

                        <a href="{{ route('users.assignments', $assignment->user) }}" class="btn btn-outline-primary">
                            <i class="bi bi-list"></i> Ver Todas las Asignaciones del Usuario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Desasignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('assignments.destroy', $assignment) }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>¿Está seguro que desea desasignar a <strong>{{ $assignment->user->full_name }}</strong> de <strong>{{ $assignment->organizationUnit->name }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Motivo (opcional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Describa el motivo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Desasignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-placeholder-lg {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: bold;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 12px;
    bottom: -8px;
    width: 2px;
    background: #e9ecef;
}
</style>
@endpush