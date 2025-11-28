{{-- Dashboard para Usuario de Consulta (solo lectura) --}}
<div class="space-y-6">

    {{-- Mensaje de Bienvenida --}}
    <div class="bg-gradient-to-r from-gray-600 to-slate-700 rounded-2xl shadow-2xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Panel de Consulta</h2>
                <p class="text-gray-200">
                    Tienes acceso de solo lectura al sistema
                </p>
            </div>
            <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Estadísticas Generales del Sistema --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Total Convocatorias --}}
        @can('jobposting.view.postings')
        <a href="{{ route('jobposting.list') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Convocatorias</div>
                        <div class="text-4xl font-bold text-gray-900">
                            {{ \Modules\JobPosting\Entities\JobPosting::count() }}
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600">Total en el sistema</div>
            </div>
        </a>
        @endcan

        {{-- Perfiles de Puesto --}}
        @can('jobprofile.view.profiles')
        <a href="{{ route('jobprofile.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Perfiles</div>
                        <div class="text-4xl font-bold text-gray-900">
                            {{ \Modules\JobProfile\Entities\JobProfile::count() }}
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600">Perfiles de puesto</div>
            </div>
        </a>
        @endcan

        {{-- Unidades Organizacionales --}}
        @can('organization.view.units')
        <a href="{{ route('organizational-units.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Unidades Org.</div>
                        <div class="text-4xl font-bold text-gray-900">
                            {{ \Modules\Organization\Entities\OrganizationalUnit::count() }}
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600">En la estructura</div>
            </div>
        </a>
        @endcan

        {{-- Usuarios --}}
        @can('user.view.users')
        <a href="{{ route('users.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-5 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Usuarios</div>
                        <div class="text-4xl font-bold text-gray-900">
                            {{ \Modules\User\Entities\User::count() }}
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600">En el sistema</div>
            </div>
        </a>
        @endcan
    </div>

    {{-- Accesos Rápidos --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-gray-500 to-slate-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            Accesos Rápidos
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @can('jobposting.view.postings')
            <a href="{{ route('jobposting.list') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Ver Convocatorias</h4>
                    <p class="text-xs text-gray-500">Consultar listado</p>
                </div>
            </a>
            @endcan

            @can('jobprofile.view.profiles')
            <a href="{{ route('jobprofile.index') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-green-600 transition-colors">Ver Perfiles</h4>
                    <p class="text-xs text-gray-500">Perfiles de puesto</p>
                </div>
            </a>
            @endcan

            @can('organization.view.units')
            <a href="{{ route('organizational-units.index') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Ver Organización</h4>
                    <p class="text-xs text-gray-500">Estructura organizacional</p>
                </div>
            </a>
            @endcan

            @can('user.view.users')
            <a href="{{ route('users.index') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Ver Usuarios</h4>
                    <p class="text-xs text-gray-500">Listado de usuarios</p>
                </div>
            </a>
            @endcan

            <a href="{{ route('profile.show') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-teal-500 hover:bg-teal-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-teal-600 transition-colors">Mi Perfil</h4>
                    <p class="text-xs text-gray-500">Ver información</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Información de Solo Lectura --}}
    <div class="bg-gradient-to-r from-gray-100 to-slate-100 rounded-2xl p-6 border-2 border-gray-300">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-800 mb-2">Acceso de Solo Lectura</h3>
                <p class="text-sm text-gray-700">
                    Tu cuenta tiene permisos de consulta. Puedes ver la información del sistema pero no realizar modificaciones.
                    Si necesitas permisos adicionales, contacta al administrador.
                </p>
            </div>
        </div>
    </div>
</div>
