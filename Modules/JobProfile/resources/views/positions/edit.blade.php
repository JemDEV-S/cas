@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Editar Código de Posición</h2>
                <a href="{{ route('jobprofile.positions.show', $positionCode->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            @if($positionCode->jobProfiles()->whereNotIn('status', ['draft', 'rejected'])->exists())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Este código de posición tiene perfiles asociados en proceso o aprobados.
                    Los cambios en el salario no afectarán a los perfiles existentes.
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('jobprofile.positions.update', $positionCode->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="code">Código <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   value="{{ old('code', $positionCode->code) }}"
                                   required>
                            <small class="form-text text-muted">
                                Use letras mayúsculas, números y guiones. Ej: CAP-001, ESP-001
                            </small>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Nombre del Puesto <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $positionCode->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3">{{ old('description', $positionCode->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Información Salarial</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="base_salary">Salario Base (S/) <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('base_salary') is-invalid @enderror"
                                           id="base_salary"
                                           name="base_salary"
                                           value="{{ old('base_salary', $positionCode->base_salary) }}"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('base_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="essalud_percentage">Porcentaje EsSalud (%)</label>
                                    <input type="number"
                                           class="form-control @error('essalud_percentage') is-invalid @enderror"
                                           id="essalud_percentage"
                                           name="essalud_percentage"
                                           value="{{ old('essalud_percentage', $positionCode->essalud_percentage) }}"
                                           step="0.01"
                                           min="0"
                                           max="100">
                                    @error('essalud_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contract_months">Meses de Contrato <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('contract_months') is-invalid @enderror"
                                   id="contract_months"
                                   name="contract_months"
                                   value="{{ old('contract_months', $positionCode->contract_months) }}"
                                   min="1"
                                   max="12"
                                   required>
                            @error('contract_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $positionCode->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Código activo
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Los montos de EsSalud, total mensual y total por periodo se calcularán automáticamente.
                        </div>

                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Valores Actuales:</h6>
                                <ul class="mb-0">
                                    <li>EsSalud: S/ {{ number_format($positionCode->essalud_amount, 2) }}</li>
                                    <li>Total Mensual: {{ $positionCode->formatted_monthly_total }}</li>
                                    <li>Total Periodo: {{ $positionCode->formatted_quarterly_total }}</li>
                                </ul>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('jobprofile.positions.show', $positionCode->id) }}"
                               class="btn btn-secondary mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Código
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseSalaryInput = document.getElementById('base_salary');
    const essaludPercentageInput = document.getElementById('essalud_percentage');
    const contractMonthsInput = document.getElementById('contract_months');

    function calculateTotals() {
        const baseSalary = parseFloat(baseSalaryInput.value) || 0;
        const essaludPercentage = parseFloat(essaludPercentageInput.value) || 9.0;
        const contractMonths = parseInt(contractMonthsInput.value) || 3;

        const essaludAmount = baseSalary * (essaludPercentage / 100);
        const monthlyTotal = baseSalary + essaludAmount;
        const periodTotal = monthlyTotal * contractMonths;

        // Update preview in info card
        console.log('New values:', {
            essalud: essaludAmount.toFixed(2),
            monthly: monthlyTotal.toFixed(2),
            period: periodTotal.toFixed(2)
        });
    }

    baseSalaryInput.addEventListener('input', calculateTotals);
    essaludPercentageInput.addEventListener('input', calculateTotals);
    contractMonthsInput.addEventListener('input', calculateTotals);
});
</script>
@endpush
