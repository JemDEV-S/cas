@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Perfil de Puesto</h1>
                <p class="mt-1 text-sm text-gray-600">Código: <code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code></p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('jobprofile.index') }}">
                    <x-button variant="secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </x-button>
                </a>
                @can('update', $jobProfile)
                    <a href="{{ route('jobprofile.profiles.edit', $jobProfile->id) }}">
                        <x-button variant="primary">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </x-button>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Estado y Acciones -->
    <x-card class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h5 class="text-lg font-semibold text-gray-900 mb-2">Estado Actual</h5>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($jobProfile->status_badge === 'success') bg-green-100 text-green-800
                    @elseif($jobProfile->status_badge === 'warning') bg-yellow-100 text-yellow-800
                    @elseif($jobProfile->status_badge === 'danger') bg-red-100 text-red-800
                    @elseif($jobProfile->status_badge === 'info') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $jobProfile->status_label }}
                </span>
            </div>
            <div class="flex gap-2">
                @if($jobProfile->canSubmitForReview())
                    @can('submitForReview', $jobProfile)
                        <form action="{{ route('jobprofile.profiles.submit', $jobProfile->id) }}" method="POST" class="inline">
                            @csrf
                            <x-button type="submit" variant="success" onclick="return confirm('¿Está seguro de enviar este perfil a revisión? Asegúrese de que toda la información esté completa.')">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar a Revisión
                            </x-button>
                        </form>
                    @endcan
                @endif

                @if($jobProfile->isApproved())
                    <a href="{{ route('jobprofile.vacancies.index', $jobProfile->id) }}">
                        <x-button variant="primary">
                            <i class="fas fa-list mr-2"></i> Ver Vacantes ({{ $jobProfile->vacancies->count() }})
                        </x-button>
                    </a>
                @endif
            </div>
        </div>
    </x-card>

    <!-- Alertas de Estado -->
    @if($jobProfile->isInReview())
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>En Revisión:</strong> Este perfil está siendo revisado por el equipo de RRHH.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($jobProfile->isModificationRequested())
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Modificaciones Solicitadas:</strong>
                    </p>
                    @if($jobProfile->review_comments)
                        <p class="mt-2 text-sm text-yellow-700 bg-white rounded p-2 border border-yellow-200">
                            {{ $jobProfile->review_comments }}
                        </p>
                        <p class="mt-2 text-xs text-yellow-600">
                            Revisado por: {{ $jobProfile->reviewedBy->name ?? 'N/A' }} - {{ $jobProfile->reviewed_at?->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($jobProfile->isRejected())
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <strong>Perfil Rechazado:</strong>
                    </p>
                    @if($jobProfile->rejection_reason)
                        <p class="mt-2 text-sm text-red-700 bg-white rounded p-2 border border-red-200">
                            {{ $jobProfile->rejection_reason }}
                        </p>
                        <p class="mt-2 text-xs text-red-600">
                            Rechazado por: {{ $jobProfile->reviewedBy->name ?? 'N/A' }} - {{ $jobProfile->reviewed_at?->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($jobProfile->isApproved())
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <strong>Perfil Aprobado:</strong> Este perfil ha sido aprobado y se han generado las vacantes correspondientes.
                    </p>
                    <p class="mt-2 text-xs text-green-600">
                        Aprobado por: {{ $jobProfile->approvedBy->name ?? 'N/A' }} - {{ $jobProfile->approved_at?->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Información General -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Información General -->
            <x-card title="Información General">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="text-sm text-gray-900 col-span-2"><code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code></dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Título del Puesto</dt>
                        <dd class="text-sm font-semibold text-gray-900 col-span-2">{{ $jobProfile->title }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Código de Posición</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if($jobProfile->positionCode)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $jobProfile->positionCode->code }}
                                </span>
                                <span class="ml-2">{{ $jobProfile->positionCode->name }}</span>
                                <p class="mt-1 text-xs text-gray-500">{{ $jobProfile->positionCode->formatted_monthly_total }} mensual</p>
                            @else
                                <span class="text-gray-400">Sin código asignado</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Unidad Organizacional</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->organizationalUnit->name ?? 'N/A' }}</dd>
                    </div>
                    @if($jobProfile->jobPosting)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Convocatoria Asociada</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <a href="{{ route('jobposting.show', $jobProfile->jobPosting->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ $jobProfile->jobPosting->code }} - {{ $jobProfile->jobPosting->title }}
                            </a>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $jobProfile->jobPosting->status->badgeClass() }}">
                                {{ $jobProfile->jobPosting->status->label() }}
                            </span>
                        </dd>
                    </div>
                    @endif
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->description ?? 'Sin descripción' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Total de Vacantes</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $jobProfile->total_vacancies }}
                            </span>
                        </dd>
                    </div>
                    @if($jobProfile->contract_start_date && $jobProfile->contract_end_date)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Vigencia del Contrato</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                            {{ $jobProfile->contract_duration }}
                        </dd>
                    </div>
                    @endif
                    @if($jobProfile->work_location)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Lugar de Prestación</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                            {{ $jobProfile->work_location }}
                        </dd>
                    </div>
                    @endif
                    @if($jobProfile->positionCode)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Remuneración Mensual</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="text-lg font-bold text-green-600">S/ {{ number_format($jobProfile->positionCode->base_salary, 2) }}</span>
                            <p class="text-xs text-gray-500 mt-1">Incluye todos los beneficios de ley</p>
                        </dd>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Requisitos del Cargo (desde Anexo 11) -->
            @if($jobProfile->positionCode)
            <x-card title="Requisitos Generales del Cargo (Anexo 11)">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Estos son los requisitos mínimos establecidos para el cargo <strong>{{ $jobProfile->positionCode->code }}</strong> según el Anexo 11.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="prose prose-sm max-w-none">
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $jobProfile->getRequisitosGenerales() }}</p>
                </div>
            </x-card>
            @endif

            <!-- Requisitos Académicos -->
            <x-card title="Requisitos Académicos">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Nivel Educativo</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->education_level_label }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Área de Estudios</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->career_field ?? 'No especificado' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Título Requerido</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->title_required ?? 'No especificado' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Colegiatura</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if($jobProfile->colegiatura_required)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Sí
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times mr-1"></i> No
                                </span>
                            @endif
                        </dd>
                    </div>
                </div>
            </x-card>

            <!-- Experiencia -->
            <x-card title="Experiencia Laboral">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Experiencia General</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="font-semibold">{{ $jobProfile->general_experience_years?->toHuman() ?? 'Sin experiencia' }}</span>
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Experiencia Específica</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="font-semibold">{{ $jobProfile->specific_experience_years?->toHuman() ?? 'Sin experiencia' }}</span>
                        </dd>
                    </div>
                    @if($jobProfile->specific_experience_description)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Detalle</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->specific_experience_description }}</dd>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Capacitaciones Requeridas -->
            @if($jobProfile->required_courses && count($jobProfile->required_courses) > 0)
            <x-card title="Capacitaciones Requeridas">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-certificate text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-yellow-700">
                                Es necesario acreditar con documentos como constancias, certificados o diplomas
                            </p>
                        </div>
                    </div>
                </div>
                <ul class="list-disc list-inside space-y-2">
                    @foreach($jobProfile->required_courses as $course)
                        <li class="text-sm text-gray-700">{{ is_array($course) ? $course['name'] : $course }}</li>
                    @endforeach
                </ul>
            </x-card>
            @endif

            <!-- Conocimientos Requeridos -->
            @if($jobProfile->knowledge_areas && count($jobProfile->knowledge_areas) > 0)
            <x-card title="Conocimientos Requeridos">
                <div class="grid grid-cols-2 gap-3">
                    @foreach($jobProfile->knowledge_areas as $knowledge)
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-sm text-gray-700">{{ $knowledge }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
            @endif

            <!-- Competencias Requeridas -->
            @if($jobProfile->required_competencies && count($jobProfile->required_competencies) > 0)
            <x-card title="Competencias Requeridas">
                <div class="flex flex-wrap gap-2">
                    @foreach($jobProfile->required_competencies as $competency)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $competency }}
                        </span>
                    @endforeach
                </div>
            </x-card>
            @endif

            <!-- Funciones Principales -->
            @if($jobProfile->main_functions && count($jobProfile->main_functions) > 0)
            <x-card title="Funciones Principales">
                <ol class="list-decimal list-inside space-y-2">
                    @foreach($jobProfile->main_functions as $function)
                        <li class="text-sm text-gray-700 pl-2">{{ $function }}</li>
                    @endforeach
                </ol>
            </x-card>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Información de Seguimiento -->
            <x-card title="Seguimiento">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Solicitado por</p>
                        <p class="mt-1 text-sm text-gray-900">{{ optional($jobProfile->requestedBy)->first_name . ' ' . optional($jobProfile->requestedBy)->last_name ?? 'N/A' }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $jobProfile->requested_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    </div>

                    @if($jobProfile->reviewedBy)
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Revisado por</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $jobProfile->reviewedBy->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $jobProfile->reviewed_at?->format('d/m/Y H:i') }}</p>
                            @if($jobProfile->review_comments)
                                <div class="mt-2 p-3 bg-yellow-50 rounded-md">
                                    <p class="text-xs font-medium text-yellow-800">Comentarios:</p>
                                    <p class="mt-1 text-sm text-yellow-700">{{ $jobProfile->review_comments }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($jobProfile->approvedBy)
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aprobado por</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $jobProfile->approvedBy->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ $jobProfile->approved_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif

                    @if($jobProfile->rejection_reason)
                        <div class="border-t border-gray-200 pt-4">
                            <div class="p-3 bg-red-50 rounded-md">
                                <p class="text-xs font-medium text-red-800">Razón de Rechazo:</p>
                                <p class="mt-1 text-sm text-red-700">{{ $jobProfile->rejection_reason }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Historial de Cambios -->
            @if($jobProfile->history->count() > 0)
            <x-card title="Historial de Cambios">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($jobProfile->history->take(5) as $index => $history)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-clock text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $history->action_label }}</p>
                                                @if($history->status_change_text)
                                                    <p class="mt-0.5 text-xs text-gray-500">{{ $history->status_change_text }}</p>
                                                @endif
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $history->user->name ?? 'Sistema' }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                <time datetime="{{ $history->created_at }}">{{ $history->created_at->format('d/m/Y H:i') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
