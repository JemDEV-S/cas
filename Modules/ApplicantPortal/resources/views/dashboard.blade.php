@extends('applicantportal::components.layouts.master')

@section('title', 'Portal del Postulante')

@section('content')

<!-- Mascota guía - Bienvenida -->
<div class="mb-8 relative">
    <div class="gradient-municipal rounded-3xl shadow-2xl overflow-hidden">
        <!-- Patrón decorativo andino -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <pattern id="andean-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M0,10 L5,5 L10,10 L5,15 Z M10,10 L15,5 L20,10 L15,15 Z" fill="currentColor"/>
                </pattern>
                <rect width="100" height="100" fill="url(#andean-pattern)"/>
            </svg>
        </div>
        
        <div class="relative px-6 py-8 sm:px-12 sm:py-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                <!-- Contenido principal -->
                <div class="flex-1 text-white">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            ¡Bienvenido/a, {{ auth()->user()->getFullNameAttribute() ?? 'Usuario' }}! 
                        </h1>
                    </div>
                    <p class="text-white/90 text-lg mb-4">
                        Tu portal para acceder a oportunidades laborales en la Municipalidad de San Jerónimo
                    </p>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <div class="flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <span>Convocatorias trimestrales</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            <span>Proceso transparente</span>
                        </div>
                    </div>
                </div>
                
                <!-- Mascota - Vicuñita Jerónimo -->
                <div class="flex-shrink-0">
                    <div class="relative">
                        <!-- Círculo decorativo -->
                        <div class="absolute -inset-4 bg-white/10 rounded-full blur-2xl"></div>
                        
                        <!-- Mascota SVG -->
                        <div class="relative float-animation">
                            <svg class="w-40 h-40 sm:w-48 sm:h-48" viewBox="0 0 200 200" fill="none">
                                <!-- Cuerpo de la vicuña -->
                                <ellipse cx="100" cy="120" rx="45" ry="55" fill="#F0C84F"/>
                                <!-- Cabeza -->
                                <circle cx="100" cy="70" r="30" fill="#F0C84F"/>
                                <!-- Orejas -->
                                <ellipse cx="85" cy="50" rx="8" ry="20" fill="#F0C84F" transform="rotate(-20 85 50)"/>
                                <ellipse cx="115" cy="50" rx="8" ry="20" fill="#F0C84F" transform="rotate(20 115 50)"/>
                                <!-- Ojos -->
                                <circle cx="90" cy="70" r="5" fill="#2C3E50"/>
                                <circle cx="110" cy="70" r="5" fill="#2C3E50"/>
                                <circle cx="92" cy="68" r="2" fill="white"/>
                                <circle cx="112" cy="68" r="2" fill="white"/>
                                <!-- Nariz -->
                                <ellipse cx="100" cy="82" rx="6" ry="4" fill="#E0A83E"/>
                                <!-- Sonrisa -->
                                <path d="M 92 86 Q 100 90 108 86" stroke="#2C3E50" stroke-width="2" fill="none" stroke-linecap="round"/>
                                <!-- Patas -->
                                <rect x="75" y="160" width="10" height="30" rx="5" fill="#F0C84F"/>
                                <rect x="95" y="160" width="10" height="30" rx="5" fill="#F0C84F"/>
                                <rect x="105" y="160" width="10" height="30" rx="5" fill="#F0C84F"/>
                                <rect x="125" y="160" width="10" height="30" rx="5" fill="#F0C84F"/>
                                <!-- Bufanda municipal (colores) -->
                                <path d="M 75 90 Q 80 100 100 98 Q 120 96 125 90" stroke="#3484A5" stroke-width="8" fill="none"/>
                                <path d="M 75 90 L 70 120" stroke="#2CA792" stroke-width="6" fill="none"/>
                                <path d="M 125 90 L 130 120" stroke="#2CA792" stroke-width="6" fill="none"/>
                                <!-- Brazo saludando -->
                                <g class="wave-animation">
                                    <ellipse cx="140" cy="110" rx="8" ry="25" fill="#F0C84F" transform="rotate(45 140 110)"/>
                                    <circle cx="150" cy="95" r="8" fill="#F0C84F"/>
                                </g>
                            </svg>
                        </div>
                        
                        <!-- Burbuja de diálogo -->
                        <div class="absolute -right-8 top-0 hidden lg:block">
                            <div class="bg-white rounded-2xl shadow-xl p-4 w-48 relative">
                                <div class="absolute -left-2 top-6 w-0 h-0 border-t-8 border-t-transparent border-r-8 border-r-white border-b-8 border-b-transparent"></div>
                                <p class="text-sm font-semibold text-gray-800 mb-1">¡Hola! Soy Jerónimo</p>
                                <p class="text-xs text-gray-600">Tu guía en el proceso de postulación 🦙</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Postulaciones Activas -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-municipal-blue group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-municipal-blue bg-blue-50 px-3 py-1.5 rounded-full">En proceso</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['active_applications'] ?? 0 }}</h3>
            <p class="text-sm text-gray-600 font-medium">Postulaciones activas</p>
            <div class="mt-4 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                @php
                    $total = $myApplications->count();
                    $percentage = $total > 0 ? ($stats['active_applications'] / $total) * 100 : 0;
                @endphp
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-400 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Postulaciones Aprobadas -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-municipal-green group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-green-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-municipal-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-municipal-green bg-green-50 px-3 py-1.5 rounded-full">¡Éxito!</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['approved_applications'] ?? 0 }}</h3>
            <p class="text-sm text-gray-600 font-medium">Postulaciones aprobadas</p>
            <div class="mt-4 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                @php
                    $percentage = $total > 0 ? ($stats['approved_applications'] / $total) * 100 : 0;
                @endphp
                <div class="h-full bg-gradient-to-r from-green-500 to-green-400 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- En Evaluación -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-municipal-yellow group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-100 to-yellow-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-municipal-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-yellow-700 bg-yellow-50 px-3 py-1.5 rounded-full">Espera</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['in_evaluation'] ?? 0 }}</h3>
            <p class="text-sm text-gray-600 font-medium">En evaluación</p>
            <div class="mt-4 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                @php
                    $percentage = $total > 0 ? ($stats['in_evaluation'] / $total) * 100 : 0;
                @endphp
                <div class="h-full bg-gradient-to-r from-yellow-500 to-yellow-400 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Convocatorias Disponibles -->
    <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-400 group">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-purple-50 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-purple-600 bg-purple-50 px-3 py-1.5 rounded-full">Nuevas</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['available_postings'] ?? 0 }}</h3>
            <p class="text-sm text-gray-600 font-medium">Convocatorias abiertas</p>
            <div class="mt-4 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 to-purple-400 rounded-full" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal en grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Columna izquierda - 2/3 -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Postulaciones recientes -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-municipal-soft">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 gradient-municipal rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">Tus Postulaciones Recientes</h2>
                    </div>
                    <a href="{{ route('applicant.applications.index') }}" class="text-sm font-semibold text-municipal-blue hover:text-municipal-green flex items-center gap-1 transition-colors">
                        Ver todas
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            @if($recentApplications && $recentApplications->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($recentApplications as $application)
                        <div class="p-6 hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-transparent transition-all duration-200 group">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 gradient-municipal rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-base font-bold text-gray-900 mb-1">{{ $application->jobProfile->position_code->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $application->jobProfile->requesting_unit->name }}</p>
                                        </div>
                                        <span class="ml-4 inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold
                                            {{ $application->status == 'EN_EVALUACION' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}
                                            {{ $application->status == 'PRESENTADA' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                                            {{ $application->status == 'APROBADA' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                                            {{ $application->status == 'APTO' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}">
                                            @if($application->status == 'EN_EVALUACION')
                                                <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                                            @elseif($application->status == 'APROBADA')
                                                <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                            @endif
                                            {{ str_replace('_', ' ', $application->status) }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $application->application_date->diffForHumans() }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            San Jerónimo, Cusco
                                        </span>
                                        @if($application->jobProfile->position_code->base_salary)
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                S/ {{ number_format($application->jobProfile->position_code->base_salary, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Aún no tienes postulaciones</h3>
                    <p class="text-gray-600 mb-4">Comienza a postular a las convocatorias disponibles</p>
                    <a href="{{ route('applicant.job-postings.index') }}" class="inline-flex items-center gap-2 px-6 py-3 gradient-municipal text-white font-bold rounded-xl hover:shadow-xl transition-all duration-300">
                        Ver Convocatorias
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
        </div>

        <!-- Acciones rápidas con Jerónimo -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 gradient-municipal rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Acciones Rápidas</h2>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button class="group relative overflow-hidden gradient-municipal text-white rounded-xl p-6 hover:shadow-2xl transition-all duration-300 hover:scale-105">
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <span class="font-bold text-base">Nueva Postulación</span>
                        <span class="text-xs text-white/80 mt-1">Aplica a convocatorias</span>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </button>

                <button class="group relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-200 text-gray-700 rounded-xl p-6 hover:border-municipal-blue hover:shadow-lg transition-all duration-300">
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-municipal-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-base">Actualizar Perfil</span>
                        <span class="text-xs text-gray-600 mt-1">Mantén tu CV al día</span>
                    </div>
                </button>

                <button class="group relative overflow-hidden bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-200 text-yellow-900 rounded-xl p-6 hover:border-municipal-yellow hover:shadow-lg transition-all duration-300">
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-municipal-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-base">Mis Documentos</span>
                        <span class="text-xs text-yellow-700 mt-1">CV, certificados, títulos</span>
                    </div>
                </button>

                <button class="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200 text-purple-900 rounded-xl p-6 hover:border-purple-400 hover:shadow-lg transition-all duration-300">
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="font-bold text-base">Mis Citas</span>
                        <span class="text-xs text-purple-700 mt-1">Entrevistas y pruebas</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Columna derecha - 1/3 -->
    <div class="space-y-6">
        <!-- Tarjeta de oportunidades con mascota -->
        <div class="relative gradient-municipal rounded-2xl shadow-xl overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
            
            <div class="relative p-6 text-white">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold mb-1">¡Nuevas Oportunidades!</h2>
                        <p class="text-white/90 text-sm">8 convocatorias coinciden con tu perfil</p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                
                <div class="space-y-2 mb-5">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>3 para profesionales</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>5 para técnicos</span>
                    </div>
                </div>
                
                <button class="w-full bg-white text-municipal-blue font-bold py-3 rounded-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-2">
                    <span>Explorar Convocatorias</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </div>
        </div>



        <!-- Próximas fechas importantes -->
        <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Fechas Importantes</h2>
            </div>
            
            <div class="space-y-3"> -->
                <!-- Fecha 1 - Urgente -->
                <!-- <div class="group p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-xl border-2 border-red-200 hover:shadow-lg transition-all duration-300">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex flex-col items-center justify-center shadow-md">
                                <span class="text-[10px] font-bold text-white/90">DIC</span>
                                <span class="text-xl font-black text-white">28</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <p class="text-sm font-bold text-gray-900">Cierre de convocatoria</p>
                                <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">¡3 días!</span>
                            </div>
                            <p class="text-xs text-gray-600">Analista de Sistemas - Plazo límite</p>
                        </div>
                    </div>
                </div> -->

                <!-- Fecha 2 -->
                <!-- <div class="group p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-2 border-blue-100 hover:border-blue-300 hover:shadow-lg transition-all duration-300">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 gradient-municipal rounded-xl flex flex-col items-center justify-center shadow-md">
                                <span class="text-[10px] font-bold text-white/90">ENE</span>
                                <span class="text-xl font-black text-white">05</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 mb-1">Entrevista programada</p>
                            <p class="text-xs text-gray-600">Especialista en RRHH - 10:00 AM</p>
                        </div>
                    </div>
                </div> -->

                <!-- Fecha 3 -->
                <!-- <div class="group p-4 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl border-2 border-amber-100 hover:border-amber-300 hover:shadow-lg transition-all duration-300">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex flex-col items-center justify-center shadow-md">
                                <span class="text-[10px] font-bold text-white/90">ENE</span>
                                <span class="text-xl font-black text-white">12</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 mb-1">Resultado de evaluación</p>
                            <p class="text-xs text-gray-600">Asistente Administrativo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Centro de ayuda con Jerónimo -->
        <div class="bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50 rounded-2xl shadow-sm border-2 border-purple-200 p-6">
            <div class="flex items-start gap-4 mb-4">
                <!-- Mini Jerónimo -->
                <div class="flex-shrink-0">
                    <svg class="w-16 h-16" viewBox="0 0 80 80" fill="none">
                        <circle cx="40" cy="30" r="15" fill="#F0C84F"/>
                        <ellipse cx="40" cy="50" rx="18" ry="22" fill="#F0C84F"/>
                        <circle cx="36" cy="30" r="2.5" fill="#2C3E50"/>
                        <circle cx="44" cy="30" r="2.5" fill="#2C3E50"/>
                        <path d="M 36 34 Q 40 36 44 34" stroke="#2C3E50" stroke-width="1.5" fill="none"/>
                        <ellipse cx="33" cy="22" rx="3" ry="8" fill="#F0C84F" transform="rotate(-15 33 22)"/>
                        <ellipse cx="47" cy="22" rx="3" ry="8" fill="#F0C84F" transform="rotate(15 47 22)"/>
                    </svg>
                </div>
                
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-900 mb-2">¿Necesitas ayuda, amigo/a?</h2>
                    <p class="text-sm text-gray-600 mb-4">Jerónimo está aquí para guiarte en tu postulación</p>
                </div>
            </div>
            
            <div class="space-y-2">
                <button class="w-full text-left px-4 py-3 bg-white rounded-xl text-sm font-semibold text-gray-700 hover:bg-purple-100 hover:text-purple-900 transition-all duration-200 flex items-center justify-between group shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Guía de postulación</span>
                    </div>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <button class="w-full text-left px-4 py-3 bg-white rounded-xl text-sm font-semibold text-gray-700 hover:bg-purple-100 hover:text-purple-900 transition-all duration-200 flex items-center justify-between group shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Preguntas frecuentes</span>
                    </div>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <button class="w-full text-left px-4 py-3 bg-white rounded-xl text-sm font-semibold text-gray-700 hover:bg-purple-100 hover:text-purple-900 transition-all duration-200 flex items-center justify-between group shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Contáctanos</span>
                    </div>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Animaciones CSS personalizadas -->
<style>
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes wave {
    0%, 100% {
        transform: rotate(45deg);
    }
    50% {
        transform: rotate(35deg);
    }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}

.wave-animation {
    transform-origin: 140px 110px;
    animation: wave 1s ease-in-out infinite;
}

/* Gradientes personalizados municipales */
.gradient-municipal {
    background: linear-gradient(135deg, #3484A5 0%, #2CA792 100%);
}

.gradient-municipal-soft {
    background: linear-gradient(135deg, rgba(52, 132, 165, 0.05) 0%, rgba(44, 167, 146, 0.05) 100%);
}

/* Colores municipales personalizados */
:root {
    --municipal-blue: #3484A5;
    --municipal-green: #2CA792;
    --municipal-yellow: #F59E0B;
}

.text-municipal-blue {
    color: var(--municipal-blue);
}

.text-municipal-green {
    color: var(--municipal-green);
}

.text-municipal-yellow {
    color: var(--municipal-yellow);
}

.bg-municipal-blue {
    background-color: var(--municipal-blue);
}

.bg-municipal-green {
    background-color: var(--municipal-green);
}

.border-municipal-blue {
    border-color: var(--municipal-blue);
}

.border-municipal-green {
    border-color: var(--municipal-green);
}

.border-municipal-yellow {
    border-color: var(--municipal-yellow);
}

/* Efectos hover mejorados */
.hover\:border-municipal-blue:hover {
    border-color: var(--municipal-blue);
}

.hover\:border-municipal-green:hover {
    border-color: var(--municipal-green);
}

.hover\:border-municipal-yellow:hover {
    border-color: var(--municipal-yellow);
}

.hover\:text-municipal-blue:hover {
    color: var(--municipal-blue);
}

.hover\:text-municipal-green:hover {
    color: var(--municipal-green);
}

.hover\:bg-municipal-blue:hover {
    background-color: var(--municipal-blue);
}

/* Sombras personalizadas */
.shadow-municipal {
    box-shadow: 0 10px 25px -5px rgba(52, 132, 165, 0.2), 0 8px 10px -6px rgba(44, 167, 146, 0.15);
}

/* Animación de entrada suave */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Pulso suave para elementos importantes */
@keyframes pulse-soft {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse-soft {
    animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

@endsection