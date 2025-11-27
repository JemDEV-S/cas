@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Crear Código de Posición</h2>
                <a href="{{ route('jobprofile.positions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('jobprofile.positions.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="code">Código <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   value="{{ old('code') }}"
                                   placeholder="Ej: CAP-001, ESP-001"
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
                                   value="{{ old('name') }}"
                                   placeholder="Ej: Especialista en Sistemas"
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
                                      rows="3"
                                      placeholder="Descripción breve del puesto">{{ old('description') }}</textarea>
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
                                           value="{{ old('base_salary') }}"
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
                                           value="{{ old('essalud_percentage', 9.0) }}"
                                           step="0.01"
                                           min="0"
                                           max="100">
                                    <small class="form-text text-muted">Por defecto: 9%</small>
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
                                   value="{{ old('contract_months', 3) }}"
                                   min="1"
                                   max="12"
                                   required>
                            <small class="form-text text-muted">Por defecto: 3 meses</small>
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
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Código activo
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Los montos de EsSalud, total mensual y total por periodo se calcularán automáticamente.
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('jobprofile.positions.index') }}" class="btn btn-secondary mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Código
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

        // Show preview
        const previewDiv = document.getElementById('salary-preview');
        if (previewDiv && baseSalary > 0) {
            previewDiv.innerHTML = `
                <strong>Vista Previa:</strong><br>
                EsSalud: S/ ${essaludAmount.toFixed(2)}<br>
                Total Mensual: S/ ${monthlyTotal.toFixed(2)}<br>
                Total Periodo (${contractMonths} meses): S/ ${periodTotal.toFixed(2)}
            `;
        }
    }

    baseSalaryInput.addEventListener('input', calculateTotals);
    essaludPercentageInput.addEventListener('input', calculateTotals);
    contractMonthsInput.addEventListener('input', calculateTotals);
});
</script>
@endpush
