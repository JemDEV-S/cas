@extends('layouts.app')

@section('title', 'Asignaciones de ' . $user->full_name)

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado con info del usuario --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        @if($user->photo_url)
                            <img src="{{ $user->photo_url }}" class="rounded-circle me-3" width="80">
                        @else
                            <div class="avatar-placeholder-lg me-3">
                                {{ substr($user->first_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="mb-1">{{ $user->full_name }}</h3>
                            <p class="text-muted mb-0">
                                <i class="bi bi-envelope"></i> {{ $user->email }} &nbsp;|&nbsp; 
                                <i class="bi bi-card-text"></i> DNI: {{ $user->dni }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-primary">
                        <i class="bi bi-person"></i> Ver Perfil
                    </a>
                    @can('user.assign.organization')
                        <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Nueva Asignación
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Asignación Principal --}}
    @if($primaryAssignment)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-star-fill"></i> Asignación Organizacional Principal
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>{{ $primaryAssignment->organizationUnit->name }}</h4>
                        <p class="text-muted mb-0">Código: {{ $primaryAssignment->organizationUnit->code }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1">
                            <strong>Desde:</strong> {{ $primaryAssignment->start_date->format('d/m/Y') }}
                        </p>
                        @if($primaryAssignment->end_date)
                            <p class="mb-0">
                                <strong>Hasta:</strong> {{ $primaryAssignment->end_date->format('d/m/Y') }}
                            </p>
                        @else
                            <span class="badge bg-success">Indefinido</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('assignments.show', $primaryAssignment) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i> Ver Detalle
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            Este usuario no tiene una asignación organizacional principal activa.
        </div>
    @endif

    {{-- Asignaciones Activas --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-check-circle text-success"></i> Asignaciones Activas ({{ $activeAssignments->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($activeAssignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unidad Organizacional</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeAssignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->organizationUnit->name }}</strong><br>
                                        <small class="text-muted">{{ $assignment->organizationUnit->code }}</small>
                                    </td>
                                    <td>{{ $assignment->start_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($assignment->end_date)
                                            {{ $assignment->end_date->format('d/m/Y') }}
                                        @else
                                            <span class="badge bg-secondary">Indefinido</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($assignment->is_primary)
                                            <span class="badge bg-primary">
                                                <i class="bi bi-star-fill"></i> Principal
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Secundaria</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('assignments.show', $assignment) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No hay asignaciones activas</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Historial Completo --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Historial de Asignaciones ({{ $assignmentHistory->count() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($assignmentHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unidad Organizacional</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignmentHistory as $assignment)
                                <tr class="{{ !$assignment->is_active ? 'table-secondary' : '' }}">
                                    <td>
                                        <strong>{{ $assignment->organizationUnit->name }}</strong><br>
                                        <small class="text-muted">{{ $assignment->organizationUnit->code }}</small>
                                    </td>
                                    <td>{{ $assignment->start_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($assignment->end_date)
                                            {{ $assignment->end_date->format('d/m/Y') }}
                                        @else
                                            <span class="badge bg-secondary">Indefinido</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($assignment->is_primary)
                                            <span class="badge bg-primary">Principal</span>
                                        @else
                                            <span class="badge bg-secondary">Secundaria</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($assignment->is_active && $assignment->isCurrent())
                                            <span class="badge bg-success">Activa</span>
                                        @elseif($assignment->is_active)
                                            <span class="badge bg-warning">Programada</span>
                                        @else
                                            <span class="badge bg-danger">Finalizada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('assignments.show', $assignment) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No hay historial de asignaciones</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-placeholder-lg {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
}
</style>
@endpush