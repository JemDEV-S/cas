{{-- Dashboard para Jurado/Evaluador --}}
<div class="space-y-6">

    {{-- Mensaje de Bienvenida --}}
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Panel de Evaluación</h2>
                <p class="text-purple-100">
                    Aquí podrás gestionar las evaluaciones de postulantes asignadas
                </p>
            </div>
            <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <span class="text-3xl">📋</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Evaluaciones</div>
                    <div class="text-4xl font-bold text-gray-900">0</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Asignadas</div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg">
                    <span class="text-3xl">⏳</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Pendientes</div>
                    <div class="text-4xl font-bold text-gray-900">0</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Por completar</div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg">
                    <span class="text-3xl">✅</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Completadas</div>
                    <div class="text-4xl font-bold text-gray-900">0</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Finalizadas</div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                    <span class="text-3xl">⭐</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Puntaje Prom.</div>
                    <div class="text-4xl font-bold text-gray-900">0.0</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">De 100 puntos</div>
        </div>
    </div>

    {{-- Información --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <div class="text-center py-12 text-gray-500">
            <div class="text-6xl mb-4">🎯</div>
            <p class="text-lg font-medium">Módulo de Evaluación</p>
            <p class="text-sm mt-2">Próximamente podrás evaluar a los postulantes asignados</p>
        </div>
    </div>

    {{-- Acceso rápido --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('profile.show') }}"
           class="group flex items-center p-6 bg-white rounded-xl shadow-lg hover:shadow-xl border-2 border-gray-200 hover:border-purple-500 transition-all">
            <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="ml-4">
                <h4 class="text-lg font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Mi Perfil</h4>
                <p class="text-sm text-gray-500">Ver mi información personal</p>
            </div>
        </a>

        @can('jobposting.view.postings')
        <a href="{{ route('jobposting.list') }}"
           class="group flex items-center p-6 bg-white rounded-xl shadow-lg hover:shadow-xl border-2 border-gray-200 hover:border-blue-500 transition-all">
            <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="ml-4">
                <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Convocatorias</h4>
                <p class="text-sm text-gray-500">Ver convocatorias activas</p>
            </div>
        </a>
        @endcan
    </div>
</div>
