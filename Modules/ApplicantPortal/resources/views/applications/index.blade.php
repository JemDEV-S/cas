@extends('applicantportal::components.layouts.master')

@section('title', 'Mis Postulaciones')

@section('content')

<!-- Header de página -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Mis Postulaciones</h1>
            <p class="text-gray-600">Seguimiento de todas tus postulaciones en el sistema</p>
        </div>
        <a href="{{ route('applicant.job-postings.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 gradient-municipal text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Postulación
        </a>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-sm text-gray-600 mb-1">Total</p>
        <p class="text-2xl font-bold text-gray-900">{{ $statusCounts['total'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
        <p class="text-sm text-gray-600 mb-1">Aprobadas</p>
        <p class="text-2xl font-bold text-green-600">{{ $statusCounts['APROBADA'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
        <p class="text-sm text-gray-600 mb-1">En Evaluación</p>
        <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['EN_EVALUACION'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
        <p class="text-sm text-gray-600 mb-1">En Revisión</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $statusCounts['EN_REVISION'] ?? 0 }}</p>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <form method="GET" action="{{ route('applicant.applications.index') }}" class="flex flex-col md:flex-row gap-4">
        <!-- Filtro por estado -->
        <div class="flex-1">
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
            <select id="status"
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-municipal-blue focus:border-transparent">
                <option value="">Todos los estados</option>
                <option value="PRESENTADA" {{ $status == 'PRESENTADA' ? 'selected' : '' }}>Presentada</option>
                <option value="EN_REVISION" {{ $status == 'EN_REVISION' ? 'selected' : '' }}>En Revisión</option>
                <option value="APTO" {{ $status == 'APTO' ? 'selected' : '' }}>Apto</option>
                <option value="NO_APTO" {{ $status == 'NO_APTO' ? 'selected' : '' }}>No Apto</option>
                <option value="EN_EVALUACION" {{ $status == 'EN_EVALUACION' ? 'selected' : '' }}>En Evaluación</option>
                <option value="APROBADA" {{ $status == 'APROBADA' ? 'selected' : '' }}>Aprobada</option>
                <option value="RECHAZADA" {{ $status == 'RECHAZADA' ? 'selected' : '' }}>Rechazada</option>
                <option value="DESISTIDA" {{ $status == 'DESISTIDA' ? 'selected' : '' }}>Desistida</option>
            </select>
        </div>

        <!-- Búsqueda -->
        <div class="flex-1">
            <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Buscar</label>
            <input type="text"
                   id="search"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Código o nombre del puesto..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-municipal-blue focus:border-transparent">
        </div>

        <!-- Botones -->
        <div class="flex items-end gap-3">
            <button type="submit"
                    class="px-6 py-2 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300">
                Filtrar
            </button>
            <a href="{{ route('applicant.applications.index') }}"
               class="px-6 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Listado de postulaciones -->
@if($applications->count() > 0)
    <div class="space-y-4">
        @foreach($applications as $application)
            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <!-- Información principal -->
                        <div class="flex-1">
                            <div class="flex items-start gap-4 mb-4">
                                <!-- Estado visual -->
                                <div class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center
                                    {{ $application->status == 'APROBADA' ? 'bg-gradient-to-br from-green-500 to-green-600' : '' }}
                                    {{ $application->status == 'EN_EVALUACION' ? 'bg-gradient-to-br from-blue-500 to-blue-600' : '' }}
                                    {{ $application->status == 'PRESENTADA' ? 'bg-gradient-to-br from-yellow-500 to-yellow-600' : '' }}
                                    {{ $application->status == 'RECHAZADA' ? 'bg-gradient-to-br from-red-500 to-red-600' : '' }}
                                    {{ !in_array($application->status, ['APROBADA', 'EN_EVALUACION', 'PRESENTADA', 'RECHAZADA']) ? 'bg-gradient-to-br from-gray-500 to-gray-600' : '' }}">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <!-- Detalles -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                                {{ $application->jobProfile->position_code->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600">{{ $application->jobPosting->title }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">
                                            {{ $application->code }}
                                        </span>
                                        <span class="px-3 py-1 text-xs font-bold rounded-full
                                            {{ $application->status == 'APROBADA' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $application->status == 'EN_EVALUACION' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $application->status == 'PRESENTADA' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $application->status == 'RECHAZADA' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $application->status == 'APTO' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $application->status == 'NO_APTO' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ !in_array($application->status, ['APROBADA', 'EN_EVALUACION', 'PRESENTADA', 'RECHAZADA', 'APTO', 'NO_APTO']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ str_replace('_', ' ', $application->status) }}
                                        </span>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Postulado: {{ $application->application_date->format('d/m/Y') }}
                                        </span>
                                        @if($application->final_score)
                                            <span class="flex items-center gap-1.5 font-semibold text-municipal-blue">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                Puntaje: {{ number_format($application->final_score, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-col gap-2 lg:min-w-[180px]">
                            <a href="{{ route('applicant.applications.show', $application->id) }}"
                               class="px-4 py-2 gradient-municipal text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 text-center text-sm">
                                Ver Detalles
                            </a>
                            @if(in_array($application->status, ['PRESENTADA', 'EN_REVISION']))
                                <form method="POST" action="{{ route('applicant.applications.withdraw', $application->id) }}"
                                      onsubmit="return confirm('¿Estás seguro de que deseas desistir de esta postulación?');">
                                    @csrf
                                    <button type="submit"
                                            class="w-full px-4 py-2 bg-red-100 text-red-800 font-semibold rounded-xl hover:bg-red-200 transition-all duration-300 text-sm">
                                        Desistir
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Paginación -->
    <div class="mt-8">
        {{ $applications->links() }}
    </div>
@else
    <!-- Estado vacío -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No tienes postulaciones aún</h3>
        <p class="text-gray-600 mb-6">Comienza a postular a las convocatorias disponibles</p>
        <a href="{{ route('applicant.job-postings.index') }}"
           class="inline-block px-6 py-3 gradient-municipal text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300">
            Ver Convocatorias
        </a>
    </div>
@endif

@endsection
