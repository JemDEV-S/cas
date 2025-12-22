v<!-- DASHBOARD CONTENT (dashboard.blade.php) -->
@extends('application::layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-8 py-10 sm:px-12 sm:py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">
                        ¡Bienvenido, {{ auth()->user()->name ?? 'Usuario' }}! 👋
                    </h1>
                    <p class="text-blue-100 text-lg">
                        Gestiona tus postulaciones y encuentra nuevas oportunidades laborales
                    </p>
                </div>
                <div class="hidden lg:block">
                    <svg class="w-32 h-32 text-white/20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card 1 -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Activas</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">5</h3>
            <p class="text-sm text-gray-600">Postulaciones activas</p>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Exitosas</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">12</h3>
            <p class="text-sm text-gray-600">Postulaciones aprobadas</p>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Pendientes</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">3</h3>
            <p class="text-sm text-gray-600">En evaluación</p>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Disponibles</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">24</h3>
            <p class="text-sm text-gray-600">Convocatorias abiertas</p>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Recent Applications -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Recent Applications Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Postulaciones Recientes</h2>
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center">
                    Ver todas
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                <!-- Application Item -->
                <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Analista de Sistemas</h3>
                            <p class="text-sm text-gray-600">Oficina de Tecnologías de la Información</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            En evaluación
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Hace 2 días
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Lima, Perú
                        </span>
                    </div>
                </div>

                <!-- Application Item 2 -->
                <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Especialista en Recursos Humanos</h3>
                            <p class="text-sm text-gray-600">Gerencia de Gestión del Talento</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Postulado
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Hace 5 días
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Cusco, Perú
                        </span>
                    </div>
                </div>

                <!-- Application Item 3 -->
                <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Asistente Administrativo</h3>
                            <p class="text-sm text-gray-600">Oficina General de Administración</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            Aprobado
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Hace 1 semana
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Arequipa, Perú
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h2>
            <div class="grid grid-cols-2 gap-4">
                <button class="flex items-center justify-center space-x-3 px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl hover:shadow-lg transition-all duration-300 hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="font-medium">Nueva Postulación</span>
                </button>
                <button class="flex items-center justify-center space-x-3 px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-xl hover:border-blue-600 hover:text-blue-600 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium">Actualizar Perfil</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column - Sidebar -->
    <div class="space-y-6">
        <!-- New Opportunities -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Nuevas Oportunidades</h2>
                <svg class="w-8 h-8 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <p class="text-blue-100 mb-6">Hay 8 nuevas convocatorias que coinciden con tu perfil</p>
            <button class="w-full bg-white text-blue-700 font-medium py-3 rounded-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                Ver Convocatorias
            </button>
        </div>

        <!-- Profile Completion -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Perfil Completado</h2>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-2xl font-bold text-blue-600">85%</span>
                    </div>
                </div>
                <div class="overflow-hidden h-3 mb-4 text-xs flex rounded-full bg-blue-100">
                    <div style="width: 85%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-blue-600 to-indigo-700 rounded-full transition-all duration-500"></div>
                </div>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Datos personales</span>
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Experiencia laboral</span>
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Formación académica</span>
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <button class="mt-6 w-full text-center text-sm font-medium text-blue-600 hover:text-blue-700 py-2 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                Completar perfil →
            </button>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Próximas Fechas</h2>
            <div class="space-y-4">
                <!-- Event Item -->
                <div class="flex items-start space-x-3 p-3 bg-red-50 rounded-lg border border-red-100">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-xs font-semibold text-red-600">DIC</span>
                            <span class="text-lg font-bold text-red-700">28</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Cierre de convocatoria</p>
                        <p class="text-xs text-gray-600 mt-1">Analista de Sistemas</p>
                    </div>
                </div>

                <!-- Event Item 2 -->
                <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-xs font-semibold text-blue-600">ENE</span>
                            <span class="text-lg font-bold text-blue-700">05</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Entrevista programada</p>
                        <p class="text-xs text-gray-600 mt-1">Recursos Humanos</p>
                    </div>
                </div>

                <!-- Event Item 3 -->
                <div class="flex items-start space-x-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-amber-100 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-xs font-semibold text-amber-600">ENE</span>
                            <span class="text-lg font-bold text-amber-700">12</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Resultado de evaluación</p>
                        <p class="text-xs text-gray-600 mt-1">Asistente Administrativo</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900">¿Necesitas ayuda?</h2>
            </div>
            <p class="text-sm text-gray-600 mb-4">Consulta nuestra guía de postulación o contacta con soporte.</p>
            <div class="space-y-2">
                <button class="w-full text-left px-4 py-2 bg-white rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center justify-between">
                    <span>Centro de ayuda</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button class="w-full text-left px-4 py-2 bg-white rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center justify-between">
                    <span>Contactar soporte</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection