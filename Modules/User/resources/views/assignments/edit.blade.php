@extends('layouts.app')

@section('title', 'Editar Asignación')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil"></i> Editar Asignación
                        </h5>
                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('assignments.update', $assignment) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Errores de validación:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Usuario (solo lectura) --}}
                        <div class="mb-4">
                            <label class="form-label">Usuario</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        @if($assignment->user->photo_url)
                                            <img src="{{ $assignment->user->photo_url }}" class="rounded-circle me-3" width="50">
                                        @endif
                                        <div>
                                            <strong>{{ $assignment->user->full_name }}</strong><br>
                                            <small class="text-muted">DNI: {{ $assignment->user->dni }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Unidad Organizacional (solo lectura) --}}
                        <div class="mb-4">
                            <label class="form-label">Unidad Organizacional</label>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <strong>{{ $assignment->organizationUnit->name }}</strong><br>
                                    <small class="text-muted">Código: {{ $assignment->organizationUnit->code }}</small>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> No es posible cambiar la unidad organizacional. 
                                Debe crear una nueva asignación.
                            </small>
                        </div>

                        {{-- Fechas --}}
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="start_date" class="form-label required">Fecha de Inicio</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', $assignment->start_date->format('Y-m-d')) }}" 
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="end_date" class="form-label">Fecha de Fin (Opcional)</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date', $assignment->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Dejar vacío para asignación indefinida
                                </small>
                            </div>
                        </div>

                        {{-- Tipo y estado --}}
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="is_primary" id="is_primary" 
                                           value="1" 
                                           {{ old('is_primary', $assignment->is_primary) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_primary">
                                        <strong>Asignación Principal</strong>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="is_active" id="is_active" 
                                           value="1" 
                                           {{ old('is_active', $assignment->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Asignación Activa</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Importante:</strong> Los cambios afectarán inmediatamente los permisos y accesos del usuario.
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection