@extends('layouts.app')

@section('title', 'Reevaluacion - ' . $application->code)

@section('content')
<div class="space-y-6">
    <!-- Header con acciones -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $application->code }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Reevaluacion de elegibilidad - {{ $application->full_name }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    @if($application->eligibilityOverride)
                        <a href="{{ route('admin.eligibility-override.pdf', $application->id) }}"
                           class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded"
                           target="_blank">
                            Descargar PDF
                        </a>
                    @endif
                    <a href="{{ route('admin.eligibility-override.index', $application->jobProfile->job_posting_id) }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado actual y resolucion (si existe) -->
    @if($application->eligibilityOverride)
        <div class="bg-white shadow sm:rounded-lg border-l-4
            {{ $application->eligibilityOverride->decision->value === 'APPROVED' ? 'border-green-500' : 'border-red-500' }}">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold
                            {{ $application->eligibilityOverride->decision->value === 'APPROVED' ? 'text-green-800' : 'text-red-800' }}">
                            @if($application->eligibilityOverride->decision->value === 'APPROVED')
                                RECLAMO APROBADO - Postulante ahora es APTO
                            @else
                                RECLAMO RECHAZADO - Postulante mantiene estado NO APTO
                            @endif
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Resuelto por {{ $application->eligibilityOverride->resolver->name ?? 'N/A' }}
                            el {{ $application->eligibilityOverride->resolved_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        {{ $application->eligibilityOverride->decision->value === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $application->eligibilityOverride->decision->label() }}
                    </span>
                </div>

                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="font-semibold text-gray-800">{{ $application->eligibilityOverride->resolution_summary }}</p>
                    <p class="mt-2 text-gray-600">{{ $application->eligibilityOverride->resolution_detail }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna izquierda: Datos del postulante -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Datos Personales -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Datos del Postulante</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Nombre Completo</p>
                            <p class="font-semibold text-gray-900">{{ strtoupper($application->full_name) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-semibold text-gray-900">{{ $application->dni }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-gray-900">{{ $application->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Telefono</p>
                            <p class="text-gray-900">{{ $application->mobile_phone ?? $application->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Perfil al que postula -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Perfil de Puesto</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Codigo</p>
                            <p class="font-semibold text-gray-900">{{ $application->jobProfile->code ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nombre del Perfil</p>
                            <p class="text-gray-900">{{ $application->jobProfile->profile_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Convocatoria</p>
                            <p class="text-gray-900">{{ $application->jobProfile->jobPosting->code ?? 'N/A' }} - {{ $application->jobProfile->jobPosting->title ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo original de NO APTO -->
            @if($application->ineligibility_reason || ($application->eligibilityOverride && $application->eligibilityOverride->original_reason))
                <div class="bg-red-50 border border-red-200 shadow sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-red-200">
                        <h3 class="text-lg font-semibold text-red-800">Motivo Original de Inelegibilidad</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-red-700">
                            {{ $application->eligibilityOverride->original_reason ?? $application->ineligibility_reason ?? 'No especificado' }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Evaluacion automatica (si existe) -->
            @if($application->latestEvaluation)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Resultado de Evaluacion Automatica</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        @php $eval = $application->latestEvaluation; @endphp

                        <!-- Academicos -->
                        @if($eval->academics_evaluation)
                            <div class="p-3 rounded {{ $eval->academics_evaluation['passed'] ?? false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium {{ $eval->academics_evaluation['passed'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                                        Formacion Academica
                                    </span>
                                    <span class="text-sm {{ $eval->academics_evaluation['passed'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $eval->academics_evaluation['passed'] ?? false ? 'CUMPLE' : 'NO CUMPLE' }}
                                    </span>
                                </div>
                                @if(!empty($eval->academics_evaluation['reason']))
                                    <p class="text-sm mt-1 {{ $eval->academics_evaluation['passed'] ?? false ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $eval->academics_evaluation['reason'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Experiencia General -->
                        @if($eval->general_experience_evaluation)
                            <div class="p-3 rounded {{ $eval->general_experience_evaluation['passed'] ?? false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium {{ $eval->general_experience_evaluation['passed'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                                        Experiencia General
                                    </span>
                                    <span class="text-sm {{ $eval->general_experience_evaluation['passed'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $eval->general_experience_evaluation['passed'] ?? false ? 'CUMPLE' : 'NO CUMPLE' }}
                                    </span>
                                </div>
                                @if(!empty($eval->general_experience_evaluation['reason']))
                                    <p class="text-sm mt-1 {{ $eval->general_experience_evaluation['passed'] ?? false ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $eval->general_experience_evaluation['reason'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Experiencia Especifica -->
                        @if($eval->specific_experience_evaluation)
                            <div class="p-3 rounded {{ $eval->specific_experience_evaluation['passed'] ?? false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium {{ $eval->specific_experience_evaluation['passed'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                                        Experiencia Especifica
                                    </span>
                                    <span class="text-sm {{ $eval->specific_experience_evaluation['passed'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $eval->specific_experience_evaluation['passed'] ?? false ? 'CUMPLE' : 'NO CUMPLE' }}
                                    </span>
                                </div>
                                @if(!empty($eval->specific_experience_evaluation['reason']))
                                    <p class="text-sm mt-1 {{ $eval->specific_experience_evaluation['passed'] ?? false ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $eval->specific_experience_evaluation['reason'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Colegiatura -->
                        @if($eval->professional_registry_evaluation)
                            <div class="p-3 rounded {{ $eval->professional_registry_evaluation['passed'] ?? false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium {{ $eval->professional_registry_evaluation['passed'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                                        Colegiatura Profesional
                                    </span>
                                    <span class="text-sm {{ $eval->professional_registry_evaluation['passed'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $eval->professional_registry_evaluation['passed'] ?? false ? 'CUMPLE' : 'NO CUMPLE' }}
                                    </span>
                                </div>
                                @if(!empty($eval->professional_registry_evaluation['reason']))
                                    <p class="text-sm mt-1 {{ $eval->professional_registry_evaluation['passed'] ?? false ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $eval->professional_registry_evaluation['reason'] }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Cursos requeridos -->
                        @if($eval->required_courses_evaluation)
                            <div class="p-3 rounded {{ $eval->required_courses_evaluation['passed'] ?? false ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium {{ $eval->required_courses_evaluation['passed'] ?? false ? 'text-green-800' : 'text-red-800' }}">
                                        Cursos Requeridos
                                    </span>
                                    <span class="text-sm {{ $eval->required_courses_evaluation['passed'] ?? false ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $eval->required_courses_evaluation['passed'] ?? false ? 'CUMPLE' : 'NO CUMPLE' }}
                                    </span>
                                </div>
                                @if(!empty($eval->required_courses_evaluation['reason']))
                                    <p class="text-sm mt-1 {{ $eval->required_courses_evaluation['passed'] ?? false ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $eval->required_courses_evaluation['reason'] }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Formacion Academica -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Formacion Academica</h3>
                </div>
                <div class="px-6 py-4">
                    @if($application->academics->count() > 0)
                        <div class="space-y-3">
                            @foreach($application->academics as $academic)
                                <div class="border rounded-lg p-3 {{ $academic->is_related_career ? 'border-amber-300 bg-amber-50' : 'border-gray-200' }}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $academic->degree_type }}
                                            </span>
                                            @if($academic->is_related_career)
                                                <span class="ml-1 px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                                    Carrera Afin
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $academic->issue_date?->format('d/m/Y') }}</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mt-2">{{ $academic->degree_title }}</h4>
                                    <p class="text-sm text-gray-600">{{ $academic->institution_name }}</p>
                                    @if($academic->career)
                                        <p class="text-sm text-gray-500 mt-1">Carrera: {{ $academic->career->name }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No hay formacion academica registrada.</p>
                    @endif
                </div>
            </div>

            <!-- Experiencia Laboral -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Experiencia Laboral</h3>
                </div>
                <div class="px-6 py-4">
                    @if($application->experiences->count() > 0)
                        <div class="space-y-3">
                            @foreach($application->experiences as $experience)
                                <div class="border rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $experience->position }}</h4>
                                            <p class="text-sm text-gray-600">{{ $experience->organization }}</p>
                                        </div>
                                        <div class="text-right">
                                            @if($experience->is_specific)
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Especifica</span>
                                            @else
                                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">General</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">
                                        {{ $experience->start_date?->format('d/m/Y') }} - {{ $experience->end_date?->format('d/m/Y') }}
                                        ({{ $experience->duration_days ?? 0 }} dias)
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No hay experiencia laboral registrada.</p>
                    @endif
                </div>
            </div>

            <!-- Historial reciente -->
            @if($application->history && $application->history->count() > 0)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Historial Reciente</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            @foreach($application->history->take(5) as $history)
                                <div class="flex items-start space-x-3 text-sm">
                                    <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full bg-gray-400"></div>
                                    <div>
                                        <p class="text-gray-900">{{ $history->description ?? $history->event_type_name }}</p>
                                        <p class="text-gray-500 text-xs">
                                            {{ $history->performed_at?->format('d/m/Y H:i') }}
                                            @if($history->performer)
                                                - {{ $history->performer->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Formulario de resolucion -->
        <div class="lg:col-span-1">
            @if($canBeReviewed)
                <!-- Formulario de resolucion -->
                <div class="bg-white shadow sm:rounded-lg sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-indigo-50">
                        <h3 class="text-lg font-semibold text-indigo-900">Resolver Reevaluacion</h3>
                        <p class="text-sm text-indigo-700 mt-1">Ingrese la resolucion del reclamo</p>
                    </div>
                    <div class="px-6 py-4">
                        <form id="resolution-form">
                            @csrf
                            <!-- Tipo de resolucion -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Resolucion</label>
                                <select name="resolution_type" id="resolution_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="CLAIM">Reclamo</option>
                                    <option value="CORRECTION">Correccion de Oficio</option>
                                    <option value="OTHER">Otro</option>
                                </select>
                            </div>

                            <!-- Resumen -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Resumen de la Resolucion *</label>
                                <input type="text" name="resolution_summary" id="resolution_summary"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       placeholder="Ej: Reclamo procede por documentacion adicional"
                                       maxlength="255" required>
                                <p class="text-xs text-gray-500 mt-1">Maximo 255 caracteres</p>
                            </div>

                            <!-- Detalle -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Detalle de la Resolucion *</label>
                                <textarea name="resolution_detail" id="resolution_detail" rows="6"
                                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                          placeholder="Detalle completo del fundamento de la resolucion..."
                                          minlength="20" maxlength="2000" required></textarea>
                                <p class="text-xs text-gray-500 mt-1">Minimo 20, maximo 2000 caracteres</p>
                            </div>

                            <!-- Botones de accion -->
                            <div class="space-y-3">
                                <button type="button" onclick="submitApprove()"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                    APROBAR - Cambiar a APTO
                                </button>
                                <button type="button" onclick="submitReject()"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                    RECHAZAR - Mantener NO APTO
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <!-- Ya fue resuelta -->
                <div class="bg-gray-50 shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-gray-600">Esta postulacion ya fue reevaluada</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if($canBeReviewed)
<script>
    function validateForm() {
        const summary = document.getElementById('resolution_summary').value.trim();
        const detail = document.getElementById('resolution_detail').value.trim();

        if (!summary) {
            alert('Debe ingresar un resumen de la resolucion');
            return false;
        }

        if (detail.length < 20) {
            alert('El detalle debe tener al menos 20 caracteres');
            return false;
        }

        return true;
    }

    function submitApprove() {
        if (!validateForm()) return;

        if (!confirm('¿Esta seguro de APROBAR el reclamo? El postulante pasara a estado APTO.')) return;

        const form = document.getElementById('resolution-form');
        const formData = new FormData(form);

        fetch('{{ route("admin.eligibility-override.approve", $application->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                resolution_type: formData.get('resolution_type'),
                resolution_summary: formData.get('resolution_summary'),
                resolution_detail: formData.get('resolution_detail'),
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrio un error al procesar la solicitud');
        });
    }

    function submitReject() {
        if (!validateForm()) return;

        if (!confirm('¿Esta seguro de RECHAZAR el reclamo? El postulante mantendra estado NO APTO.')) return;

        const form = document.getElementById('resolution-form');
        const formData = new FormData(form);

        fetch('{{ route("admin.eligibility-override.reject", $application->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                resolution_type: formData.get('resolution_type'),
                resolution_summary: formData.get('resolution_summary'),
                resolution_detail: formData.get('resolution_detail'),
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrio un error al procesar la solicitud');
        });
    }
</script>
@endif
@endsection
