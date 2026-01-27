@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Procesamiento de Resultados</h2>
            <p class="text-gray-500 text-sm">Seleccione una convocatoria y el proceso que desea ejecutar</p>
        </div>
        <a href="{{ route('admin.results.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Resultados
        </a>
    </div>

    {{-- Información sobre los procesos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Procesar CV -->
        <div class="bg-white p-5 rounded-lg shadow border hover:shadow-lg transition-shadow">
            <div class="text-blue-600 text-3xl mb-3"><i class="fas fa-file-alt"></i></div>
            <h3 class="font-semibold text-lg mb-2">Procesar CV</h3>
            <p class="text-sm text-gray-600 mb-3">Fase 6 - Evaluación Curricular. Puntaje mínimo: 35 puntos.</p>
        </div>

        <!-- Procesar Entrevistas -->
        <div class="bg-white p-5 rounded-lg shadow border hover:shadow-lg transition-shadow">
            <div class="text-purple-600 text-3xl mb-3"><i class="fas fa-users"></i></div>
            <h3 class="font-semibold text-lg mb-2">Procesar Entrevistas</h3>
            <p class="text-sm text-gray-600 mb-3">Fase 8 - Entrevista Personal. Incluye bonus joven (+10%).</p>
        </div>

        <!-- Calcular Puntaje Final -->
        <div class="bg-white p-5 rounded-lg shadow border hover:shadow-lg transition-shadow">
            <div class="text-green-600 text-3xl mb-3"><i class="fas fa-calculator"></i></div>
            <h3 class="font-semibold text-lg mb-2">Calcular Puntaje Final</h3>
            <p class="text-sm text-gray-600 mb-3">Bonificaciones y Total. Puntaje mínimo final: 70 puntos.</p>
        </div>

        <!-- Asignar Ganadores -->
        <div class="bg-white p-5 rounded-lg shadow border hover:shadow-lg transition-shadow">
            <div class="text-yellow-600 text-3xl mb-3"><i class="fas fa-trophy"></i></div>
            <h3 class="font-semibold text-lg mb-2">Asignar Ganadores</h3>
            <p class="text-sm text-gray-600 mb-3">Ganadores y Accesitarios según ranking.</p>
        </div>
    </div>

    {{-- Lista de Convocatorias --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-t-lg">
            <h5 class="text-lg font-semibold">
                <i class="fas fa-briefcase"></i> Convocatorias Disponibles
            </h5>
        </div>

        @if($postings->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
                <p class="text-gray-500 text-lg mb-2">No hay convocatorias disponibles</p>
                <p class="text-gray-400 text-sm">Las convocatorias deben estar en estado ACTIVA, EN_EVALUACION o FINALIZADA</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puestos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($postings as $posting)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $posting->code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ Str::limit($posting->title ?? 'Sin título', 50) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($posting->status === 'ACTIVA') bg-green-100 text-green-800
                                    @elseif($posting->status === 'EN_EVALUACION') bg-blue-100 text-blue-800
                                    @elseif($posting->status === 'FINALIZADA') bg-gray-100 text-gray-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $posting->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $posting->jobProfiles_count ?? $posting->jobProfiles->count() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $posting->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.results.cv-processing', $posting) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors"
                                       title="Procesar evaluaciones curriculares">
                                        <i class="fas fa-file-alt mr-1"></i> CV
                                    </a>
                                    <a href="{{ route('admin.results.interview-processing', $posting) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded hover:bg-purple-700 transition-colors"
                                       title="Procesar entrevistas">
                                        <i class="fas fa-users mr-1"></i> Entrevista
                                    </a>
                                    <a href="{{ route('admin.results.final-calculation', $posting) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors"
                                       title="Calcular puntaje final">
                                        <i class="fas fa-calculator mr-1"></i> Final
                                    </a>
                                    <a href="{{ route('admin.results.winner-assignment', $posting) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded hover:bg-yellow-700 transition-colors"
                                       title="Asignar ganadores">
                                        <i class="fas fa-trophy mr-1"></i> Ganadores
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
