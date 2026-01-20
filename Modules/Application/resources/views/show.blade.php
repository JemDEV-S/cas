@extends('layouts.app')

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
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Formación Académica</h3>
                @if($application->academics->count() > 0)
                    <span class="text-sm text-gray-500">{{ $application->academics->count() }} registro(s)</span>
                @endif
            </div>
        </div>
        <div class="px-6 py-4">
            @if($application->academics->count() > 0)
                <div class="space-y-4">
                    @foreach($application->academics as $academic)
                        <div class="border rounded-lg p-4 {{ $academic->is_related_career ? 'border-amber-300 bg-amber-50' : 'border-gray-200' }}">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $academic->degree_type === 'DOCTORADO' ? 'bg-purple-100 text-purple-800' :
                                           ($academic->degree_type === 'MAESTRIA' ? 'bg-indigo-100 text-indigo-800' :
                                           ($academic->degree_type === 'TITULO' ? 'bg-blue-100 text-blue-800' :
                                           ($academic->degree_type === 'BACHILLER' ? 'bg-green-100 text-green-800' :
                                           ($academic->degree_type === 'TECNICO' ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-800')))) }}">
                                        {{ $academic->degree_type_name }}
                                    </span>
                                    @if($academic->is_related_career)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                            Carrera Afín
                                        </span>
                                    @endif
                                    @if($academic->is_verified)
                                        <span class="text-green-600" title="Verificado">✓</span>
                                    @endif
                                </div>
                                <span class="text-sm text-gray-500">{{ $academic->issue_date->format('d/m/Y') }}</span>
                            </div>

                            <h4 class="font-semibold text-gray-900 mb-1">{{ $academic->degree_title }}</h4>
                            <p class="text-sm text-gray-600 mb-2">{{ $academic->institution_name }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                @if($academic->career)
                                    <div>
                                        <span class="text-gray-500">Carrera:</span>
                                        <span class="font-medium text-gray-800">{{ $academic->career->name }}</span>
                                        @if($academic->career->short_name)
                                            <span class="text-gray-400">({{ $academic->career->short_name }})</span>
                                        @endif
                                    </div>
                                    @if($academic->career->sunedu_category)
                                        <div>
                                            <span class="text-gray-500">Categoría SUNEDU:</span>
                                            <span class="text-gray-700">{{ $academic->career->sunedu_category }}</span>
                                        </div>
                                    @endif
                                    @if($academic->career->category_group)
                                        <div>
                                            <span class="text-gray-500">Grupo:</span>
                                            <span class="text-gray-700">{{ $academic->career->category_group }}</span>
                                        </div>
                                    @endif
                                    @if($academic->career->requires_colegiatura)
                                        <div>
                                            <span class="px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded">
                                                Requiere Colegiatura
                                            </span>
                                        </div>
                                    @endif
                                @elseif($academic->career_field)
                                    <div>
                                        <span class="text-gray-500">Campo/Especialidad:</span>
                                        <span class="text-gray-700">{{ $academic->career_field }}</span>
                                    </div>
                                @endif

                                @if($academic->is_related_career && $academic->related_career_name)
                                    <div class="col-span-2 mt-2 p-2 bg-amber-100 rounded">
                                        <span class="text-amber-800 text-sm">
                                            <strong>Carrera afín a:</strong> {{ $academic->related_career_name }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Mostrar equivalencias de la carrera si existen --}}
                            @if($academic->career && ($academic->career->equivalences->count() > 0 || $academic->career->equivalentTo->count() > 0))
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-500 mb-1">Carreras equivalentes:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($academic->career->equivalences as $equiv)
                                            <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">
                                                {{ $equiv->equivalentCareer->name ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                        @foreach($academic->career->equivalentTo as $equiv)
                                            <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded">
                                                {{ $equiv->career->name ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Mostrar sinónimos si existen --}}
                            @if($academic->career && $academic->career->synonyms->count() > 0)
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">También conocida como:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($academic->career->synonyms as $synonym)
                                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                                                {{ $synonym->synonym }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($academic->verification_notes)
                                <div class="mt-2 text-xs text-gray-500 italic">
                                    Nota: {{ $academic->verification_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No hay formación académica registrada.</p>
            @endif
        </div>
    </div>

    <!-- Conocimientos -->
    @if($application->knowledge->count() > 0)
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Conocimientos</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($application->knowledge as $item)
                        <div class="border rounded-lg p-3">
                            <p class="font-medium text-gray-800">{{ $item->knowledge_name }}</p>
                            <div class="mt-2 flex items-center">
                                <span class="text-sm text-gray-500 mr-2">Nivel:</span>
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $item->proficiency_level === 'AVANZADO' ? 'bg-green-100 text-green-800' :
                                       ($item->proficiency_level === 'INTERMEDIO' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $item->proficiency_level_name }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Registros Profesionales -->
    @if($application->professionalRegistrations->count() > 0)
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Registros Profesionales</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    @foreach($application->professionalRegistrations as $registration)
                        <div class="border rounded-lg p-4 {{ $registration->isValid() ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $registration->registration_type === 'COLEGIATURA' ? 'bg-blue-100 text-blue-800' :
                                               ($registration->registration_type === 'OSCE_CERTIFICATION' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $registration->registration_type_name }}
                                        </span>
                                        @if($registration->isValid())
                                            <span class="text-green-600 text-sm">✓ Vigente</span>
                                        @else
                                            <span class="text-red-600 text-sm">✗ Vencido</span>
                                        @endif
                                        @if($registration->is_verified)
                                            <span class="text-blue-600 text-xs">(Verificado)</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 font-medium text-gray-800">{{ $registration->issuing_entity }}</p>
                                    <p class="text-sm text-gray-600">N° {{ $registration->registration_number }}</p>
                                </div>
                                <div class="text-right text-sm">
                                    <p class="text-gray-500">Emisión: {{ $registration->issue_date->format('d/m/Y') }}</p>
                                    @if($registration->expiry_date)
                                        <p class="{{ $registration->isValid() ? 'text-gray-500' : 'text-red-600 font-semibold' }}">
                                            Vence: {{ $registration->expiry_date->format('d/m/Y') }}
                                        </p>
                                    @else
                                        <p class="text-gray-400">Sin vencimiento</p>
                                    @endif
                                </div>
                            </div>
                            @if($registration->verification_notes)
                                <p class="mt-2 text-xs text-gray-500 italic">{{ $registration->verification_notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

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
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Capacitaciones y Cursos</h3>
                    <span class="text-sm text-gray-500">{{ $application->trainings->count() }} capacitación(es)</span>
                </div>
            </div>
            <div class="px-6 py-4">
                <!-- Resumen de horas -->
                <div class="mb-4 p-4 bg-indigo-50 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-indigo-700">{{ $statistics['total_training_hours'] ?? 0 }}</p>
                            <p class="text-xs text-indigo-600">Horas Académicas Totales</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-indigo-700">{{ $application->trainings->count() }}</p>
                            <p class="text-xs text-indigo-600">Cursos/Capacitaciones</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-indigo-700">{{ $application->trainings->where('is_verified', true)->count() }}</p>
                            <p class="text-xs text-indigo-600">Verificados</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-indigo-700">{{ $application->trainings->where('is_verified', false)->count() }}</p>
                            <p class="text-xs text-indigo-600">Pendientes</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($application->trainings as $training)
                        <div class="border rounded-lg p-4 {{ $training->is_verified ? 'border-green-200' : 'border-gray-200' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <h4 class="font-medium text-gray-900">{{ $training->course_name }}</h4>
                                        @if($training->is_verified)
                                            <span class="text-green-600 text-sm" title="Verificado">✓</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600">{{ $training->institution }}</p>
                                    @if($training->start_date && $training->end_date)
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $training->start_date->format('d/m/Y') }} - {{ $training->end_date->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($training->academic_hours)
                                        <span class="px-3 py-1 text-sm font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                            {{ $training->academic_hours }} hrs
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">Sin horas</span>
                                    @endif
                                </div>
                            </div>
                            @if($training->verification_notes)
                                <p class="mt-2 text-xs text-gray-500 italic">{{ $training->verification_notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
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
