@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Procesamiento de Resultados de Entrevista</h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> - Fase 8
            </p>
        </div>
        <a href="{{ route('admin.results.cv-processing.list') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tarjetas de Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Elegibles para Entrevista</div>
            <div class="text-3xl font-bold text-blue-600">{{ $summary['total_eligible_for_interview'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Evaluaciones Completadas</div>
            <div class="text-3xl font-bold text-green-600">{{ $summary['evaluations_submitted'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Evaluaciones Pendientes</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $summary['evaluations_pending'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Ya Procesados</div>
            <div class="text-3xl font-bold text-purple-600">{{ $summary['already_processed'] }}</div>
        </div>
    </div>

    {{-- Panel de Informacion --}}
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Reglas de Procesamiento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Puntaje maximo: <strong>50 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-yellow-500 mr-2"></i>
                    <span>Puntaje minimo para aprobar: <strong>35 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-gift text-blue-500 mr-2"></i>
                    <span>Bonus joven (&lt; 29 años): <strong>+10% sobre puntaje</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-user-times text-red-500 mr-2"></i>
                    <span>&lt; 35 puntos: Cambia a <strong>NO APTO</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Advertencias --}}
    @if($summary['evaluations_pending'] > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-yellow-800">Hay evaluaciones pendientes</p>
                    <p class="text-sm text-yellow-700">
                        {{ $summary['evaluations_pending'] }} entrevista(s) aun no han sido evaluadas completamente.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($summary['without_evaluation'] > 0)
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-times-circle text-red-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-red-800">Postulaciones sin entrevista asignada</p>
                    <p class="text-sm text-red-700">
                        {{ $summary['without_evaluation'] }} postulacion(es) no tienen entrevista asignada.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Acciones --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h3 class="text-lg font-semibold mb-4">Acciones</h3>

        <div class="flex flex-wrap gap-3">
            @if($summary['evaluations_submitted'] > 0)
                <form method="POST" action="{{ route('admin.results.interview-processing.preview', $posting) }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-eye mr-2"></i> Vista Previa
                    </button>
                </form>
            @else
                <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <i class="fas fa-eye mr-2"></i> Vista Previa (Sin datos)
                </button>
            @endif

            @if($summary['already_processed'] > 0)
                <a href="{{ route('admin.results.interview-processing.download-pdf', $posting) }}"
                   class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 inline-flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Descargar PDF de Resultados
                </a>
            @else
                <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <i class="fas fa-file-pdf mr-2"></i> Descargar PDF (Sin datos procesados)
                </button>
            @endif
        </div>

        @if($summary['evaluations_submitted'] == 0)
            <p class="text-sm text-gray-500 mt-3">Complete las evaluaciones de entrevista para continuar</p>
        @endif
    </div>
</div>
@endsection
