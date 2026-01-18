@extends('applicantportal::components.layouts.master')

@section('title', $posting->title)

@section('content')

<!-- Breadcrumb -->
<nav class="mb-6 flex items-center text-sm text-gray-600">
    <a href="{{ route('applicant.dashboard') }}" class="hover:text-municipal-blue">Dashboard</a>
    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('applicant.job-postings.index') }}" class="hover:text-municipal-blue">Convocatorias</a>
    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-900 font-semibold">{{ $posting->code }}</span>
</nav>

@if($hasSubmittedApplication)
    <!-- Alerta de restricción: Ya postuló a un perfil en esta convocatoria -->
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 px-6 py-4 rounded-r-lg shadow-sm" role="alert">
        <div class="flex items-start">
            <svg class="w-6 h-6 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-bold">Ya postulaste a un perfil en esta convocatoria</p>
                <p class="text-sm mt-1">Solo puedes postular a un perfil por convocatoria. Si deseas cambiar tu postulación, debes desistir de la actual primero.</p>
            </div>
        </div>
    </div>
@endif

<!-- Header de convocatoria -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
    <div class="flex items-start justify-between mb-6">
        <div class="flex-1">
            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full mb-3">
                {{ $posting->code }}
            </span>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $posting->title }}</h1>
            <p class="text-gray-600 text-lg">{{ $posting->description }}</p>
        </div>
        @if($hasApplied)
            <div class="ml-6">
                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 font-bold rounded-xl gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Ya postulaste
                </span>
            </div>
        @endif
    </div>

    <!-- Información general -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 bg-gradient-municipal-soft rounded-xl">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 gradient-municipal rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">Fecha de Publicación</p>
                <p class="font-bold text-gray-900">{{ $posting->published_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total de Vacantes</p>
                <p class="font-bold text-gray-900">{{ $jobProfiles->sum('total_vacancies') }} posiciones</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">Fase Actual</p>
                <p class="font-bold text-municipal-green">{{ $currentPhase?->phase?->name ?? 'En proceso' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros dinámicos con Alpine.js -->
<div x-data="{
    search: '',
    profiles: @js($jobProfiles->toArray()),
    get filteredProfiles() {
        return this.profiles.filter(profile => {
            const searchMatch = !this.search ||
                profile.profile_name?.toLowerCase().includes(this.search.toLowerCase()) ||
                profile.position_code?.name?.toLowerCase().includes(this.search.toLowerCase()) ||
                profile.code?.toLowerCase().includes(this.search.toLowerCase()) ||
                profile.requesting_unit?.name?.toLowerCase().includes(this.search.toLowerCase());

            return searchMatch;
        });
    },
    get totalFiltered() {
        return this.filteredProfiles.length;
    }
}" class="mb-6">

    <!-- Barra de filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtrar Perfiles
            </h3>
            <span class="text-sm text-gray-600" x-text="`${totalFiltered} de ${profiles.length} perfiles`"></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Búsqueda por perfil/cargo/código/unidad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar por perfil, cargo, código o unidad
                </label>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Ej: Especialista en TI, Analista, Asistente, Gerencia..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-municipal-blue focus:border-transparent">
            </div>
        </div>
    </div>

    <!-- Perfiles/Puestos disponibles -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Perfiles Disponibles
            <span class="text-base font-normal text-gray-600" x-text="`(${totalFiltered})`"></span>
        </h2>

        <!-- Mensaje si no hay resultados -->
        <template x-if="filteredProfiles.length === 0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No se encontraron perfiles</h3>
                <p class="text-gray-600 mb-6">Intenta ajustar los filtros de búsqueda.</p>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6">
            <template x-for="profile in filteredProfiles" :key="profile.id">
                <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 hover:border-municipal-blue transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <!-- Header del perfil -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="profile.profile_name || 'Sin nombre'"></h3>
                                <p class="text-sm text-gray-600 mb-2" x-text="profile.position_code ? `${profile.position_code.name} - ${profile.position_code.code}` : 'Sin cargo especificado'"></p>
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full" x-text="profile.code"></span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Vacantes</p>
                                <p class="text-2xl font-bold text-municipal-blue" x-text="profile.total_vacancies"></p>
                            </div>
                        </div>

                        <!-- Unidad solicitante -->
                        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-600 mb-1">Unidad Solicitante</p>
                            <p class="font-semibold text-gray-900" x-text="profile.requesting_unit?.name || 'No especificada'"></p>
                        </div>

                        <!-- Requisitos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Formación Académica
                                </p>
                                <p class="text-sm text-gray-900 font-medium leading-relaxed" x-text="profile.formatted_education_levels || 'No especificado'"></p>
                                <template x-if="profile.career_field">
                                    <p class="text-sm text-gray-600 mt-2">
                                        <span class="font-semibold">Carrera:</span> <span x-text="profile.career_field"></span>
                                    </p>
                                </template>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Experiencia Requerida
                                </p>
                                <template x-if="profile.formatted_general_experience">
                                    <p class="text-sm text-gray-900 font-medium">
                                        <span class="text-gray-600">General:</span>
                                        <span x-text="profile.formatted_general_experience"></span>
                                    </p>
                                </template>
                                <template x-if="profile.formatted_specific_experience">
                                    <p class="text-sm text-gray-900 font-medium mt-1">
                                        <span class="text-gray-600">Específica:</span>
                                        <span x-text="profile.formatted_specific_experience"></span>
                                    </p>
                                </template>
                                <template x-if="!profile.formatted_general_experience && !profile.formatted_specific_experience">
                                    <p class="text-sm text-gray-600">No especificada</p>
                                </template>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Remuneración
                                </p>
                                <p class="text-lg font-bold text-municipal-green">S/ <span x-text="parseFloat(profile.position_code?.base_salary || 0).toFixed(2)"></span></p>
                                <p class="text-xs text-gray-500">Mensual</p>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Duración del Contrato
                                </p>
                                <p class="text-sm font-medium text-gray-900"><span x-text="profile.position_code?.contract_months || 0"></span> meses</p>
                                <p class="text-xs text-gray-500">Renovable según evaluación</p>
                            </div>
                        </div>

                        <!-- Botón de postulación -->
                        <div class="flex gap-3">
                            @foreach($jobProfiles as $prof)
                                <template x-if="profile.id === '{{ $prof->id }}'">
                                    <div class="flex-1 flex gap-3">
                                        @php
                                            $userHasAppliedToProfile = in_array($prof->id, $appliedProfileIds);
                                        @endphp

                                        @if($userHasAppliedToProfile)
                                            <span class="flex-1 px-6 py-3 bg-green-100 text-green-800 font-bold rounded-xl text-center flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Ya postulaste a este perfil
                                            </span>
                                        @elseif($hasSubmittedApplication)
                                            <span class="flex-1 px-6 py-3 bg-yellow-100 text-yellow-800 font-bold rounded-xl text-center flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Ya postulaste a otro perfil
                                            </span>
                                        @elseif($canApply)
                                            <a href="{{ route('applicant.job-postings.apply', [$posting->id, $prof->id]) }}"
                                               class="flex-1 px-6 py-3 gradient-municipal text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300 text-center">
                                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Postular a este perfil
                                            </a>
                                        @else
                                            <span class="flex-1 px-6 py-3 bg-gray-200 text-gray-600 font-bold rounded-xl text-center">
                                                Fuera de plazo de postulación
                                            </span>
                                        @endif

                                        <button type="button"
                                                @click="$dispatch('toggle-details', { id: '{{ $prof->id }}' })"
                                                class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all duration-300">
                                            Ver más detalles
                                            <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            @endforeach
                        </div>

                        <!-- Detalles expandibles -->
                        @foreach($jobProfiles as $prof)
                            <template x-if="profile.id === '{{ $prof->id }}'">
                                <div x-data="{ open: false }"
                                     @toggle-details.window="if ($event.detail.id === '{{ $prof->id }}') open = !open"
                                     x-show="open"
                                     x-transition
                                     class="mt-6 pt-6 border-t border-gray-200">
                                    @if($prof->required_courses)
                                        <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                                            <p class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Cursos Requeridos
                                            </p>
                                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-2">
                                                @foreach($prof->required_courses as $course)
                                                    <li class="leading-relaxed">{{ $course }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if($prof->knowledge_areas)
                                        <div class="mb-6 p-4 bg-indigo-50 rounded-xl">
                                            <p class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                </svg>
                                                Conocimientos Técnicos
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($prof->knowledge_areas as $knowledge)
                                                    <span class="px-3 py-1.5 bg-blue-100 text-blue-800 text-sm font-medium rounded-lg">{{ $knowledge }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($prof->required_competencies)
                                        <div class="p-4 bg-purple-50 rounded-xl">
                                            <p class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                                Competencias Requeridas
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($prof->required_competencies as $competency)
                                                    <span class="px-3 py-1.5 bg-purple-100 text-purple-800 text-sm font-medium rounded-lg">{{ $competency }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@endsection
