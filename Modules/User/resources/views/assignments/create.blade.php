@extends('layouts.app')

@section('title', 'Nueva Asignación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Nueva Asignación Organizacional
                        </h5>
                        <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('assignments.store') }}">
                    @csrf
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Por favor corrija los siguientes errores:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            </div>
                        @endif

                        {{-- Usuario --}}
                        <div class="mb-4">
                            <label for="user_id" class="form-label required">Usuario</label>
                            <select name="user_id" id="user_id" 
                                    class="form-select @error('user_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione un usuario</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                        {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }} - DNI: {{ $user->dni }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Seleccione el usuario que será asignado
                            </small>
                        </div>

                        {{-- Unidad Organizacional --}}
                        <div class="mb-4">
                            <label for="organization_unit_id" class="form-label required">
                                Unidad Organizacional
                            </label>
                            <select name="organization_unit_id" id="organization_unit_id" 
                                    class="form-select @error('organization_unit_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione una unidad</option>
                                @foreach($organizationalUnits as $unit)
                                    <option value="{{ $unit->id }}" 
                                        {{ old('organization_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Unidad organizacional a la que se asignará el usuario
                            </small>
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
                                <small class="form-text text-muted">
                                    Fecha de inicio de la asignación
                                </small>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="end_date" class="form-label">Fecha de Fin (Opcional)</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Dejar vacío para asignación indefinida
                                </small>
                            </div>
                        </div>

                        {{-- Tipo de asignación --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       name="is_primary" id="is_primary" 
                                       value="1" {{ old('is_primary') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_primary">
                                    <strong>Asignación Principal</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Marque esta opción si esta será la unidad organizacional principal del usuario.
                                Si ya tiene una asignación principal, esta será marcada como secundaria automáticamente.
                            </small>
                        </div>

                        {{-- Vista previa de información --}}
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-info-circle"></i> Información
                            </h6>
                            <ul class="mb-0">
                                <li>El usuario será notificado de su asignación por correo electrónico</li>
                                <li>Puede tener múltiples asignaciones activas simultáneamente</li>
                                <li>Solo puede tener una asignación principal a la vez</li>
                                <li>Las fechas pueden modificarse posteriormente si es necesario</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Asignación
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
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    // Validar que fecha fin sea posterior a fecha inicio
    endDate.addEventListener('change', function() {
        if (this.value && startDate.value) {
            if (new Date(this.value) <= new Date(startDate.value)) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                this.value = '';
            }
        }
    });

    // Actualizar fecha mínima de fecha fin cuando cambia fecha inicio
    startDate.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            endDate.min = nextDay.toISOString().split('T')[0];
            
            // Limpiar fecha fin si es menor que nueva fecha inicio
            if (endDate.value && new Date(endDate.value) <= new Date(this.value)) {
                endDate.value = '';
            }
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.required::after {
    content: " *";
    color: red;
}
</style>
@endpush