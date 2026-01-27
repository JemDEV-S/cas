@extends('applicantportal::components.layouts.master')

@section('title', 'Detalle de Postulación - ' . $application->code)

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center text-sm text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('applicant.applications.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            Mis Postulaciones
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $application->code }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header con estado -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-14 h-14 gradient-municipal rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $application->code }}</h1>
                        <p class="text-gray-600">{{ $jobProfile->profile_name }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Convocatoria: <strong>{{ $jobPosting->code }}</strong>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Fecha: {{ $application->application_date->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>

            <!-- Badge de estado -->
            <div class="flex flex-col items-end gap-3">
                @php
                    $color = $application->status->color();
                    $statusColorClasses = [
                        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        'blue' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'green' => 'bg-green-100 text-green-800 border-green-300',
                        'red' => 'bg-red-100 text-red-800 border-red-300',
                        'purple' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'orange' => 'bg-orange-100 text-orange-800 border-orange-300',
                        'gray' => 'bg-gray-100 text-gray-800 border-gray-300',
                    ];
                    $statusColor = $statusColorClasses[$color] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                @endphp

                <span class="px-4 py-2 rounded-xl font-bold text-sm border-2 {{ $statusColor }}">
                    {{ $application->status->label() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Botón grande para descargar ficha de postulación -->
    @if(in_array($application->status, [
        \Modules\Application\Enums\ApplicationStatus::SUBMITTED,
        \Modules\Application\Enums\ApplicationStatus::ELIGIBLE,
        \Modules\Application\Enums\ApplicationStatus::NOT_ELIGIBLE,
        \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION,
        \Modules\Application\Enums\ApplicationStatus::APPROVED
    ]) && $application->generatedDocuments()->whereHas('template', fn($q) => $q->where('code', 'TPL_APPLICATION_SHEET'))->exists())
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-white">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Ficha de Postulación</h3>
                        <p class="text-blue-100 text-sm">Descarga tu comprobante de inscripción en formato PDF</p>
                    </div>
                </div>
                <a href="{{ route('applicant.applications.download-pdf', $application->id) }}"
                   class="w-full sm:w-auto px-8 py-4 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition-all shadow-md flex items-center justify-center gap-3 text-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar PDF
                </a>
            </div>
        </div>
    @endif

    <!-- Contenido según el estado -->
    @if($application->status === \Modules\Application\Enums\ApplicationStatus::DRAFT)
        <!-- Estado: BORRADOR -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-bold text-yellow-800 mb-2">Tu postulación está guardada pero NO enviada</h3>
                    <p class="text-sm text-yellow-700 mb-4">
                        Esta postulación se encuentra en estado de borrador. Puedes editarla y completar la información antes de enviarla oficialmente.
                        <strong>Recuerda que solo las postulaciones enviadas participarán en el proceso de selección.</strong>
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('applicant.job-postings.apply', [$jobPosting->id, $jobProfile->id]) }}"
                           class="px-6 py-2 bg-yellow-600 text-white font-semibold rounded-xl hover:bg-yellow-700 transition-all">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar y Completar
                        </a>
                        <form action="{{ route('applicant.applications.submit', $application->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-2 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                                <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Enviar Ahora
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED)
        <!-- Estado: PRESENTADA -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-bold text-blue-800 mb-2">Postulación enviada correctamente</h3>
                    <p class="text-sm text-blue-700 mb-4">
                        Tu postulación ha sido registrada exitosamente. Está en proceso de evaluación automática.
                        Espera la publicación de resultados de la <strong>Fase 4</strong> para conocer si fuiste declarado APTO.
                    </p>
                </div>
            </div>
        </div>

    @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
        <!-- Estado: APTO -->
        @php
            $cvDocument = $application->documents()->where('document_type', 'DOC_CV')->first();
        @endphp

        @if($jobPosting->results_published)
            <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-xl font-bold text-green-800 mb-2">¡Felicidades! Has sido declarado APTO</h3>
                        <p class="text-sm text-green-700 mb-4">
                            Cumples con los requisitos declarados para el perfil. El siguiente paso es la
                            <strong>Fase 5: Presentación de CV Documentado</strong>, donde deberás adjuntar los
                            documentos sustentatorios (títulos, certificados, constancias de trabajo, etc.).
                        </p>
                        @if($application->documents()->where('document_type', 'DOC_APPLICATION_FORM')->exists())
                            <a href="{{ route('applicant.applications.download-pdf', $application->id) }}"
                               class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-all">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Descargar Ficha de Postulación
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sección de CV documentado --}}
            @if($canUploadCv ?? false)
                <div class="bg-gradient-to-r from-emerald-600 to-green-700 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 text-white">
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">CV Documentado - Fase 5</h3>
                                @if($cvDocument)
                                    <p class="text-emerald-100 text-sm">Tu CV ha sido subido correctamente</p>
                                @else
                                    <p class="text-emerald-100 text-sm">Sube tus documentos sustentatorios en un PDF</p>
                                @endif
                            </div>
                        </div>
                        @if($cvDocument)
                            <div class="flex gap-3">
                                <a href="{{ route('applicant.applications.view-cv', $application->id) }}"
                                   target="_blank"
                                   class="px-6 py-3 bg-white text-emerald-700 font-bold rounded-xl hover:bg-emerald-50 transition-all flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Ver PDF
                                </a>
                                <a href="{{ route('applicant.applications.upload-cv.form', $application->id) }}"
                                   class="px-6 py-3 bg-emerald-800 text-white font-bold rounded-xl hover:bg-emerald-900 transition-all flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reemplazar
                                </a>
                            </div>
                        @else
                            <a href="{{ route('applicant.applications.upload-cv.form', $application->id) }}"
                               class="w-full sm:w-auto px-8 py-4 bg-white text-emerald-700 font-bold rounded-xl hover:bg-emerald-50 transition-all shadow-md flex items-center justify-center gap-3 text-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Subir CV Documentado
                            </a>
                        @endif
                    </div>
                </div>
            @elseif($cvDocument)
                {{-- Si ya subió CV pero la fase terminó, solo mostrar para ver --}}
                <div class="bg-gradient-to-r from-gray-600 to-gray-700 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 text-white">
                            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">CV Documentado</h3>
                                <p class="text-gray-200 text-sm">Tu CV fue subido correctamente (fase cerrada)</p>
                            </div>
                        </div>
                        <a href="{{ route('applicant.applications.view-cv', $application->id) }}"
                           target="_blank"
                           class="px-6 py-3 bg-white text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver PDF
                        </a>
                    </div>
                </div>
            @else
                {{-- Si no subió CV y la fase terminó, mostrar mensaje de advertencia --}}
                <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-6 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-bold text-yellow-800 mb-2">CV Documentado no presentado</h3>
                            <p class="text-sm text-yellow-700">
                                La fase de presentación de CV documentado no está activa actualmente. Si no subiste tu CV durante el período habilitado, es posible que no puedas continuar en el proceso.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-bold text-yellow-800 mb-2">Resultados en proceso</h3>
                        <p class="text-sm text-yellow-700">
                            Los resultados de elegibilidad están siendo procesados. Serán publicados próximamente.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::NOT_ELIGIBLE)
        <!-- Estado: NO APTO -->
        @if($jobPosting->results_published)
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-xl font-bold text-red-800 mb-2">Declarado NO APTO</h3>
                        <p class="text-sm text-red-700 mb-4">
                            Lamentablemente, según la evaluación correspondiente, no cumples con los requisitos mínimos del perfil.
                        </p>

                        @if($application->ineligibility_reason)
                            <div class="bg-white border border-red-200 rounded-lg p-4 mb-4">
                                <h4 class="font-bold text-red-900 mb-2">Motivos:</h4>
                                <div class="text-sm text-red-800 whitespace-pre-line">{{ $application->ineligibility_reason }}</div>
                            </div>
                        @endif

                        <div class="bg-white border border-red-200 rounded-lg p-4 mb-4">
                            <h4 class="font-bold text-red-900 mb-2">¿Qué puedo hacer?</h4>
                            <ul class="list-disc list-inside space-y-2 text-sm text-red-800">
                                <li>Si consideras que hay un error en la evaluación, puedes presentar un reclamo</li>
                                <li>Verifica que la información declarada sea correcta y coincida con tus documentos</li>
                                <li>Puedes postular a otros perfiles si cumples sus requisitos</li>
                            </ul>
                        </div>

                        <button type="button"
                                onclick="alert('Funcionalidad de reclamos en desarrollo')"
                                class="inline-flex items-center px-6 py-2 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Presentar Reclamo
                        </button>
                    </div>
                </div>
            </div>
        @endif

    @elseif($application->status === \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION)
        <!-- Estado: EN_EVALUACION -->
        <div class="bg-purple-50 border-l-4 border-purple-500 rounded-xl p-6 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-bold text-purple-800 mb-2">En Evaluación Curricular</h3>
                    <p class="text-sm text-purple-700">
                        Tu documentación está siendo evaluada por el comité de selección.
                        Los resultados serán publicados según el cronograma de la convocatoria.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Ficha de Postulación (si está enviada) -->
    @if(in_array($application->status, [
        \Modules\Application\Enums\ApplicationStatus::SUBMITTED,
        \Modules\Application\Enums\ApplicationStatus::ELIGIBLE,
        \Modules\Application\Enums\ApplicationStatus::NOT_ELIGIBLE,
        \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION,
        \Modules\Application\Enums\ApplicationStatus::APPROVED
    ]) && $application->documents()->where('document_type', 'DOC_APPLICATION_FORM')->exists())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Ficha de Postulación
            </h2>

            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $application->code }}.pdf</p>
                        <p class="text-sm text-gray-600">
                            Generado: {{ $application->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <a href="{{ route('applicant.applications.download-pdf', $application->id) }}"
                       class="px-4 py-2 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Descargar PDF
                    </a>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <strong>Importante:</strong> Esta ficha es tu comprobante de inscripción. Guárdala para futuras referencias.
                </p>
            </div>
        </div>
    @endif

    <!-- Timeline de Fases del Proceso -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Cronología del Proceso CAS</h2>

        <div class="relative">
            <!-- Línea vertical -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

            <!-- Fases -->
            <div class="space-y-6">
                @php
                    $phases = [
                        ['code' => 'PHASE_03_REGISTRATION', 'name' => 'Fase 3: Registro Virtual de Postulantes', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['code' => 'PHASE_04_ELIGIBILITY', 'name' => 'Fase 4: Publicación de Aptos', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['code' => 'PHASE_05_DOCUMENTS', 'name' => 'Fase 5: Presentación de CV Documentado', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        ['code' => 'PHASE_06_EVALUATION', 'name' => 'Fase 6: Evaluación Curricular', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['code' => 'PHASE_08_INTERVIEW', 'name' => 'Fase 8: Entrevista Personal', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                        ['code' => 'PHASE_09_RESULTS', 'name' => 'Fase 9: Publicación de Resultados Finales', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ];

                    $currentPhaseCode = isset($currentPhase) ? $currentPhase->code : null;
                    $currentPhaseIndex = $currentPhaseCode
                        ? collect($phases)->search(fn($p) => $p['code'] === $currentPhaseCode)
                        : false;
                @endphp

                @foreach($phases as $index => $phase)
                    @php
                        $isCompleted = $index < $currentPhaseIndex;
                        $isCurrent = $index === $currentPhaseIndex;
                        $isPending = $index > $currentPhaseIndex;
                    @endphp

                    <div class="relative pl-12">
                        <!-- Icono -->
                        <div class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center
                            {{ $isCompleted ? 'bg-green-500' : ($isCurrent ? 'bg-blue-500' : 'bg-gray-300') }}">
                            @if($isCompleted)
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 {{ $isCurrent ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $phase['icon'] }}"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Contenido -->
                        <div class="pb-4">
                            <h3 class="font-bold {{ $isCurrent ? 'text-blue-900' : ($isCompleted ? 'text-green-900' : 'text-gray-500') }}">
                                {{ $phase['name'] }}
                                @if($isCurrent)
                                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">En curso</span>
                                @endif
                            </h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Resumen de Datos Declarados -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Datos Declarados</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Formación Académica -->
            <div class="border border-gray-200 rounded-xl p-4">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    Formación Académica
                </h3>
                <p class="text-sm text-gray-600">{{ $application->academics->count() }} título(s)/grado(s) declarado(s)</p>
            </div>

            <!-- Experiencia -->
            <div class="border border-gray-200 rounded-xl p-4">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Experiencia Laboral
                </h3>
                <p class="text-sm text-gray-600">{{ $application->experiences->count() }} experiencia(s) declarada(s)</p>
            </div>

            <!-- Capacitaciones -->
            <div class="border border-gray-200 rounded-xl p-4">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Capacitaciones
                </h3>
                <p class="text-sm text-gray-600">{{ $application->trainings->count() }} capacitación(es) declarada(s)</p>
            </div>

            <!-- Condiciones Especiales -->
            <div class="border border-gray-200 rounded-xl p-4">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Condiciones Especiales
                </h3>
                <p class="text-sm text-gray-600">
                    @if($application->specialConditions->count() > 0)
                        {{ $application->specialConditions->count() }} bonificación(es)
                    @else
                        Ninguna
                    @endif
                </p>
            </div>
        </div>
    </div>

</div>

@if(session('auto_download_pdf'))
<script>
    // Descargar automáticamente la ficha de postulación después de enviar
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.location.href = "{{ route('applicant.applications.download-pdf', $application->id) }}";
        }, 1000); // Esperar 1 segundo para que el usuario vea el mensaje de éxito
    });
</script>
@endif
@endsection
