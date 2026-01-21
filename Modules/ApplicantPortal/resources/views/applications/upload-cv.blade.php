@extends('applicantportal::components.layouts.master')

@section('title', 'Subir CV Documentado - ' . $application->code)

@section('content')
<div class="max-w-3xl mx-auto">

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
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('applicant.applications.show', $application->id) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                            {{ $application->code }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Subir CV</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    {{-- Mensaje de éxito grande cuando se sube el CV --}}
    @if(session('cv_uploaded'))
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-xl p-8 mb-6">
            <div class="flex flex-col items-center text-center text-white">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold mb-3">¡CV Subido Correctamente!</h2>
                <p class="text-xl text-green-100 mb-2">Tu CV documentado ha sido registrado en el sistema exitosamente.</p>
                <p class="text-green-200 mb-6">Ahora solo debes esperar la evaluación curricular por parte del comité.</p>
                <div class="flex gap-4">
                    <a href="{{ route('applicant.applications.view-cv', $application->id) }}"
                       target="_blank"
                       class="px-6 py-3 bg-white text-green-700 font-bold rounded-xl hover:bg-green-50 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ver mi CV
                    </a>
                    <a href="{{ route('applicant.applications.show', $application->id) }}"
                       class="px-6 py-3 bg-green-700 text-white font-bold rounded-xl hover:bg-green-800 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Ver mi postulación
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-green-700 rounded-2xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Subir CV Documentado</h1>
                <p class="text-emerald-100">Fase 5: Presentación de documentos sustentatorios</p>
            </div>
        </div>
    </div>

    <!-- Info de la postulación -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Postulación</p>
                <p class="font-bold text-gray-900">{{ $application->code }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Perfil</p>
                <p class="font-bold text-gray-900">{{ $application->jobProfile->profile_name }}</p>
            </div>
        </div>
    </div>

    @php
        $cvDocument = $application->documents()->where('document_type', 'DOC_CV')->first();
    @endphp

    @if($cvDocument && !session('cv_uploaded'))
        {{-- CV ya subido - mostrar información --}}
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-9 h-9 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-green-800 text-xl mb-2">Ya tienes un CV subido</h3>
                    <p class="text-green-700 mb-1">{{ $cvDocument->file_name }}</p>
                    <p class="text-sm text-green-600">
                        Tamaño: {{ $cvDocument->formatted_size }} |
                        Subido: {{ $cvDocument->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <a href="{{ route('applicant.applications.view-cv', $application->id) }}"
                   target="_blank"
                   class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Ver PDF
                </a>
            </div>
        </div>

        {{-- Opción de reemplazar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                ¿Necesitas reemplazar tu CV?
            </h3>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>Nota:</strong> Si subes un nuevo archivo, el anterior será eliminado y reemplazado por el nuevo.
                </p>
            </div>
            @include('applicantportal::applications.partials.cv-upload-form', ['application' => $application])
        </div>
    @else
        {{-- Formulario de subida principal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Instrucciones</h2>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-7 h-7 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="font-bold text-blue-900 mb-3 text-lg">Sigue estos pasos:</h4>
                        <ol class="text-blue-800 space-y-3">
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">1</span>
                                <span><strong>Reúne todos tus documentos:</strong> DNI, títulos, certificados, constancias de trabajo, diplomas, certificados de capacitación, etc.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">2</span>
                                <span><strong>Escanea o fotografía:</strong> Toma fotos claras de cada documento o escanéalos.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">3</span>
                                <span><strong>Une en un solo PDF:</strong> Usa aplicaciones como CamScanner, Adobe Scan, o cualquier herramienta que una imágenes en PDF.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">4</span>
                                <span><strong>Sube tu archivo:</strong> El archivo debe ser <strong>PDF</strong> y no superar los <strong>15 MB</strong>.</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-yellow-800">
                        <strong>Importante:</strong> Asegúrate de incluir todos los documentos que respalden tu formación académica, experiencia laboral y capacitaciones declaradas en tu postulación.
                    </p>
                </div>
            </div>

            @include('applicantportal::applications.partials.cv-upload-form', ['application' => $application])
        </div>
    @endif

    <!-- Botón volver -->
    <div class="text-center">
        <a href="{{ route('applicant.applications.show', $application->id) }}"
           class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al detalle de la postulación
        </a>
    </div>

</div>
@endsection
