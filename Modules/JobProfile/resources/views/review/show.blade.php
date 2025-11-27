@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Revisar Perfil de Puesto</h1>
                <p class="mt-1 text-sm text-gray-600">Código: <code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code></p>
            </div>
            <a href="{{ route('jobprofile.review.index') }}">
                <x-button variant="secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contenido Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información General -->
            <x-card title="Información General">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
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
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Régimen Laboral</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->work_regime_label ?? 'N/A' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Total de Vacantes</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $jobProfile->total_vacancies }}
                            </span>
                        </dd>
                    </div>
                    @if($jobProfile->description)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->description }}</dd>
                    </div>
                    @endif
                    @if($jobProfile->justification)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Justificación</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->justification }}</dd>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Requisitos Académicos -->
            <x-card title="Requisitos Académicos">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">Nivel Educativo</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->education_level_label }}</dd>
                    </div>
                    @if($jobProfile->career_field)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Área de Estudios</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->career_field }}</dd>
                    </div>
                    @endif
                    @if($jobProfile->title_required)
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Título Requerido</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $jobProfile->title_required }}</dd>
                    </div>
                    @endif
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Colegiatura</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if($jobProfile->colegiatura_required)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Requerida
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times mr-1"></i> No Requerida
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
                            <span class="font-semibold">{{ $jobProfile->general_experience_years ?? 0 }}</span> años
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 border-t border-gray-200 pt-4">
                        <dt class="text-sm font-medium text-gray-500">Experiencia Específica</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <span class="font-semibold">{{ $jobProfile->specific_experience_years ?? 0 }}</span> años
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

        <!-- Panel de Acciones de Revisión -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Información del Solicitante -->
            <x-card title="Información del Solicitante">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Solicitado por</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $jobProfile->requestedBy->name ?? 'N/A' }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ $jobProfile->requestedBy->email ?? '' }}</p>
                    </div>
                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de Solicitud</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $jobProfile->requested_at?->format('d/m/Y H:i') ?? $jobProfile->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Acciones de Revisión -->
            <x-card title="Acciones de Revisión" class="border-2 border-blue-200">
                <div class="space-y-4">
                    <!-- Aprobar -->
                    <form action="{{ route('jobprofile.review.approve', $jobProfile->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="approve_comments" class="block text-sm font-medium text-gray-700 mb-1">
                                Comentarios (opcional)
                            </label>
                            <textarea
                                name="comments"
                                id="approve_comments"
                                rows="2"
                                class="border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm w-full text-sm"
                                placeholder="Comentarios adicionales..."></textarea>
                        </div>
                        <x-button type="submit" variant="success" class="w-full" onclick="return confirm('¿Está seguro de aprobar este perfil? Se generarán las vacantes automáticamente.')">
                            <i class="fas fa-check-circle mr-2"></i> Aprobar Perfil
                        </x-button>
                    </form>

                    <!-- Solicitar Modificación -->
                    <div class="border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            onclick="showModificationForm()"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-edit mr-2"></i> Solicitar Modificación
                        </button>
                    </div>

                    <!-- Rechazar -->
                    <div class="border-t border-gray-200 pt-4">
                        <button
                            type="button"
                            onclick="showRejectionForm()"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="fas fa-times-circle mr-2"></i> Rechazar Perfil
                        </button>
                    </div>
                </div>
            </x-card>

            <!-- Historial -->
            @if($jobProfile->history->count() > 0)
            <x-card title="Historial">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($jobProfile->history->take(3) as $history)
                            <li>
                                <div class="relative pb-6">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-3 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-2">
                                        <div>
                                            <span class="h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white">
                                                <i class="fas fa-clock text-white text-xs"></i>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 flex-col">
                                            <div>
                                                <p class="text-xs font-medium text-gray-900">{{ $history->action_label }}</p>
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</p>
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

<!-- Modal para Solicitar Modificación -->
<div id="modificationModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solicitar Modificación</h3>
        <form action="{{ route('jobprofile.review.request-modification', $jobProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="modification_comments" class="block text-sm font-medium text-gray-700 mb-1">
                    Comentarios <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="comments"
                    id="modification_comments"
                    rows="4"
                    required
                    class="border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm w-full"
                    placeholder="Explique qué modificaciones se requieren..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeModificationForm()">
                    Cancelar
                </x-button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Rechazar -->
<div id="rejectionModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rechazar Perfil</h3>
        <form action="{{ route('jobprofile.review.reject', $jobProfile->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Razón del Rechazo <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="reason"
                    id="rejection_reason"
                    rows="4"
                    required
                    class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm w-full"
                    placeholder="Explique por qué se rechaza este perfil..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeRejectionForm()">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="danger">
                    Confirmar Rechazo
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showModificationForm() {
    document.getElementById('modificationModal').classList.remove('hidden');
}

function closeModificationForm() {
    document.getElementById('modificationModal').classList.add('hidden');
}

function showRejectionForm() {
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeRejectionForm() {
    document.getElementById('rejectionModal').classList.add('hidden');
}
</script>
@endpush
@endsection
