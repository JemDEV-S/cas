@extends('layouts.app')

@section('title', 'Evaluación de CV - ' . ($application->full_name ?? 'Postulante'))

@section('page-title')
    Evaluación de CV - {{ $application->full_name ?? 'Postulante' }}
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.eligibility-override.index', $application->jobProfile->job_posting_id) }}">Reevaluación de Elegibilidad</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.eligibility-override.show', $application->id) }}">{{ $application->code }}</a></li>
    <li class="breadcrumb-item active">Evaluación de CV</li>
@endsection

@section('content')
<div class="flex h-screen bg-gray-50">
    <!-- Panel Izquierdo: CV -->
    <div class="w-1/2 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header del CV -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center justify-between">
                <div class="text-white">
                    <h3 class="text-lg font-semibold">Curriculum Vitae</h3>
                    <p class="text-sm text-blue-100 mt-1">
                        {{ $application->full_name ?? 'N/A' }}
                    </p>
                </div>
                @if($cvDocument)
                <a href="{{ route('application.documents.download', $cvDocument->id) }}"
                   class="px-4 py-2 bg-white text-blue-700 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar CV
                </a>
                @endif
            </div>
        </div>

        <!-- Visor de PDF -->
        <div class="flex-1 overflow-hidden bg-gray-100">
            @if($cvDocument && $cvDocument->fileExists())
                <iframe
                    src="{{ route('admin.eligibility-override.view-cv', $application->id) }}"
                    class="w-full h-full border-0"
                    title="Curriculum Vitae">
                </iframe>
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg font-medium">CV no disponible</p>
                        <p class="text-sm mt-1">El postulante no ha cargado su CV</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel Derecho: Detalles de la Evaluación -->
    <div class="w-1/2 flex flex-col bg-gray-50">
        <!-- Header del formulario -->
        <div class="px-6 py-4 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Evaluación de Currículum (Completada)</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $jobProfile->jobPosting->title ?? 'Convocatoria' }} - {{ $cvEvaluation->phase->name ?? 'Fase 04' }}
                    </p>
                </div>

                <!-- Puntaje final -->
                <div class="text-right">
                    @php
                        $isDisqualified = isset($cvEvaluation->metadata['disqualified']) && $cvEvaluation->metadata['disqualified'] === true;
                    @endphp

                    @if($isDisqualified)
                        <div class="text-2xl font-bold text-red-600">DESCALIFICADO</div>
                        <p class="text-xs text-red-500 mt-1">Evaluación con descalificación</p>
                    @else
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($cvEvaluation->total_score, 2) }}</div>
                        <p class="text-xs text-gray-500">de {{ number_format($maxTotalScore, 2) }} pts</p>
                        <div class="mt-1 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                            @php
                                $progress = $maxTotalScore > 0 ? ($cvEvaluation->total_score / $maxTotalScore) * 100 : 0;
                            @endphp
                            <div class="h-full bg-blue-600" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($progress, 1) }}%</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contenido con scroll -->
        <div class="flex-1 overflow-y-auto px-6 py-6">

            <!-- INFORMACIÓN DEL PERFIL Y POSTULACIÓN -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 shadow-md mb-6 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 border-b border-blue-300">
                    <h4 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Información de la Postulación
                    </h4>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Postulación</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->code ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Postulante</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $application->full_name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @if($positionCode)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Código de Puesto</p>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800">
                                {{ $positionCode }}
                                @if($jobProfile && $jobProfile->positionCode && $jobProfile->positionCode->name)
                                <span class="ml-2 text-blue-600">- {{ $jobProfile->positionCode->name }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Título del Perfil</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $jobProfile->title ?? 'N/A' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Evaluador</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $cvEvaluation->evaluator->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de Evaluación</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $cvEvaluation->submitted_at ? $cvEvaluation->submitted_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MENSAJE SI ESTÁ DESCALIFICADO -->
            @if($isDisqualified)
            <div class="bg-red-100 border-2 border-red-500 rounded-xl p-6 text-center mb-6">
                <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <h3 class="text-xl font-bold text-red-900 mb-2">Postulante Descalificado</h3>
                @if($cvEvaluation->general_comments)
                <p class="text-sm text-red-800 font-medium">{{ $cvEvaluation->general_comments }}</p>
                @endif
            </div>
            @endif

            <!-- CRITERIOS DE EVALUACIÓN (Solo lectura) -->
            @if(!$isDisqualified && $criteria->count() > 0)
            <div class="space-y-4">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg px-5 py-3 shadow-md">
                    <h4 class="text-base font-bold text-white">Criterios de Evaluación</h4>
                    <p class="text-sm text-blue-100 mt-1">Resultados de la evaluación realizada</p>
                </div>

                @foreach($criteria as $criterion)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <!-- Header del criterio -->
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-gray-900">
                                    {{ $criterion->order }}. {{ $criterion->name }}
                                </h4>
                                @if($criterion->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $criterion->description }}</p>
                                @endif
                            </div>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                Peso: {{ $criterion->weight }}x
                            </span>
                        </div>
                    </div>

                    <!-- Cuerpo del criterio -->
                    <div class="px-5 py-4 space-y-4">
                        <!-- Puntaje obtenido -->
                        <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Puntaje obtenido:</span>
                            <div>
                                <span class="text-xl font-bold text-green-600">{{ number_format($details[$criterion->id]->score ?? 0, 2) }}</span>
                                <span class="text-sm text-gray-600"> / {{ number_format($criterion->max_score, 2) }} pts</span>
                            </div>
                        </div>

                        <!-- Comentarios -->
                        @if(isset($details[$criterion->id]) && $details[$criterion->id]->comments)
                        <div>
                            <h5 class="text-sm font-semibold text-gray-700 mb-2">Comentarios del Evaluador:</h5>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-800">{{ $details[$criterion->id]->comments }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Evidencia -->
                        @if(isset($details[$criterion->id]) && $details[$criterion->id]->evidence)
                        <div>
                            <h5 class="text-sm font-semibold text-gray-700 mb-2">Evidencia:</h5>
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-800">{{ $details[$criterion->id]->evidence }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Puntaje ponderado -->
                        <div class="flex items-center justify-end pt-2 border-t border-gray-100">
                            <span class="text-sm text-gray-600">Puntaje ponderado: </span>
                            <span class="ml-2 text-base font-semibold text-gray-900">
                                {{ number_format(($details[$criterion->id]->score ?? 0) * $criterion->weight, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Comentarios Generales -->
            @if($cvEvaluation->general_comments && !$isDisqualified)
            <div class="mt-6 bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h4 class="text-base font-semibold text-gray-900">Comentarios Generales</h4>
                </div>
                <div class="px-5 py-4">
                    <p class="text-sm text-gray-800">{{ $cvEvaluation->general_comments }}</p>
                </div>
            </div>
            @endif

        </div>

        <!-- Footer con botones de acción -->
        <div class="px-6 py-4 bg-white border-t border-gray-200 shadow-lg">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('admin.eligibility-override.show', $application->id) }}"
                   class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                    Volver al Detalle
                </a>

                <div class="flex items-center gap-3">
                    {{-- Solo mostrar botón de modificar si NO hay un reclamo pendiente --}}
                    @if(!$application->pendingEligibilityOverride)
                    <a href="{{ route('admin.eligibility-override.edit-cv-evaluation', $application->id) }}"
                       class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modificar Evaluación
                    </a>
                    @endif

                    <a href="{{ route('admin.eligibility-override.index', $application->jobProfile->job_posting_id) }}"
                       class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        Volver a Lista de Reevaluaciones
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
