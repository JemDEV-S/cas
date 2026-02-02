@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                Reporte Detallado de Cálculo Final (Dry Run)
            </h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> -
                Organizado por Unidad Orgánica y Perfil
            </p>
            <div class="mt-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Vista Previa - Ningún dato ha sido modificado
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.results.final-calculation.preview', $posting) }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-list mr-2"></i> Vista Simple
            </a>
            <a href="{{ route('admin.results.final-calculation', $posting) }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    {{-- Información del Proceso --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-600 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="font-semibold text-gray-700">Convocatoria:</span>
                <span class="text-gray-900 ml-1">{{ $posting->code }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Fecha:</span>
                <span class="text-gray-900 ml-1">{{ now()->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Hora:</span>
                <span class="text-gray-900 ml-1">{{ now()->format('H:i') }} hrs.</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Puntaje Mínimo:</span>
                <span class="text-gray-900 ml-1">{{ $preview['summary']['min_score_required'] }} / 100+ pts</span>
            </div>
        </div>
    </div>

    {{-- Estadísticas Globales --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border-l-4 border-blue-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Total a Procesar</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $preview['summary']['will_approve_count'] + $preview['summary']['will_fail_count'] }}</p>
                </div>
                <i class="fas fa-users text-blue-200 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white border-l-4 border-green-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Aprobarán</p>
                    <p class="text-3xl font-bold text-green-600">{{ $preview['summary']['will_approve_count'] }}</p>
                    <p class="text-xs text-gray-500">≥ 70 puntos</p>
                </div>
                <i class="fas fa-check-circle text-green-200 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white border-l-4 border-red-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">No Aptos</p>
                    <p class="text-3xl font-bold text-red-600">{{ $preview['summary']['will_fail_count'] }}</p>
                    <p class="text-xs text-gray-500">< 70 puntos</p>
                </div>
                <i class="fas fa-times-circle text-red-200 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white border-l-4 border-yellow-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Incompletos</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $preview['summary']['incomplete_count'] }}</p>
                    <p class="text-xs text-gray-500">Sin entrevista</p>
                </div>
                <i class="fas fa-exclamation-triangle text-yellow-200 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white border-l-4 border-purple-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Mínimo Req.</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $preview['summary']['min_score_required'] }}</p>
                    <p class="text-xs text-gray-500">puntos finales</p>
                </div>
                <i class="fas fa-flag-checkered text-purple-200 text-3xl"></i>
            </div>
        </div>
    </div>

    {{-- Contenido por Unidades Organizacionales --}}
    @php
        // Organizar postulantes por unidad orgánica y perfil
        $organized = [];

        // Procesar postulantes aprobados
        foreach ($preview['will_approve'] as $item) {
            $app = $item['application'];
            $jobProfile = $app->jobProfile;
            if (!$jobProfile) continue;

            $unit = $jobProfile->requestingUnit ?? $jobProfile->organizationalUnit;
            $unitId = $unit?->id ?? 'sin_unidad';
            $unitName = $unit?->name ?? 'Sin Unidad Asignada';

            if (!isset($organized[$unitId])) {
                $organized[$unitId] = [
                    'id' => $unitId,
                    'name' => $unitName,
                    'code' => $unit?->code ?? 'N/A',
                    'profiles' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $profileId = $jobProfile->id;
            if (!isset($organized[$unitId]['profiles'][$profileId])) {
                $organized[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $jobProfile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $item['result_status'] = 'approved';
            $organized[$unitId]['profiles'][$profileId]['applications'][] = $item;
            $organized[$unitId]['stats']['total']++;
            $organized[$unitId]['stats']['approved']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['total']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['approved']++;
        }

        // Procesar postulantes desaprobados
        foreach ($preview['will_fail'] as $item) {
            $app = $item['application'];
            $jobProfile = $app->jobProfile;
            if (!$jobProfile) continue;

            $unit = $jobProfile->requestingUnit ?? $jobProfile->organizationalUnit;
            $unitId = $unit?->id ?? 'sin_unidad';
            $unitName = $unit?->name ?? 'Sin Unidad Asignada';

            if (!isset($organized[$unitId])) {
                $organized[$unitId] = [
                    'id' => $unitId,
                    'name' => $unitName,
                    'code' => $unit?->code ?? 'N/A',
                    'profiles' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $profileId = $jobProfile->id;
            if (!isset($organized[$unitId]['profiles'][$profileId])) {
                $organized[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $jobProfile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $item['result_status'] = 'failed';
            $organized[$unitId]['profiles'][$profileId]['applications'][] = $item;
            $organized[$unitId]['stats']['total']++;
            $organized[$unitId]['stats']['failed']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['total']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['failed']++;
        }

        // Procesar postulantes incompletos
        foreach ($preview['incomplete'] as $item) {
            $app = $item['application'];
            $jobProfile = $app->jobProfile;
            if (!$jobProfile) continue;

            $unit = $jobProfile->requestingUnit ?? $jobProfile->organizationalUnit;
            $unitId = $unit?->id ?? 'sin_unidad';
            $unitName = $unit?->name ?? 'Sin Unidad Asignada';

            if (!isset($organized[$unitId])) {
                $organized[$unitId] = [
                    'id' => $unitId,
                    'name' => $unitName,
                    'code' => $unit?->code ?? 'N/A',
                    'profiles' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $profileId = $jobProfile->id;
            if (!isset($organized[$unitId]['profiles'][$profileId])) {
                $organized[$unitId]['profiles'][$profileId] = [
                    'id' => $profileId,
                    'code' => $jobProfile->code,
                    'title' => $jobProfile->title,
                    'position_code' => $jobProfile->positionCode?->code ?? 'N/A',
                    'position_name' => $jobProfile->positionCode?->name ?? $jobProfile->profile_name,
                    'vacancies' => $jobProfile->total_vacancies ?? 1,
                    'applications' => [],
                    'stats' => ['total' => 0, 'approved' => 0, 'failed' => 0, 'incomplete' => 0],
                ];
            }

            $item['result_status'] = 'incomplete';
            $organized[$unitId]['profiles'][$profileId]['applications'][] = $item;
            $organized[$unitId]['stats']['total']++;
            $organized[$unitId]['stats']['incomplete']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['total']++;
            $organized[$unitId]['profiles'][$profileId]['stats']['incomplete']++;
        }

        // Ordenar postulaciones por puntaje final descendente dentro de cada perfil
        foreach ($organized as &$unit) {
            foreach ($unit['profiles'] as &$profile) {
                usort($profile['applications'], function($a, $b) {
                    $scoreA = $a['final_score'] ?? 0;
                    $scoreB = $b['final_score'] ?? 0;
                    return $scoreB <=> $scoreA;
                });
            }
            $unit['profiles'] = array_values($unit['profiles']);
        }

        $organized = array_values($organized);
    @endphp

    @foreach($organized as $unitIndex => $unit)
    {{-- Unidad Organizacional --}}
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-600">
        {{-- Header de Unidad --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-building mr-2"></i>
                        {{ $unit['name'] }}
                    </h3>
                    <p class="text-sm text-blue-200 mt-1">Código: {{ $unit['code'] }}</p>
                </div>
                <div class="text-sm text-right">
                    <div class="bg-white bg-opacity-20 px-3 py-1 rounded-lg inline-block">
                        <span class="font-semibold">Total:</span> {{ $unit['stats']['total'] }} |
                        <span class="text-green-300">✓ {{ $unit['stats']['approved'] }}</span> |
                        <span class="text-red-300">✗ {{ $unit['stats']['failed'] }}</span> |
                        <span class="text-yellow-300">⚠ {{ $unit['stats']['incomplete'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Perfiles dentro de la unidad --}}
        <div class="p-4 bg-gray-50">
            @foreach($unit['profiles'] as $profile)
            {{-- Perfil de Puesto --}}
            <div class="mb-4 bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                {{-- Header del Perfil --}}
                <div class="bg-gradient-to-r from-gray-100 to-gray-200 p-3 border-b border-gray-300">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="inline-block px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded">
                                {{ $profile['code'] }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800 ml-2">{{ $profile['title'] }}</span>
                        </div>
                        <div class="text-xs text-gray-600 flex gap-3">
                            <span><i class="fas fa-briefcase text-blue-500 mr-1"></i> {{ $profile['position_code'] }}</span>
                            <span><i class="fas fa-users text-green-500 mr-1"></i> Vacantes: {{ $profile['vacancies'] }}</span>
                            <span class="font-semibold">
                                Postulantes: {{ $profile['stats']['total'] }}
                                (<span class="text-green-600">{{ $profile['stats']['approved'] }}</span> /
                                <span class="text-red-600">{{ $profile['stats']['failed'] }}</span> /
                                <span class="text-yellow-600">{{ $profile['stats']['incomplete'] }}</span>)
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tabla de postulantes --}}
                <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="px-2 py-2 text-left" style="width: 2%;">N°</th>
                            <th class="px-2 py-2 text-left" style="width: 6%;">Código</th>
                            <th class="px-2 py-2 text-center" style="width: 5%;">DNI</th>
                            <th class="px-2 py-2 text-left" style="width: 15%;">Apellidos y Nombres</th>
                            <th class="px-2 py-2 text-center" style="width: 4%;" title="Puntaje CV">CV</th>
                            <th class="px-2 py-2 text-center" style="width: 4%;" title="Entrevista RAW">E.RAW</th>
                            <th class="px-2 py-2 text-center" style="width: 5%;" title="Bonus Edad + FF.AA.">B.Entr</th>
                            <th class="px-2 py-2 text-center" style="width: 4%;" title="Puntaje Base">Base</th>
                            <th class="px-2 py-2 text-center" style="width: 5%;" title="Exp. Sector Público">E.Pub</th>
                            <th class="px-2 py-2 text-center" style="width: 4%;" title="Subtotal">Subt</th>
                            <th class="px-2 py-2 text-center" style="width: 5%;" title="Bonificaciones Especiales">B.Esp</th>
                            <th class="px-2 py-2 text-center font-bold" style="width: 5%;" title="Puntaje Final">FINAL</th>
                            <th class="px-2 py-2 text-center" style="width: 6%;">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($profile['applications'] as $item)
                        <tr class="border-b hover:bg-gray-50 {{ $item['result_status'] == 'approved' ? 'bg-green-50' : ($item['result_status'] == 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
                            <td class="px-2 py-2 text-center text-gray-600">{{ $counter++ }}</td>
                            <td class="px-2 py-2 text-gray-700" style="font-size: 10px;">{{ $item['application']->code }}</td>
                            <td class="px-2 py-2 text-center text-gray-700">{{ $item['application']->dni }}</td>
                            <td class="px-2 py-2">
                                <div class="font-medium text-gray-900" style="font-size: 10px;">{{ strtoupper($item['application']->full_name) }}</div>
                            </td>

                            @if($item['result_status'] != 'incomplete')
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                        {{ number_format($item['curriculum_score'], 1) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-purple-100 text-purple-800">
                                        {{ number_format($item['interview_score_raw'], 1) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if(($item['age_bonus'] ?? 0) > 0 || ($item['military_bonus'] ?? 0) > 0)
                                        <div class="flex flex-col gap-1">
                                            @if(($item['age_bonus'] ?? 0) > 0)
                                                <span class="inline-block px-1 py-0.5 text-xs rounded bg-green-100 text-green-800" title="Bonus Joven">
                                                    J:+{{ number_format($item['age_bonus'], 1) }}
                                                </span>
                                            @endif
                                            @if(($item['military_bonus'] ?? 0) > 0)
                                                <span class="inline-block px-1 py-0.5 text-xs rounded bg-purple-100 text-purple-800" title="Bonus FF.AA.">
                                                    FF:+{{ number_format($item['military_bonus'], 1) }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded {{ ($item['base_score'] ?? 0) >= 70 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ number_format($item['base_score'] ?? 0, 1) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if(($item['public_sector_bonus'] ?? 0) > 0)
                                        <span class="inline-block px-1 py-0.5 text-xs rounded bg-blue-100 text-blue-800" title="{{ $item['public_sector_years'] ?? 0 }} años">
                                            +{{ number_format($item['public_sector_bonus'], 1) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                        {{ number_format($item['subtotal'] ?? 0, 1) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if(($item['special_bonus_total'] ?? 0) > 0)
                                        <div class="flex flex-col gap-1">
                                            @foreach(($item['special_bonuses']['details'] ?? []) as $detail)
                                                <span class="inline-block px-1 py-0.5 text-xs rounded {{ str_contains($detail['type'], 'Discapacidad') ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}" title="{{ $detail['law'] }}">
                                                    +{{ number_format($detail['amount'], 1) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded {{ $item['result_status'] == 'approved' ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900' }}">
                                        {{ number_format($item['final_score'] ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if($item['result_status'] == 'approved')
                                        <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">
                                            APROBADO
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-red-100 text-red-800">
                                            NO APTO
                                        </span>
                                    @endif
                                </td>
                            @else
                                {{-- Postulante incompleto --}}
                                <td colspan="8" class="px-2 py-2 text-center text-yellow-700 font-medium">
                                    {{ $item['reason'] ?? 'Sin puntaje de entrevista' }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span class="inline-block px-2 py-1 text-xs font-bold rounded bg-yellow-100 text-yellow-800">
                                        INCOMPLETO
                                    </span>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Fin tabla postulantes --}}
        </div>
        {{-- Fin perfil --}}
        @endforeach
        {{-- Fin todos los perfiles --}}
        </div>
        {{-- Fin contenedor de perfiles --}}
    </div>
    {{-- Fin unidad organizacional --}}
    @endforeach

    {{-- Nota informativa sobre bonificaciones --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-yellow-800">LEYENDA DE BONIFICACIONES:</h3>
                <ul class="mt-2 text-xs text-yellow-700 space-y-1">
                    <li><strong>J:</strong> Bonus Joven (10% sobre entrevista RAW) - Ley 31533 Art. 3.1</li>
                    <li><strong>FF:</strong> Bonus FF.AA. (10% sobre entrevista RAW) - RPE 61-2010-SERVIR/PE Art. 4</li>
                    <li><strong>E.Pub:</strong> Experiencia Sector Público (1 pt/año, máx 3 pts) - Ley 31533 Art. 3.2</li>
                    <li><strong>B.Esp:</strong> Bonificaciones Especiales (Discapacidad 15%, Deportistas 10-15%, etc.) sobre Subtotal</li>
                    <li><strong>Fórmula:</strong> FINAL = CV + (Entrevista RAW + Bonus J + Bonus FF) + Exp.Pub + Bonif.Especiales</li>
                    <li><strong>Puntaje Mínimo Aprobatorio:</strong> 70 puntos finales</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Botones de acción --}}
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">Confirmar Procesamiento de Cálculo Final</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Esta acción calculará y guardará los puntajes finales de
                    <strong>{{ $preview['summary']['will_approve_count'] + $preview['summary']['will_fail_count'] }}</strong> postulación(es).
                </p>
            </div>
            <div class="flex gap-4">
                <a href="{{ route('admin.results.final-calculation', $posting) }}"
                   class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
                <form action="{{ route('admin.results.final-calculation.execute', $posting) }}" method="POST"
                      onsubmit="return confirm('¿Está seguro de ejecutar el cálculo de puntaje final?\n\nEsta acción:\n- Calculará y guardará los puntajes finales\n- Actualizará el estado de los postulantes\n- Marcará como NO APTOS a quienes no alcancen 70 puntos\n\n¿Desea continuar?')">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-play mr-2"></i>
                        Ejecutar Cálculo Final
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
