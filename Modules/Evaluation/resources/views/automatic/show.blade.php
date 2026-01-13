@extends('layouts.app')

@section('title', 'Evaluar Convocatoria')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Breadcrumb --}}
    <nav class="mb-6" aria-label="breadcrumb">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ route('evaluation.automatic.index') }}" class="hover:text-blue-600">
                    Evaluaciones Automáticas
                </a>
            </li>
            <li class="before:content-['/'] before:mx-2">
                <span class="text-gray-800 font-medium">{{ $jobPosting->code }}</span>
            </li>
        </ol>
    </nav>

    {{-- Encabezado --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ $jobPosting->title }}</h2>
            <p class="text-gray-600 text-sm">
                <code class="bg-gray-100 px-2 py-1 rounded">{{ $jobPosting->code }}</code>
                <span class="mx-2">•</span>
                <i class="fas fa-users mr-1"></i>
                {{ $jobPosting->jobProfiles->sum(fn($jp) => $jp->vacancies->count()) }} vacante(s)
            </p>
        </div>
        <div>
            @can('execute', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class)
                @if($stats['pending'] > 0)
                    <button type="button"
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            onclick="document.getElementById('executeModal').classList.remove('hidden')">
                        <i class="fas fa-play mr-2"></i>
                        Ejecutar Evaluación
                    </button>
                @else
                    <button type="button" class="px-6 py-3 bg-gray-300 text-gray-600 font-semibold rounded-lg cursor-not-allowed" disabled>
                        <i class="fas fa-check mr-2"></i>
                        Sin Pendientes
                    </button>
                @endif
            @endcan
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
            <i class="fas fa-file-alt text-3xl text-blue-600 mb-2"></i>
            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</h3>
            <p class="text-gray-600 text-sm">Total Postulaciones</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-yellow-200 p-6 text-center">
            <i class="fas fa-clock text-3xl text-yellow-600 mb-2"></i>
            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</h3>
            <p class="text-gray-600 text-sm">Pendientes</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-green-200 p-6 text-center">
            <i class="fas fa-check-circle text-3xl text-green-600 mb-2"></i>
            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['eligible'] }}</h3>
            <p class="text-gray-600 text-sm">APTOS</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6 text-center">
            <i class="fas fa-times-circle text-3xl text-red-600 mb-2"></i>
            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['not_eligible'] }}</h3>
            <p class="text-gray-600 text-sm">NO APTOS</p>
        </div>
    </div>

    @if($stats['last_evaluation'])
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <span>Última evaluación ejecutada: {{ $stats['last_evaluation']->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    @endif

    {{-- Tabla de Postulaciones por Perfil y Vacante --}}
    @foreach($jobPosting->jobProfiles as $jobProfile)
        @foreach($jobProfile->vacancies as $vacancy)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-briefcase mr-2"></i>
                        {{ $jobProfile->title }}
                        <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800">
                            {{ $vacancy->applications->count() }} postulaciones
                        </span>
                    </h5>
                </div>
                <div class="p-6">
                    @if($vacancy->applications->isEmpty())
                        <p class="text-center text-gray-500 py-8">No hay postulaciones para esta vacante.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Postulante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluación</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($vacancy->applications as $application)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $application->code }}</code>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $application->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $application->document_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($application->status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Presentada
                                                    </span>
                                                @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Elegible
                                                    </span>
                                                @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::NOT_ELIGIBLE)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        No Elegible
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($application->eligibility_checked_at)
                                                    <i class="fas fa-check-circle text-green-600" title="Evaluada"></i>
                                                @else
                                                    <i class="fas fa-clock text-yellow-600" title="Pendiente"></i>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                @if($application->eligibility_checked_at)
                                                    {{ $application->eligibility_checked_at->format('d/m/Y H:i') }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($application->eligibility_checked_at)
                                                    <a href="{{ route('evaluation.automatic.application', $application->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endforeach
</div>

{{-- Modal para ejecutar evaluación --}}
@can('execute', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class)
<div id="executeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h5 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-robot mr-2"></i>
                Ejecutar Evaluación Automática
            </h5>
            <button type="button"
                    class="text-gray-400 hover:text-gray-600"
                    onclick="document.getElementById('executeModal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('evaluation.automatic.execute', $jobPosting->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Se evaluarán <strong>{{ $stats['pending'] }} postulaciones pendientes</strong>.</span>
                    </div>
                </div>

                <p class="text-gray-700 mb-3">Esta acción ejecutará la evaluación automática que verificará:</p>
                <ul class="space-y-2 text-gray-700 mb-4">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Formación académica</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Experiencia general y específica</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Colegiatura profesional</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Certificaciones y cursos</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Conocimientos técnicos</span>
                    </li>
                </ul>

                @can('reexecute', \Modules\Evaluation\Policies\AutomaticEvaluationPolicy::class)
                    @if($stats['pending'] < $stats['total'])
                        <div class="flex items-start">
                            <input type="checkbox"
                                   name="force"
                                   id="forceEvaluate"
                                   value="1"
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="forceEvaluate" class="ml-2">
                                <span class="font-medium text-gray-900">Forzar reevaluación</span>
                                <span class="block text-sm text-gray-600">
                                    Reevaluar todas las postulaciones, incluso las ya evaluadas
                                </span>
                            </label>
                        </div>
                    @endif
                @endcan
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors"
                        onclick="document.getElementById('executeModal').classList.add('hidden')">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-play mr-2"></i>
                    Ejecutar Ahora
                </button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
