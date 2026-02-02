@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">
                <i class="fas fa-eye text-blue-600 mr-2"></i>
                Previsualizacion de Calculo Final (Dry Run)
            </h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> -
                Esta es una simulacion, ningun dato ha sido modificado
            </p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.results.final-calculation.preview-detailed', $posting) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-file-alt mr-2"></i> Ver Reporte Detallado
                </button>
            </form>
            <a href="{{ route('admin.results.final-calculation', $posting) }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Total a Procesar</div>
            <div class="text-3xl font-bold text-blue-600">{{ $preview['summary']['will_approve_count'] + $preview['summary']['will_fail_count'] }}</div>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-4">
            <div class="text-sm text-green-600">Aprobaran (>= 70)</div>
            <div class="text-3xl font-bold text-green-600">{{ $preview['summary']['will_approve_count'] }}</div>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-4">
            <div class="text-sm text-red-600">Desaprobaran (< 70)</div>
            <div class="text-3xl font-bold text-red-600">{{ $preview['summary']['will_fail_count'] }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-4">
            <div class="text-sm text-yellow-600">Incompletos</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $preview['summary']['incomplete_count'] }}</div>
        </div>
    </div>

    {{-- Postulantes que APROBARAN --}}
    @if(count($preview['will_approve']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-green-50 border-b border-green-200">
            <h3 class="text-lg font-semibold text-green-800">
                <i class="fas fa-check-circle mr-2"></i>
                Aprobaran ({{ count($preview['will_approve']) }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">CV (40%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Entrevista (40%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bonus Edad</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Sector Publico</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bonificaciones</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase font-bold">Puntaje Final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($preview['will_approve'] as $index => $item)
                    <tr class="hover:bg-green-50">
                        <td class="px-4 py-3 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $item['application']->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item['application']->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['application']->dni }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ number_format($item['curriculum_score'], 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                {{ number_format($item['interview_score_raw'], 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($item['age_bonus'] > 0)
                                <span class="text-green-600 font-medium">+{{ number_format($item['age_bonus'], 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($item['public_sector_bonus'] > 0)
                                <span class="text-green-600 font-medium">+{{ number_format($item['public_sector_bonus'], 2) }}</span>
                                <div class="text-xs text-gray-500">{{ $item['public_sector_years'] }} años</div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($item['special_bonus_total'] > 0)
                                <span class="text-green-600 font-medium">+{{ number_format($item['special_bonus_total'], 2) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                {{ number_format($item['final_score'], 2) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Postulantes que DESAPROBARAN --}}
    @if(count($preview['will_fail']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-red-50 border-b border-red-200">
            <h3 class="text-lg font-semibold text-red-800">
                <i class="fas fa-times-circle mr-2"></i>
                Desaprobaran ({{ count($preview['will_fail']) }})
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">CV</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Entrevista</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje Bruto</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase font-bold">Puntaje Final</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($preview['will_fail'] as $index => $item)
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-3 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $item['application']->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item['application']->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $item['application']->dni }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ number_format($item['curriculum_score'], 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                {{ number_format($item['interview_score_raw'], 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-600">
                            {{ number_format($item['base_score'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                {{ number_format($item['final_score'], 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-red-600">
                            {{ $item['reason'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Incompletos --}}
    @if(count($preview['incomplete']) > 0)
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 bg-yellow-50 border-b border-yellow-200">
            <h3 class="text-lg font-semibold text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Registros Incompletos ({{ count($preview['incomplete']) }}) - Seran omitidos
            </h3>
        </div>
        <div class="p-4">
            <ul class="space-y-2">
                @foreach($preview['incomplete'] as $item)
                <li class="flex items-center text-sm">
                    <i class="fas fa-minus-circle text-yellow-500 mr-2"></i>
                    <span class="font-medium">{{ $item['application']->full_name }}</span>
                    <span class="text-gray-500 ml-2">({{ $item['application']->dni }})</span>
                    <span class="text-yellow-600 ml-2">- {{ $item['reason'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Boton de Ejecutar --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">Confirmar Calculo</h3>
                <p class="text-sm text-gray-500">
                    Esta accion calculara los puntajes finales de {{ $preview['summary']['will_approve_count'] + $preview['summary']['will_fail_count'] }} postulacion(es).
                </p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.results.final-calculation', $posting) }}"
                   class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <form action="{{ route('admin.results.final-calculation.execute', $posting) }}" method="POST"
                      onsubmit="return confirm('Esta seguro de ejecutar el calculo? Esta accion modificara los datos de las postulaciones.')">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>
                        Ejecutar Calculo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
