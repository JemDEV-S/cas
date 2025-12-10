@extends('application::layouts.master')

@section('title', 'Detalle de Postulación')

@section('content')
<div class="space-y-6">
    <!-- Header con acciones -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $application->code }}</h2>
                    <p class="mt-1 text-sm text-gray-600">Postulación de {{ $application->full_name }}</p>
                </div>
                <div class="flex space-x-2">
                    @can('update', $application)
                        <a href="{{ route('application.edit', $application->id) }}"
                           class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Editar
                        </a>
                    @endcan
                    @can('evaluate', $application)
                        <form method="POST" action="{{ route('application.evaluate-eligibility', $application->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                Evaluar Elegibilidad
                            </button>
                        </form>
                    @endcan
                    <a href="{{ route('application.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Datos Personales -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Datos Personales</h3>
        </div>
        <div class="px-6 py-4 grid grid-cols-2 gap-4">
            <div><strong>Nombre Completo:</strong> {{ $application->full_name }}</div>
            <div><strong>DNI:</strong> {{ $application->dni }}</div>
            <div><strong>Fecha de Nacimiento:</strong> {{ $application->birth_date->format('d/m/Y') }}</div>
            <div><strong>Email:</strong> {{ $application->email }}</div>
            <div><strong>Teléfono Móvil:</strong> {{ $application->mobile_phone }}</div>
            <div><strong>Teléfono Fijo:</strong> {{ $application->phone ?? 'N/A' }}</div>
            <div class="col-span-2"><strong>Dirección:</strong> {{ $application->address }}</div>
        </div>
    </div>

    <!-- Estado y Elegibilidad -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Estado de la Postulación</h3>
        </div>
        <div class="px-6 py-4 grid grid-cols-2 gap-4">
            <div>
                <strong>Estado:</strong>
                <span class="ml-2 px-3 py-1 text-sm font-semibold rounded-full
                    {{ $application->status === 'APTO' ? 'bg-green-100 text-green-800' :
                       ($application->status === 'NO_APTO' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ $application->status }}
                </span>
            </div>
            <div>
                <strong>Elegible:</strong>
                @if($application->is_eligible === true)
                    <span class="ml-2 text-green-600 font-semibold">✓ SÍ</span>
                @elseif($application->is_eligible === false)
                    <span class="ml-2 text-red-600 font-semibold">✗ NO</span>
                @else
                    <span class="ml-2 text-gray-400">Pendiente de evaluación</span>
                @endif
            </div>
            @if($application->ineligibility_reason)
                <div class="col-span-2 bg-red-50 p-3 rounded">
                    <strong class="text-red-800">Razón de No Elegibilidad:</strong>
                    <p class="text-red-600 mt-1">{{ $application->ineligibility_reason }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Formación Académica -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Formación Académica</h3>
        </div>
        <div class="px-6 py-4">
            @if($application->academics->count() > 0)
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Institución</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Título</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($application->academics as $academic)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $academic->institution_name }}</td>
                                <td class="px-4 py-2 text-sm">{{ $academic->degree_type }}</td>
                                <td class="px-4 py-2 text-sm">{{ $academic->degree_title }}</td>
                                <td class="px-4 py-2 text-sm">{{ $academic->issue_date->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No hay formación académica registrada.</p>
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
                <div class="mb-4 p-4 bg-blue-50 rounded">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <strong>Experiencia General:</strong>
                            <span class="text-blue-700 font-semibold">{{ $experienceData['general']['formatted'] }}</span>
                        </div>
                        <div>
                            <strong>Experiencia Específica:</strong>
                            <span class="text-green-700 font-semibold">{{ $experienceData['specific']['formatted'] }}</span>
                        </div>
                        <div>
                            <strong>Sector Público:</strong>
                            <span class="text-purple-700 font-semibold">{{ $experienceData['public_sector']['formatted'] }}</span>
                        </div>
                    </div>
                </div>

                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Organización</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Cargo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Periodo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Duración</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($application->experiences as $experience)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $experience->organization }}</td>
                                <td class="px-4 py-2 text-sm">{{ $experience->position }}</td>
                                <td class="px-4 py-2 text-sm">
                                    {{ $experience->start_date->format('d/m/Y') }} - {{ $experience->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $experience->formatted_duration }}</td>
                                <td class="px-4 py-2 text-sm">
                                    @if($experience->is_specific)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Específica</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">General</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No hay experiencia laboral registrada.</p>
            @endif
        </div>
    </div>

    <!-- Capacitaciones -->
    @if($application->trainings->count() > 0)
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Capacitaciones y Cursos</h3>
            </div>
            <div class="px-6 py-4">
                <p class="mb-2 text-sm text-gray-600">
                    <strong>Total de horas:</strong> {{ $statistics['total_training_hours'] }} horas académicas
                </p>
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Institución</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Curso</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Horas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($application->trainings as $training)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $training->institution }}</td>
                                <td class="px-4 py-2 text-sm">{{ $training->course_name }}</td>
                                <td class="px-4 py-2 text-sm">{{ $training->academic_hours ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Condiciones Especiales -->
    @if($application->specialConditions->count() > 0)
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Condiciones Especiales</h3>
            </div>
            <div class="px-6 py-4">
                @foreach($application->specialConditions as $condition)
                    <div class="mb-2 p-3 bg-yellow-50 rounded">
                        <strong>{{ $condition->condition_type_name }}</strong>
                        <span class="ml-2 text-sm text-gray-600">
                            (Bonificación: {{ $condition->bonus_percentage }}%)
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Acciones adicionales -->
    <div class="flex justify-end space-x-2">
        <a href="{{ route('application.history', $application->id) }}"
           class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
            Ver Historial
        </a>
    </div>
</div>
@endsection
