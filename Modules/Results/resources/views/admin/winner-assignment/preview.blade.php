@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">
                <i class="fas fa-eye text-blue-600 mr-2"></i>
                Previsualizacion de Asignacion (Dry Run)
            </h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> -
                Esta es una simulacion, ningun dato ha sido modificado
            </p>
        </div>
        <a href="{{ route('admin.results.winner-assignment', $posting) }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-4">
            <div class="text-sm text-yellow-600">Ganadores Asignados</div>
            <div class="text-3xl font-bold text-yellow-600">{{ $preview['summary']['total_winners'] }}</div>
        </div>
        <div class="bg-orange-50 rounded-lg shadow-sm border border-orange-200 p-4">
            <div class="text-sm text-orange-600">Accesitarios Asignados</div>
            <div class="text-3xl font-bold text-orange-600">{{ $preview['summary']['total_accesitarios'] }}</div>
        </div>
        <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="text-sm text-gray-600">No Seleccionados</div>
            <div class="text-3xl font-bold text-gray-600">{{ $preview['summary']['total_not_selected'] }}</div>
        </div>
    </div>

    {{-- Asignacion por Puesto --}}
    <div class="space-y-6 mb-6">
        @forelse($preview['profiles'] as $profileData)
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            {{-- Header del Puesto --}}
            <div class="p-4 bg-blue-50 border-b border-blue-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">
                            {{ $profileData['position_code'] }}
                        </h3>
                        <p class="text-base text-blue-800 font-medium">
                            {{ $profileData['position_title'] }}
                        </p>
                        <p class="text-sm text-blue-700">
                            {{ $profileData['vacancies'] }} vacante(s) - Total postulantes: {{ $profileData['total_applicants'] }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ count($profileData['winners']) + count($profileData['accesitarios']) + count($profileData['not_selected']) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ganadores --}}
            @if(count($profileData['winners']) > 0)
            <div class="border-b">
                <div class="p-4 bg-yellow-50 border-b border-yellow-200">
                    <h4 class="font-semibold text-yellow-800">
                        <i class="fas fa-crown mr-2"></i>
                        GANADORES ({{ count($profileData['winners']) }})
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ranking</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje Final</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vacante</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($profileData['winners'] as $winner)
                            <tr class="hover:bg-yellow-50">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-200 text-yellow-900 font-bold text-sm">
                                        {{ $winner['ranking'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $winner['application']->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $winner['application']->code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $winner['application']->dni }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                                        {{ number_format($winner['final_score'], 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-yellow-700">
                                    #{{ $winner['vacancy_number'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-crown mr-1"></i> GANADOR
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Accesitarios --}}
            @if(count($profileData['accesitarios']) > 0)
            <div class="border-b">
                <div class="p-4 bg-orange-50 border-b border-orange-200">
                    <h4 class="font-semibold text-orange-800">
                        <i class="fas fa-star mr-2"></i>
                        ACCESITARIOS ({{ count($profileData['accesitarios']) }})
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ranking</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Orden</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje Final</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($profileData['accesitarios'] as $accesitario)
                            <tr class="hover:bg-orange-50">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-200 text-orange-900 font-bold text-sm">
                                        {{ $accesitario['ranking'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-orange-700">
                                    #{{ $accesitario['accesitario_order'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $accesitario['application']->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $accesitario['application']->code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $accesitario['application']->dni }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-800">
                                        {{ number_format($accesitario['final_score'], 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-star mr-1"></i> ACCESITARIO
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- No Seleccionados --}}
            @if(count($profileData['not_selected']) > 0)
            <div>
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h4 class="font-semibold text-gray-800">
                        <i class="fas fa-times-circle mr-2"></i>
                        NO SELECCIONADOS ({{ count($profileData['not_selected']) }})
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ranking</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Puntaje Final</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($profileData['not_selected'] as $notSelected)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-sm">
                                        {{ $notSelected['ranking'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $notSelected['application']->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $notSelected['application']->code }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $notSelected['application']->dni }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-800">
                                        {{ number_format($notSelected['final_score'], 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times mr-1"></i> NO SELECCIONADO
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800">No hay puestos con postulantes para asignar</p>
        </div>
        @endforelse
    </div>

    {{-- Boton de Ejecutar --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">Confirmar Asignacion</h3>
                <p class="text-sm text-gray-500">
                    Esta accion asignara:
                    <strong>{{ $preview['summary']['total_winners'] }} ganador(es)</strong>,
                    <strong>{{ $preview['summary']['total_accesitarios'] }} accesitario(s)</strong>,
                    y actualizara el estado de los no seleccionados.
                </p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.results.winner-assignment', $posting) }}"
                   class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <form action="{{ route('admin.results.winner-assignment.execute', $posting) }}" method="POST"
                      onsubmit="return confirm('Esta seguro de ejecutar la asignacion? Esta accion es definitiva y modificara los estados de todas las postulaciones.')">
                    @csrf
                    <input type="hidden" name="accesitarios_count" value="{{ $preview['accesitarios_count'] }}">
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>
                        Ejecutar Asignacion
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
