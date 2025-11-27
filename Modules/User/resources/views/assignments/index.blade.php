@extends('layouts.app')

@section('title', 'Asignaciones Organizacionales')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-0">Asignaciones Organizacionales</h2>
            <p class="text-muted">Gestión de asignaciones de usuarios a unidades organizacionales</p>
        </div>
        <div class="col-md-4 text-end">
            @can('user.assign.organization')
                <div class="btn-group">
                    <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nueva Asignación
                    </a>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        <span class="visually-hidden">Más opciones</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('assignments.bulk.create') }}">
                                <i class="bi bi-people"></i> Asignación Masiva
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('assignments.transfer.create') }}">
                                <i class="bi bi-arrow-left-right"></i> Transferir Usuarios
                            </a>
                        </li>
                    </ul>
                </div>
            @endcan
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel"></i> Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('assignments.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nombre, DNI, código..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Usuario</label>
                        <select name="user_id" class="form-select">
                            <option value="">Todos los usuarios</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                    {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }} ({{ $user->dni }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unidad Organizacional</label>
                        <select name="organization_unit_id" class="form-select">
                            <option value="">Todas las unidades</option>
                            @foreach($organizationalUnits as $unit)
                                <option value="{{ $unit->id }}" 
                                    {{ request('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="is_active" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>
                                Activas
                            </option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>
                                Inactivas
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="is_primary" class="form-select">
                            <option value="">Todas</option>
                            <option value="1" {{ request('is_primary') === '1' ? 'selected' : '' }}>
                                Primarias
                            </option>
                            <option value="0" {{ request('is_primary') === '0' ? 'selected' : '' }}>
                                Secundarias
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="current_only" 
                                   id="currentOnly" value="1" 
                                   {{ request('current_only') ? 'checked' : '' }}>
                            <label class="form-check-label" for="currentOnly">
                                Solo vigentes
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de asignaciones --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Lista de Asignaciones ({{ $assignments->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Unidad Organizacional</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                @if($assignment->user->photo_url)
                                                    <img src="{{ $assignment->user->photo_url }}" 
                                                         class="rounded-circle" width="40">
                                                @else
                                                    <div class="avatar-placeholder">
                                                        {{ substr($assignment->user->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $assignment->user->full_name }}</strong><br>
                                                <small class="text-muted">DNI: {{ $assignment->user->dni }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $assignment->organizationUnit->name }}</strong><br>
                                        <small class="text-muted">{{ $assignment->organizationUnit->code }}</small>
                                    </td>
                                    <td>
                                        <span class="text-nowrap">
                                            {{ $assignment->start_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($assignment->end_date)
                                            <span class="text-nowrap">
                                                {{ $assignment->end_date->format('d/m/Y') }}
                                            </span>
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
                                        @if($assignment->is_active && $assignment->isCurrent())
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Activa
                                            </span>
                                        @elseif($assignment->is_active)
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock"></i> Programada
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @can('user.view.assignments')
                                                <a href="{{ route('assignments.show', $assignment) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan
                                            
                                            @can('user.update.assignment')
                                                <a href="{{ route('assignments.edit', $assignment) }}" 
                                                   class="btn btn-outline-warning" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            
                                            @can('user.unassign.organization')
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="confirmDelete('{{ $assignment->id }}')"
                                                        title="Desasignar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Mostrando {{ $assignments->firstItem() }} - {{ $assignments->lastItem() }} 
                            de {{ $assignments->total() }} resultados
                        </div>
                        <div>
                            {{ $assignments->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No se encontraron asignaciones</p>
                    @can('user.assign.organization')
                        <a href="{{ route('assignments.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Crear Primera Asignación
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de confirmación de eliminación --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Desasignación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>¿Está seguro que desea desasignar este usuario?</p>
                    <div class="mb-3">
                        <label class="form-label">Motivo (opcional)</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Describa el motivo de la desasignación..."></textarea>
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

@push('scripts')
<script>
function confirmDelete(assignmentId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/assignments/${assignmentId}`;
    deleteModal.show();
}
</script>
@endpush