@extends('layouts.app')

@section('title', 'Evaluaciones Automáticas')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Encabezado --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-1">
                <i class="fas fa-robot mr-2"></i>
                Evaluaciones Automáticas
            </h2>
            <p class="text-gray-600 text-sm">
                Ejecutar evaluación automática de elegibilidad (Fase 4) para las postulaciones
            </p>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    {{-- Tarjetas de Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-blue-200 rounded-lg shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i class="fas fa-briefcase text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h6 class="text-gray-500 text-sm font-medium mb-1">Convocatorias</h6>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_job_postings'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-yellow-200 rounded-lg shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h6 class="text-gray-500 text-sm font-medium mb-1">Pendientes</h6>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-green-200 rounded-lg shadow-sm">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 rounded-full p-3">
                            <i class="fas fa-check-double text-green-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h6 class="text-gray-500 text-sm font-medium mb-1">Evaluadas</h6>
                        <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_evaluated'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Convocatorias --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list mr-2"></i>
                Convocatorias Disponibles
            </h5>
        </div>
        <div class="p-6">
            @if($jobPostings->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No hay convocatorias con postulaciones pendientes de evaluar.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Postulaciones</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pendientes</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluadas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jobPostings as $posting)
                                @php
                                    $allApps = $posting->jobProfiles->flatMap(function($jp) {
                                        return $jp->vacancies->flatMap->applications;
                                    });
                                    $pending = $allApps->where('status', \Modules\Application\Enums\ApplicationStatus::SUBMITTED)->whereNull('eligibility_checked_at')->count();
                                    $evaluated = $allApps->whereNotNull('eligibility_checked_at')->count();
                                    $total = $allApps->count();
                                    $totalVacancies = $posting->jobProfiles->sum(fn($jp) => $jp->vacancies->count());
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $posting->code }}</code>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $posting->title }}</div>
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-users mr-1"></i>
                                                {{ $totalVacancies }} vacante(s)
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $total }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($pending > 0)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $pending }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($evaluated > 0)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $evaluated }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('evaluation.automatic.show', $posting->id) }}"
                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Información de ayuda --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg mt-6">
        <div class="p-6">
            <h6 class="text-blue-800 font-semibold mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                ¿Cómo funciona la evaluación automática?
            </h6>
            <p class="text-gray-700 mb-3">
                La evaluación automática verifica que los postulantes cumplan con los requisitos mínimos del perfil:
            </p>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Formación Académica:</strong> Nivel educativo requerido</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Experiencia General:</strong> Años de experiencia laboral total</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Experiencia Específica:</strong> Años de experiencia en el área</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Colegiatura:</strong> Registro profesional requerido</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span><strong>Certificaciones:</strong> OSCE, licencias, cursos y conocimientos técnicos</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
