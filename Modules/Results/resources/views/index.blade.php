@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Procesamiento de Resultados CV</h2>
            <p class="text-gray-500 text-sm">Seleccione una convocatoria para procesar las evaluaciones curriculares</p>
        </div>
        <a href="{{ route('admin.results.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Resultados
        </a>
    </div>

    {{-- Información --}}
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
            <div>
                <p class="font-medium text-blue-800">¿Qué hace el procesamiento de resultados CV?</p>
                <p class="text-sm text-blue-700 mt-1">
                    Esta herramienta transfiere automáticamente los puntajes de las evaluaciones curriculares completadas (Fase 6)
                    a las postulaciones, actualiza los estados según el puntaje mínimo requerido (35 puntos), y registra todo en el historial.
                </p>
            </div>
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
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.results.cv-processing', $posting) }}"
                                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-calculator mr-2"></i>
                                    Procesar CV
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
@endsection
