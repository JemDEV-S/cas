@extends('layouts.app')

@section('title', 'Transferir Usuarios')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-arrow-left-right"></i> Transferir Usuarios entre Unidades
                        </h5>
                        <a href="{{ route('assignments.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('assignments.transfer.store') }}" id="transferForm">
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
                            <strong>Transferencia de Usuarios:</strong> Esta operación transferirá todos los usuarios activos de una unidad a otra.
                            Las asignaciones antiguas serán finalizadas y se crearán nuevas asignaciones.
                        </div>

                        {{-- Unidad Origen --}}
                        <div class="mb-4">
                            <label for="from_unit_id" class="form-label required">
                                <i class="bi bi-building text-danger"></i> Unidad Organizacional Origen
                            </label>
                            <select name="from_unit_id" id="from_unit_id" 
                                    class="form-select @error('from_unit_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione unidad origen</option>
                                @foreach($organizationalUnits as $unit)
                                    <option value="{{ $unit->id }}" 
                                        data-name="{{ $unit->name }}"
                                        {{ old('from_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('from_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Unidad desde donde se transferirán los usuarios
                            </small>
                        </div>

                        {{-- Icono de transferencia --}}
                        <div class="text-center mb-4">
                            <i class="bi bi-arrow-down-circle display-1 text-primary"></i>
                        </div>

                        {{-- Unidad Destino --}}
                        <div class="mb-4">
                            <label for="to_unit_id" class="form-label required">
                                <i class="bi bi-building text-success"></i> Unidad Organizacional Destino
                            </label>
                            <select name="to_unit_id" id="to_unit_id" 
                                    class="form-select @error('to_unit_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione unidad destino</option>
                                @foreach($organizationalUnits as $unit)
                                    <option value="{{ $unit->id }}" 
                                        data-name="{{ $unit->name }}"
                                        {{ old('to_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('to_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Unidad hacia donde se transferirán los usuarios
                            </small>
                        </div>

                        {{-- Fecha de transferencia --}}
                        <div class="mb-4">
                            <label for="transfer_date" class="form-label required">
                                <i class="bi bi-calendar-event"></i> Fecha de Transferencia
                            </label>
                            <input type="date" name="transfer_date" id="transfer_date" 
                                   class="form-control @error('transfer_date') is-invalid @enderror" 
                                   value="{{ old('transfer_date', date('Y-m-d')) }}" 
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Fecha en que se hará efectiva la transferencia
                            </small>
                        </div>

                        {{-- Resumen de la transferencia --}}
                        <div id="transferSummary" class="d-none">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Resumen de Transferencia</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <strong class="text-danger">Desde:</strong>
                                            <p id="fromUnitName" class="mb-0"></p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong class="text-success">Hacia:</strong>
                                            <p id="toUnitName" class="mb-0"></p>
                                        </div>
                                        <div class="col-12">
                                            <strong>Fecha efectiva:</strong>
                                            <p id="transferDateText" class="mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Importante:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Se transferirán <strong>todos los usuarios activos</strong> de la unidad origen</li>
                                <li>Las asignaciones en la unidad origen terminarán el día anterior a la fecha de transferencia</li>
                                <li>Se crearán nuevas asignaciones en la unidad destino con la fecha especificada</li>
                                <li>Se mantendrá el tipo de asignación (principal o secundaria) de cada usuario</li>
                                <li>Los usuarios serán notificados del cambio</li>
                                <li><strong>Esta operación no se puede deshacer</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-arrow-left-right"></i> Transferir Usuarios
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
    const fromUnit = document.getElementById('from_unit_id');
    const toUnit = document.getElementById('to_unit_id');
    const transferDate = document.getElementById('transfer_date');
    const summary = document.getElementById('transferSummary');
    const form = document.getElementById('transferForm');
    
    function updateSummary() {
        if (fromUnit.value && toUnit.value && transferDate.value) {
            const fromOption = fromUnit.options[fromUnit.selectedIndex];
            const toOption = toUnit.options[toUnit.selectedIndex];
            
            document.getElementById('fromUnitName').textContent = fromOption.dataset.name;
            document.getElementById('toUnitName').textContent = toOption.dataset.name;
            
            const date = new Date(transferDate.value);
            document.getElementById('transferDateText').textContent = date.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            summary.classList.remove('d-none');
        } else {
            summary.classList.add('d-none');
        }
    }
    
    fromUnit.addEventListener('change', function() {
        // Prevenir seleccionar la misma unidad
        if (this.value && this.value === toUnit.value) {
            alert('La unidad origen y destino deben ser diferentes');
            this.value = '';
        }
        updateSummary();
    });
    
    toUnit.addEventListener('change', function() {
        // Prevenir seleccionar la misma unidad
        if (this.value && this.value === fromUnit.value) {
            alert('La unidad origen y destino deben ser diferentes');
            this.value = '';
        }
        updateSummary();
    });
    
    transferDate.addEventListener('change', updateSummary);
    
    form.addEventListener('submit', function(e) {
        if (!confirm('¿Está seguro que desea transferir todos los usuarios de una unidad a otra? Esta operación no se puede deshacer.')) {
            e.preventDefault();
            return false;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando transferencia...';
        submitBtn.disabled = true;
    });
    
    // Actualizar resumen al cargar si hay valores
    updateSummary();
});
</script>
@endpush