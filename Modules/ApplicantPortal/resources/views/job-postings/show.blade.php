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
            <!-- Búsqueda por cargo/código/unidad -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar por cargo, código o unidad
                </label>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Ej: Analista, Asistente, Gerencia..."
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
                                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="profile.position_code?.name || 'Sin nombre'"></h3>
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
                                <p class="text-sm font-semibold text-gray-700 mb-2">📚 Formación Académica</p>
                                <p class="text-sm text-gray-600" x-text="profile.education_level || 'No especificado'"></p>
                                <template x-if="profile.career_field">
                                    <p class="text-sm text-gray-600 mt-1">Carrera: <span x-text="profile.career_field"></span></p>
                                </template>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">💼 Experiencia Requerida</p>
                                <template x-if="profile.general_experience_years > 0">
                                    <p class="text-sm text-gray-600">General: <span x-text="profile.general_experience_years"></span> años</p>
                                </template>
                                <template x-if="profile.specific_experience_years > 0">
                                    <p class="text-sm text-gray-600">Específica: <span x-text="profile.specific_experience_years"></span> años</p>
                                </template>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">💰 Remuneración</p>
                                <p class="text-lg font-bold text-municipal-green">S/ <span x-text="parseFloat(profile.position_code?.base_salary || 0).toFixed(2)"></span></p>
                                <p class="text-xs text-gray-500">Mensual</p>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">📅 Duración del Contrato</p>
                                <p class="text-sm text-gray-600"><span x-text="profile.position_code?.contract_months || 0"></span> meses</p>
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
                                        <div class="mb-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">📝 Cursos Requeridos</p>
                                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                                @foreach($prof->required_courses as $course)
                                                    <li>{{ $course }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if($prof->knowledge_areas)
                                        <div class="mb-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">🎯 Conocimientos Técnicos</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($prof->knowledge_areas as $knowledge)
                                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">{{ $knowledge }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($prof->required_competencies)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 mb-2">✨ Competencias Requeridas</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($prof->required_competencies as $competency)
                                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">{{ $competency }}</span>
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
