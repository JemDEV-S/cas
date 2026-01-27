@extends('layouts.app')

@section('title', 'Detalle de Evaluación')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="breadcrumb">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                        Inicio
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li>
                    <a href="{{ route('evaluation.my-evaluations') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                        Mis Evaluaciones
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-600">Detalle</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-6 gap-4">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
                    <i class="fas fa-clipboard-check text-orange-600"></i>
                    Evaluación Completada
                </h1>
                <p class="text-gray-600">Detalle completo de la evaluación realizada</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('evaluation.my-evaluations') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                @if($evaluation->status->value === 'SUBMITTED')
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 rounded-lg text-white hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
                @endif
            </div>
        </div>

        <!-- Estado de la Evaluación -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full {{ $evaluation->status->value === 'SUBMITTED' ? 'bg-green-100' : 'bg-yellow-100' }}">
                        <i class="fas fa-{{ $evaluation->status->value === 'SUBMITTED' ? 'check-circle' : 'hourglass-half' }} text-3xl {{ $evaluation->status->value === 'SUBMITTED' ? 'text-green-600' : 'text-yellow-600' }}"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ $evaluation->status->label() }}
                        </h3>
                        <p class="text-gray-600">
                            @if($evaluation->submitted_at)
                                Enviada el {{ $evaluation->submitted_at->format('d/m/Y \a \l\a\s H:i') }}
                            @else
                                En proceso de evaluación
                            @endif
                        </p>
                    </div>
                </div>

                @if($evaluation->status->value === 'SUBMITTED')
                <div class="flex flex-col items-end">
                    <div class="text-5xl font-bold text-blue-600">
                        {{ number_format($evaluation->total_score, 2) }}
                    </div>
                    <div class="text-sm text-gray-600">
                        de {{ number_format($evaluation->max_possible_score, 2) }} puntos
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ number_format($evaluation->percentage, 2) }}%
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información del Postulante -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-4 px-6">
                        <h5 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            Postulante Evaluado
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600 mb-1 block">Nombre Completo</label>
                                <p class="font-semibold text-gray-900">
                                    {{ $evaluation->application->full_name ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 mb-1 block">Codigo de Postulación</label>
                                <p class="font-mono text-gray-900">
                                    {{ $evaluation->application->code ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 mb-1 block">Email</label>
                                <p class="text-gray-900">
                                    {{ $evaluation->application->applicant->email ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 mb-1 block">DNI</label>
                                <p class="font-mono text-gray-900">
                                    {{ $evaluation->application->applicant->dni ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <a href="{{ route('application.show', $evaluation->application_id) }}"
                                   class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    Ver Postulación Completa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Criterios de Evaluación -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white py-4 px-6">
                        <h5 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fas fa-list-check"></i>
                            Criterios de Evaluación
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @forelse($evaluation->details as $detail)
                            <div class="border-l-4 border-blue-500 bg-gray-50 rounded-lg p-5 hover:bg-gray-100 transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h6 class="font-bold text-gray-900 text-lg mb-1">
                                            {{ $detail->criterion->name }}
                                        </h6>
                                        @if($detail->criterion->description)
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ $detail->criterion->description }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="ml-4 text-right">
                                        <div class="text-3xl font-bold text-blue-600">
                                            {{ number_format($detail->score, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            de {{ number_format($detail->criterion->max_score, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Barra de progreso -->
                                @php
                                    $percentage = $detail->criterion->max_score > 0
                                        ? ($detail->score / $detail->criterion->max_score) * 100
                                        : 0;
                                    $barColor = $percentage >= 80 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                                    <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500"
                                         style="width: {{ $percentage }}%"></div>
                                </div>

                                @if($detail->comments)
                                <div class="mt-3">
                                    <label class="text-xs font-semibold text-gray-700 uppercase mb-1 block">
                                        <i class="fas fa-comment-dots mr-1"></i>Comentarios
                                    </label>
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
                                        {{ $detail->comments }}
                                    </div>
                                </div>
                                @endif

                                @if($detail->evidence)
                                <div class="mt-3">
                                    <label class="text-xs font-semibold text-gray-700 uppercase mb-1 block">
                                        <i class="fas fa-paperclip mr-1"></i>Evidencia
                                    </label>
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
                                        {{ $detail->evidence }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No hay criterios evaluados</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Comentarios Generales -->
                @if($evaluation->general_comments)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 px-6">
                        <h5 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fas fa-comment-alt"></i>
                            Comentarios Generales
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-700">
                            {{ $evaluation->general_comments }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Modificaciones -->
                @if($evaluation->modified_by || $evaluation->modification_reason)
                <div class="bg-white rounded-xl shadow-sm border border-yellow-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white py-4 px-6">
                        <h5 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            Modificaciones
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            @if($evaluation->modified_by)
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Modificado por:</strong>
                                {{ \App\Models\User::find($evaluation->modified_by)->full_name ?? 'N/A' }}
                            </p>
                            @endif

                            @if($evaluation->modified_at)
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>Fecha de modificación:</strong>
                                {{ $evaluation->modified_at->format('d/m/Y H:i') }}
                            </p>
                            @endif

                            @if($evaluation->modification_reason)
                            <p class="text-sm text-gray-700">
                                <strong>Razón:</strong>
                                {{ $evaluation->modification_reason }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Columna Lateral -->
            <div class="space-y-6">
                <!-- Resumen de Puntajes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 py-3 px-4 border-b border-gray-200">
                        <h6 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-bar"></i>
                            Resumen de Puntajes
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            <div class="text-center py-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                                <div class="text-4xl font-bold text-blue-600 mb-1">
                                    {{ number_format($evaluation->total_score, 2) }}
                                </div>
                                <div class="text-sm text-gray-600">Puntaje Total</div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ number_format($evaluation->max_possible_score, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-600">Puntaje Máximo</div>
                                </div>

                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ number_format($evaluation->percentage, 2) }}%
                                    </div>
                                    <div class="text-xs text-gray-600">Porcentaje</div>
                                </div>
                            </div>

                            @php
                                $totalPercentage = $evaluation->max_possible_score > 0
                                    ? ($evaluation->total_score / $evaluation->max_possible_score) * 100
                                    : 0;
                                $progressColor = $totalPercentage >= 80 ? 'from-green-500 to-emerald-500' :
                                                ($totalPercentage >= 50 ? 'from-yellow-500 to-orange-500' : 'from-red-500 to-rose-500');
                            @endphp
                            <div class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold inline-block text-gray-600">
                                            Rendimiento
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold inline-block text-gray-600">
                                            {{ number_format($totalPercentage, 1) }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-4 text-xs flex rounded-full bg-gray-200">
                                    <div style="width: {{ $totalPercentage }}%"
                                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r {{ $progressColor }} transition-all duration-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la Fase -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 py-3 px-4 border-b border-gray-200">
                        <h6 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            Fase de Evaluación
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Nombre de la Fase</label>
                                <p class="font-semibold text-gray-900">
                                    {{ $evaluation->phase->name ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Código</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                                    {{ $evaluation->phase->code ?? 'N/A' }}
                                </span>
                            </div>

                            @if($evaluation->phase->description)
                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Descripción</label>
                                <p class="text-sm text-gray-700">
                                    {{ $evaluation->phase->description }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Información de la Convocatoria -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 py-3 px-4 border-b border-gray-200">
                        <h6 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-briefcase"></i>
                            Convocatoria
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Título</label>
                                <p class="font-semibold text-gray-900 text-sm">
                                    {{ $evaluation->jobPosting->title ?? 'N/A' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Código</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $evaluation->jobPosting->code ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                <a href="{{ route('jobposting.show', $evaluation->job_posting_id) }}"
                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    Ver Convocatoria
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Evaluador -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 py-3 px-4 border-b border-gray-200">
                        <h6 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-user-check"></i>
                            Evaluador
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr($evaluation->evaluator->first_name ?? 'E', 0, 1)) }}{{ strtoupper(substr($evaluation->evaluator->last_name ?? 'V', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    {{ $evaluation->evaluator->full_name ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ $evaluation->evaluator->email ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        @if($evaluation->is_anonymous)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-xs text-yellow-800 flex items-center gap-2">
                                <i class="fas fa-user-secret"></i>
                                <span>Evaluación anónima</span>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Fechas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-100 py-3 px-4 border-b border-gray-200">
                        <h6 class="font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-calendar-alt"></i>
                            Fechas Importantes
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-calendar-plus text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-600">Creada</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $evaluation->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>

                            @if($evaluation->submitted_at)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-600">Enviada</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $evaluation->submitted_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            @if($evaluation->deadline_at)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">
                                    <i class="fas fa-clock text-orange-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-600">Fecha Límite</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $evaluation->deadline_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }
}
</style>
@endsection
