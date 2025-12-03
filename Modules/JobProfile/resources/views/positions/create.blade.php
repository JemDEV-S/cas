@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Crear Código de Posición</h1>
            <p class="mt-1 text-sm text-gray-600">Define un nuevo código con sus requisitos y remuneración</p>
        </div>
        <a href="{{ route('jobprofile.positions.index') }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('jobprofile.positions.store') }}" method="POST">
            @csrf

            <div class="px-6 py-5 space-y-6">
                {{-- Información Básica --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h2>

                    <div class="space-y-4">
                        {{-- Código --}}
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                Código <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror"
                                   id="code"
                                   name="code"
                                   value="{{ old('code') }}"
                                   placeholder="Ej: CAP-001, ESP-001"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">Use letras mayúsculas, números y guiones. Ej: CAP-001, ESP-001</p>
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nombre del Puesto --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Puesto <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Ej: Especialista en Sistemas"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Descripción breve del puesto">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Información Salarial --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Salarial</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Salario Base --}}
                        <div>
                            <label for="base_salary" class="block text-sm font-medium text-gray-700 mb-1">
                                Salario Base (S/) <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('base_salary') border-red-500 @enderror"
                                   id="base_salary"
                                   name="base_salary"
                                   value="{{ old('base_salary') }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            @error('base_salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Porcentaje EsSalud --}}
                        <div>
                            <label for="essalud_percentage" class="block text-sm font-medium text-gray-700 mb-1">
                                Porcentaje EsSalud (%)
                            </label>
                            <input type="number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('essalud_percentage') border-red-500 @enderror"
                                   id="essalud_percentage"
                                   name="essalud_percentage"
                                   value="{{ old('essalud_percentage', 9.0) }}"
                                   step="0.01"
                                   min="0"
                                   max="100">
                            <p class="mt-1 text-sm text-gray-500">Por defecto: 9%</p>
                            @error('essalud_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Meses de Contrato --}}
                    <div class="mt-4">
                        <label for="contract_months" class="block text-sm font-medium text-gray-700 mb-1">
                            Meses de Contrato <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('contract_months') border-red-500 @enderror"
                               id="contract_months"
                               name="contract_months"
                               value="{{ old('contract_months', 3) }}"
                               min="1"
                               max="12"
                               required>
                        <p class="mt-1 text-sm text-gray-500">Por defecto: 3 meses</p>
                        @error('contract_months')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preview Card --}}
                    <div id="salary-preview" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">Vista Previa de Cálculos</h3>
                        <div class="space-y-1 text-sm text-blue-800">
                            <div class="flex justify-between">
                                <span>EsSalud:</span>
                                <span id="preview-essalud" class="font-semibold">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Mensual:</span>
                                <span id="preview-monthly" class="font-semibold">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between border-t border-blue-300 pt-1">
                                <span class="font-semibold">Total Periodo:</span>
                                <span id="preview-period" class="font-bold text-base">S/ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Requisitos Profesionales --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Requisitos Profesionales</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Experiencia Profesional General --}}
                        <div>
                            <label for="min_professional_experience" class="block text-sm font-medium text-gray-700 mb-1">
                                Experiencia Profesional General (años)
                            </label>
                            <input type="number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_professional_experience') border-red-500 @enderror"
                                   id="min_professional_experience"
                                   name="min_professional_experience"
                                   value="{{ old('min_professional_experience') }}"
                                   step="0.5"
                                   min="0"
                                   placeholder="Ej: 2.0">
                            <p class="mt-1 text-xs text-gray-500">Años mínimos de experiencia profesional total</p>
                            @error('min_professional_experience')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Experiencia Específica --}}
                        <div>
                            <label for="min_specific_experience" class="block text-sm font-medium text-gray-700 mb-1">
                                Experiencia Específica del Puesto (años)
                            </label>
                            <input type="number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_specific_experience') border-red-500 @enderror"
                                   id="min_specific_experience"
                                   name="min_specific_experience"
                                   value="{{ old('min_specific_experience') }}"
                                   step="0.5"
                                   min="0"
                                   placeholder="Ej: 1.0">
                            <p class="mt-1 text-xs text-gray-500">Años mínimos en el puesto o área específica</p>
                            @error('min_specific_experience')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        {{-- Título Profesional --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Título Profesional</label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           name="requires_professional_title"
                                           value="1"
                                           {{ old('requires_professional_title') == '1' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">Requerido</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           name="requires_professional_title"
                                           value="0"
                                           {{ old('requires_professional_title', '0') == '0' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">No requerido</span>
                                </label>
                            </div>
                            @error('requires_professional_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Colegiatura --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Colegiatura Profesional</label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           name="requires_professional_license"
                                           value="1"
                                           {{ old('requires_professional_license') == '1' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">Requerida</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio"
                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                           name="requires_professional_license"
                                           value="0"
                                           {{ old('requires_professional_license', '0') == '0' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">No requerida</span>
                                </label>
                            </div>
                            @error('requires_professional_license')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Niveles Educativos --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Niveles Educativos Aceptados</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @php
                                $educationLevels = [
                                    'secundaria_completa' => 'Secundaria Completa',
                                    'tecnico' => 'Técnico',
                                    'bachiller' => 'Bachiller',
                                    'titulado' => 'Titulado',
                                    'maestria' => 'Maestría',
                                    'doctorado' => 'Doctorado'
                                ];
                            @endphp
                            @foreach($educationLevels as $value => $label)
                                <label class="inline-flex items-center">
                                    <input type="checkbox"
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                           name="education_levels_accepted[]"
                                           value="{{ $value }}"
                                           {{ in_array($value, old('education_levels_accepted', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Selecciona uno o más niveles educativos aceptados para este puesto</p>
                        @error('education_levels_accepted')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="border-t border-gray-200"></div>

                {{-- Estado --}}
                <div>
                    <div class="flex items-center">
                        <input class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                               type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="ml-2 text-sm font-medium text-gray-700" for="is_active">
                            Código activo
                        </label>
                    </div>
                </div>

                {{-- Info Alert --}}
                <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-blue-700">
                            Los montos de EsSalud, total mensual y total por periodo se calcularán automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-end space-x-3">
                <a href="{{ route('jobprofile.positions.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Código
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseSalaryInput = document.getElementById('base_salary');
    const essaludPercentageInput = document.getElementById('essalud_percentage');
    const contractMonthsInput = document.getElementById('contract_months');
    const previewDiv = document.getElementById('salary-preview');
    const previewEssalud = document.getElementById('preview-essalud');
    const previewMonthly = document.getElementById('preview-monthly');
    const previewPeriod = document.getElementById('preview-period');

    function calculateTotals() {
        const baseSalary = parseFloat(baseSalaryInput.value) || 0;
        const essaludPercentage = parseFloat(essaludPercentageInput.value) || 9.0;
        const contractMonths = parseInt(contractMonthsInput.value) || 3;

        if (baseSalary > 0) {
            const essaludAmount = baseSalary * (essaludPercentage / 100);
            const monthlyTotal = baseSalary + essaludAmount;
            const periodTotal = monthlyTotal * contractMonths;

            previewDiv.classList.remove('hidden');
            previewEssalud.textContent = `S/ ${essaludAmount.toFixed(2)}`;
            previewMonthly.textContent = `S/ ${monthlyTotal.toFixed(2)}`;
            previewPeriod.textContent = `S/ ${periodTotal.toFixed(2)} (${contractMonths} meses)`;
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    baseSalaryInput.addEventListener('input', calculateTotals);
    essaludPercentageInput.addEventListener('input', calculateTotals);
    contractMonthsInput.addEventListener('input', calculateTotals);

    // Calculate on page load if values exist
    calculateTotals();
});
</script>
@endpush
