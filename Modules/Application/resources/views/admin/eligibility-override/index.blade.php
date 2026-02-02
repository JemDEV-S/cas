@extends('layouts.app')

@section('title', 'Reevaluacion de Elegibilidad')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Reevaluacion de Elegibilidad</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Convocatoria: <span class="font-semibold">{{ $posting->code }}</span> - {{ $posting->title }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    @if($resolvedApplications->count() > 0)
                        <a href="{{ route('admin.eligibility-override.pdf', ['posting' => $posting->id, 'phase_id' => $phaseId, 'job_profile_id' => $jobProfileId]) }}"
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded inline-flex items-center"
                           target="_blank">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Descargar PDF {{ $phaseId ? '(Fase Filtrada)' : '' }}
                        </a>
                    @endif
                    <a href="{{ url()->previous() }}" class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador Rápido -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg sm:rounded-lg">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('admin.eligibility-override.search', $posting->id) }}" class="flex items-end gap-4">
                @csrf
                <div class="flex-1">
                    <label for="search" class="block text-sm font-semibold text-white mb-2">
                        🔍 Buscar Postulante para Revisar
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            id="search"
                            placeholder="Ingrese código, DNI o nombre del postulante..."
                            class="w-full px-4 py-3 pr-12 border-0 rounded-lg shadow-sm focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 text-gray-900 placeholder-gray-400"
                            required
                            minlength="3"
                            value="{{ old('search') }}"
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-indigo-100">
                        Busca por código, DNI o nombre. Funciona para postulantes APTOS y NO_APTOS.
                    </p>
                </div>
                <button
                    type="submit"
                    class="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg shadow-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
            </form>
        </div>
    </div>

    <!-- Estadisticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Total Resueltos</p>
            </div>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Reclamos Aprobados</p>
            </div>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</p>
                <p class="text-sm text-gray-600">Reclamos Rechazados</p>
            </div>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-amber-600">{{ $pendingApplications->count() }}</p>
                <p class="text-sm text-gray-600">Pendientes de Revision</p>
            </div>
        </div>
    </div>

    <!-- Filtros por perfil y fase -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.eligibility-override.index', $posting->id) }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Perfil</label>
                        <select name="job_profile_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los perfiles</option>
                            @foreach($posting->jobProfiles as $profile)
                                <option value="{{ $profile->id }}" {{ $jobProfileId == $profile->id ? 'selected' : '' }}>
                                    {{ $profile->code }} - {{ $profile->profile_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Fase de Evaluación</label>
                        <select name="phase_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas las fases</option>
                            @foreach($phases as $phase)
                                <option value="{{ $phase->id }}" {{ $phaseId == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        @if($phaseId)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Filtrando por: {{ $phases->firstWhere('id', $phaseId)?->name ?? 'Fase' }}
                            </span>
                        @endif
                        @if($jobProfileId)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-2">
                                Filtrando por: {{ $posting->jobProfiles->firstWhere('id', $jobProfileId)?->code ?? 'Perfil' }}
                            </span>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        @if($phaseId || $jobProfileId)
                            <a href="{{ route('admin.eligibility-override.index', $posting->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded">
                                Limpiar Filtros
                            </a>
                        @endif
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Postulaciones pendientes de revision -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
            <h3 class="text-lg font-semibold text-amber-800">
                Postulaciones Pendientes de Revision
                <span class="ml-2 px-2 py-1 text-sm bg-amber-200 text-amber-800 rounded-full">
                    {{ $pendingApplications->count() }}
                </span>
            </h3>
            <p class="text-sm text-amber-600 mt-1">Postulaciones NO APTO o en revision que pueden ser reevaluadas</p>
        </div>
        <div class="px-6 py-4">
            @if($pendingApplications->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perfil</th>
                            @if(!$phaseId)
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fase(s)</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingApplications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->code }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $application->dni }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ strtoupper($application->full_name) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $application->jobProfile->code ?? 'N/A' }}
                                </td>
                                @if(!$phaseId)
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if($application->evaluations && $application->evaluations->count() > 0)
                                        @foreach($application->evaluations->unique('phase_id')->take(2) as $evaluation)
                                            <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded mr-1 mb-1">
                                                {{ $evaluation->phase->name ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                        @if($application->evaluations->unique('phase_id')->count() > 2)
                                            <span class="text-xs text-gray-500">+{{ $application->evaluations->unique('phase_id')->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">Sin evaluaciones</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $application->status->value === 'NO_APTO' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $application->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-red-600 max-w-xs truncate" title="{{ $application->ineligibility_reason }}">
                                    {{ Str::limit($application->ineligibility_reason, 50) ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            // Verificar si tiene evaluación de CV completada (PHASE_06_CV_EVALUATION)
                                            $hasCVEvaluation = false;
                                            if ($application->evaluations && $application->evaluations->count() > 0) {
                                                $hasCVEvaluation = $application->evaluations->filter(function($eval) {
                                                    if (!$eval->phase) return false;
                                                    // Comparar con el valor del enum
                                                    $isSubmitted = in_array($eval->status->value, ['SUBMITTED', 'MODIFIED']);
                                                    $isCVPhase = $eval->phase->code === 'PHASE_06_CV_EVALUATION' ||
                                                                 str_contains(strtolower($eval->phase->name), 'evaluación curricular') ||
                                                                 str_contains(strtolower($eval->phase->name), 'evaluación de currículo');
                                                    return $isSubmitted && $isCVPhase;
                                                })->isNotEmpty();
                                            }
                                        @endphp

                                        <a href="{{ route('admin.eligibility-override.review-cv', $application->id) }}"
                                           class="inline-flex items-center px-3 py-1 {{ $hasCVEvaluation ? 'bg-blue-500 hover:bg-blue-600' : 'bg-green-500 hover:bg-green-600' }} text-white text-sm rounded"
                                           title="{{ $hasCVEvaluation ? 'Revisar CV' : 'Crear Evaluación de CV' }}">
                                            <svg class="w-4 h-4 {{ $hasCVEvaluation ? '' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($hasCVEvaluation)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                @endif
                                            </svg>
                                            @if(!$hasCVEvaluation)
                                                <span class="text-xs">Nuevo</span>
                                            @endif
                                        </a>

                                        <a href="{{ route('admin.eligibility-override.show', $application->id) }}"
                                           class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded">
                                            Revisar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="mt-2 text-gray-500">No hay postulaciones pendientes de revision</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Postulaciones ya resueltas -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">
                Reevaluaciones Resueltas
                <span class="ml-2 px-2 py-1 text-sm bg-gray-200 text-gray-800 rounded-full">
                    {{ $resolvedApplications->count() }}
                </span>
            </h3>
            <p class="text-sm text-gray-600 mt-1">Postulaciones que ya fueron reevaluadas</p>
        </div>
        <div class="px-6 py-4">
            @if($resolvedApplications->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                            @if(!$phaseId)
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fase(s)</th>
                            @endif
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Original</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Decision</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nuevo Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resuelto Por</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($resolvedApplications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $application->code }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ strtoupper($application->full_name) }}</td>
                                @if(!$phaseId)
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if($application->evaluations && $application->evaluations->count() > 0)
                                        @foreach($application->evaluations->unique('phase_id')->take(2) as $evaluation)
                                            <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded mr-1 mb-1">
                                                {{ $evaluation->phase->name ?? 'N/A' }}
                                            </span>
                                        @endforeach
                                        @if($application->evaluations->unique('phase_id')->count() > 2)
                                            <span class="text-xs text-gray-500">+{{ $application->evaluations->unique('phase_id')->count() - 2 }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">Sin evaluaciones</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $application->eligibilityOverride->original_status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $application->eligibilityOverride->isScoreClaim() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $application->eligibilityOverride->resolution_type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $application->eligibilityOverride->decision->value === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $application->eligibilityOverride->decision->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $application->eligibilityOverride->new_status === 'APTO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $application->eligibilityOverride->new_status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $application->eligibilityOverride->resolver->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $application->eligibilityOverride->resolved_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            // Verificar si tiene evaluación de CV completada (PHASE_06_CV_EVALUATION)
                                            $hasCVEvaluation = false;
                                            if ($application->evaluations && $application->evaluations->count() > 0) {
                                                $hasCVEvaluation = $application->evaluations->filter(function($eval) {
                                                    if (!$eval->phase) return false;
                                                    // Comparar con el valor del enum
                                                    $isSubmitted = in_array($eval->status->value, ['SUBMITTED', 'MODIFIED']);
                                                    $isCVPhase = $eval->phase->code === 'PHASE_06_CV_EVALUATION' ||
                                                                 str_contains(strtolower($eval->phase->name), 'evaluación curricular') ||
                                                                 str_contains(strtolower($eval->phase->name), 'evaluación de currículo');
                                                    return $isSubmitted && $isCVPhase;
                                                })->isNotEmpty();
                                            }
                                        @endphp

                                        <a href="{{ route('admin.eligibility-override.review-cv', $application->id) }}"
                                           class="inline-flex items-center px-3 py-1 {{ $hasCVEvaluation ? 'bg-blue-500 hover:bg-blue-600' : 'bg-green-500 hover:bg-green-600' }} text-white text-sm rounded"
                                           title="{{ $hasCVEvaluation ? 'Revisar CV' : 'Crear Evaluación de CV' }}">
                                            <svg class="w-4 h-4 {{ $hasCVEvaluation ? '' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($hasCVEvaluation)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                @endif
                                            </svg>
                                            @if(!$hasCVEvaluation)
                                                <span class="text-xs">Nuevo</span>
                                            @endif
                                        </a>

                                        <a href="{{ route('admin.eligibility-override.show', $application->id) }}"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Ver detalle
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500">No hay reevaluaciones resueltas</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
