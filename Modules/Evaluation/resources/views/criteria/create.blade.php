@extends('layouts.app')

@section('title', 'Crear Criterio de Evaluación')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i>
                        Nuevo Criterio de Evaluación
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <h2 class="text-xl font-semibold text-white">Nuevo Criterio de Evaluación</h2>
                </div>
                <div class="p-6">
                    <form id="createCriterionForm">
                        @csrf

                        <!-- Fase -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fase *</label>
                            <select name="phase_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Seleccionar fase...</option>
                                @foreach($phases ?? [] as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Convocatoria (Opcional) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Convocatoria Específica (Opcional)</label>
                            <select name="job_posting_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Aplicar a todas las convocatorias</option>
                                @foreach($jobPostings ?? [] as $posting)
                                    <option value="{{ $posting->id }}">{{ $posting->title }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Si no se selecciona, aplicará a todas las convocatorias de la fase</p>
                        </div>

                        <!-- Código y Nombre -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Código *</label>
                                <input type="text" name="code" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required
                                       placeholder="Ej: ACAD-01">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                                <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required
                                       placeholder="Ej: Formación Académica">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                            <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="3"
                                      placeholder="Descripción detallada del criterio..."></textarea>
                        </div>

                        <!-- Puntajes -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Puntaje Mínimo *</label>
                                <input type="number" name="min_score" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required
                                       min="0" step="0.01" value="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Puntaje Máximo *</label>
                                <input type="number" name="max_score" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required
                                       min="0" step="0.01" value="100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Peso/Ponderación *</label>
                                <input type="number" name="weight" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required
                                       min="0" max="10" step="0.1" value="1">
                                <p class="mt-1 text-sm text-gray-500">De 0 a 10</p>
                            </div>
                        </div>

                        <!-- Tipo de Puntuación -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Puntuación *</label>
                            <select name="score_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="NUMERIC">Numérica</option>
                                <option value="PERCENTAGE">Porcentaje</option>
                                <option value="QUALITATIVE">Cualitativa</option>
                            </select>
                        </div>

                        <!-- Orden -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Orden de Visualización</label>
                            <input type="number" name="order" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   min="0" value="0">
                        </div>

                        <!-- Opciones -->
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="requires_comment" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       id="requiresComment">
                                <label for="requiresComment" class="ml-2 block text-sm text-gray-900">
                                    Requiere Comentario Obligatorio
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="requires_evidence" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       id="requiresEvidence">
                                <label for="requiresEvidence" class="ml-2 block text-sm text-gray-900">
                                    Requiere Evidencia
                                </label>
                            </div>
                        </div>

                        <!-- Guía de Evaluación -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Guía de Evaluación</label>
                            <textarea name="evaluation_guide" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="4"
                                      placeholder="Instrucciones para el evaluador al calificar este criterio..."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-between">
                            <a href="{{ route('evaluation-criteria.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="btnSubmit">
                                <i class="fas fa-save mr-2"></i>
                                Crear Criterio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('createCriterionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btnSubmit = document.getElementById('btnSubmit');

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';

    // Convertir checkboxes
    formData.set('requires_comment', document.getElementById('requiresComment').checked ? '1' : '0');
    formData.set('requires_evidence', document.getElementById('requiresEvidence').checked ? '1' : '0');

    fetch('/evaluation-criteria', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/evaluation-criteria';
        } else {
            alert(data.message || 'Error al crear el criterio');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-save mr-2"></i>Crear Criterio';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el criterio');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-save mr-2"></i>Crear Criterio';
    });
});
</script>
@endsection
