@extends('layouts.app')

@section('title', 'Asignación Masiva')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people"></i> Asignación Masiva de Usuarios
                        </h5>
                        <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('assignments.bulk.store') }}" id="bulkAssignForm">
                    @csrf
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Errores:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Asignación Masiva:</strong> Permite asignar múltiples usuarios a una unidad organizacional en una sola operación.
                            Límite máximo: 100 usuarios por operación.
                        </div>

                        {{-- Unidad Organizacional --}}
                        <div class="mb-4">
                            <label for="organization_unit_id" class="form-label required">
                                Unidad Organizacional Destino
                            </label>
                            <select name="organization_unit_id" id="organization_unit_id" 
                                    class="form-select @error('organization_unit_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione una unidad</option>
                                @foreach($organizationalUnits as $unit)
                                    <option value="{{ $unit->id }}" {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Selección de Usuarios --}}
                        <div class="mb-4">
                            <label class="form-label required">Usuarios a Asignar</label>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                                <i class="bi bi-check-square"></i> Seleccionar Todos
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                                <i class="bi bi-square"></i> Deseleccionar Todos
                                            </button>
                                        </div>
                                        <span id="selectedCount" class="badge bg-primary">0 seleccionados</span>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div class="row g-2">
                                        @foreach($users as $user)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input user-checkbox" 
                                                           type="checkbox" 
                                                           name="user_ids[]" 
                                                           value="{{ $user->id }}" 
                                                           id="user_{{ $user->id }}"
                                                           {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="user_{{ $user->id }}">
                                                        <strong>{{ $user->full_name }}</strong><br>
                                                        <small class="text-muted">DNI: {{ $user->dni }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('user_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fechas --}}
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="start_date" class="form-label required">Fecha de Inicio</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', date('Y-m-d')) }}" 
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="end_date" class="form-label">Fecha de Fin (Opcional)</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Atención:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Todas las asignaciones serán creadas como <strong>secundarias</strong></li>
                                <li>Los usuarios serán notificados por correo electrónico</li>
                                <li>Si algún usuario ya tiene asignación en esa unidad, se omitirá</li>
                                <li>El proceso puede tardar varios segundos según la cantidad de usuarios</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Asignar Usuarios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateSelectedCount() {
    const count = document.querySelectorAll('.user-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count + ' seleccionados';
    
    // Deshabilitar botón si no hay usuarios seleccionados
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = count === 0;
}

function selectAll() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function deselectAll() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

document.addEventListener('DOMContentLoaded', function() {
    // Actualizar contador al cargar
    updateSelectedCount();
    
    // Actualizar contador al cambiar checkboxes
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Validar al enviar
    document.getElementById('bulkAssignForm').addEventListener('submit', function(e) {
        const count = document.querySelectorAll('.user-checkbox:checked').length;
        
        if (count === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un usuario');
            return false;
        }
        
        if (count > 100) {
            e.preventDefault();
            alert('No puede seleccionar más de 100 usuarios a la vez');
            return false;
        }
        
        // Confirmar acción
        if (!confirm(`¿Está seguro que desea asignar ${count} usuarios?`)) {
            e.preventDefault();
            return false;
        }
        
        // Mostrar loading
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        submitBtn.disabled = true;
    });
});
</script>
@endpush