@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Calculo de Puntaje Final</h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> - Fase 9
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
            <div class="text-sm text-gray-500">Elegibles para Calculo</div>
            <div class="text-3xl font-bold text-blue-600">{{ $summary['total_eligible'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Listos para Calculo</div>
            <div class="text-3xl font-bold text-green-600">{{ $summary['ready_for_calculation'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Falta Entrevista</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $summary['missing_interview'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Ya Calculados</div>
            <div class="text-3xl font-bold text-purple-600">{{ $summary['already_calculated'] }}</div>
        </div>
    </div>

    {{-- Panel de Informacion --}}
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Reglas de Calculo Final</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Puntaje minimo final: <strong>70 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                    <span>CV: <strong>40% del puntaje final</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-users text-purple-500 mr-2"></i>
                    <span>Entrevista: <strong>40% del puntaje final</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                    <span>Bonificaciones: <strong>hasta +20% adicional</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-gift text-blue-500 mr-2"></i>
                    <span>Bonus joven (&lt; 29 años): <strong>10% sobre entrevista</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-briefcase text-green-500 mr-2"></i>
                    <span>Experiencia sector publico: <strong>hasta 10 puntos</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Advertencias --}}
    @if($summary['missing_interview'] > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-yellow-800">Hay postulaciones sin entrevista completada</p>
                    <p class="text-sm text-yellow-700">
                        {{ $summary['missing_interview'] }} postulacion(es) no tienen puntaje de entrevista.
                        Complete las entrevistas antes de calcular el puntaje final.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Acciones --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Acciones</h3>

            <div class="flex gap-4 flex-wrap">
                @if($summary['ready_for_calculation'] > 0)
                    <form action="{{ route('admin.results.final-calculation.preview', $posting) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Previsualizar Resultados (Dry Run)
                        </button>
                    </form>
                @else
                    <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                        <i class="fas fa-search mr-2"></i>
                        Previsualizar Resultados
                    </button>
                    <span class="text-sm text-gray-500 self-center">
                        Complete las entrevistas para procesar
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
