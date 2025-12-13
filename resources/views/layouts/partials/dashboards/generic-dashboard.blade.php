{{-- Dashboard para Administradores (admin_general, admin_rrhh) --}}
<div class="space-y-6">

    {{-- KPIs Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Usuarios --}}
        @can('user.view.users')
        <a href="{{ route('users.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total Usuarios</div>
                        <div class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            {{ \Modules\User\Entities\User::count() }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">En el sistema</span>
                    <span class="text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Gestionar →</span>
                </div>
            </div>
        </a>
        @endcan

        {{-- Total Roles --}}
        @can('auth.view.roles')
        <a href="{{ route('roles.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Roles Activos</div>
                        <div class="text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                            {{ \Modules\Auth\Entities\Role::where('is_active', true)->count() }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Configurados</span>
                    <span class="text-green-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Gestionar →</span>
                </div>
            </div>
        </a>
        @endcan

        {{-- Unidades Organizacionales --}}
        @can('organization.view.units')
        <a href="{{ route('organizational-units.index') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Unidades Org.</div>
                        <div class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-purple-800 bg-clip-text text-transparent">
                            {{ \Modules\Organization\Entities\OrganizationalUnit::count() }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Estructuradas</span>
                    <span class="text-purple-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver →</span>
                </div>
            </div>
        </a>
        @endcan

        {{-- Convocatorias Activas --}}
        @can('jobposting.view.postings')
        <a href="{{ route('jobposting.dashboard') }}"
           class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform animate-pulse">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-500 mb-1">Convocatorias</div>
                        <div class="text-4xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                            {{ \Modules\JobPosting\Entities\JobPosting::whereIn('status', ['PUBLICADA', 'EN_PROCESO'])->count() }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Activas</span>
                    <span class="text-amber-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver →</span>
                </div>
            </div>
        </a>
        @endcan
    </div>

    {{-- MÓDULOS DEL SISTEMA - Acceso Rápido --}}
    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-xl p-6">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl mr-3 shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </span>
            Módulos del Sistema
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- MÓDULO: CONVOCATORIAS --}}
            @can('jobposting.view.postings')
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-amber-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        Convocatorias
                    </h4>
                    <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-semibold">JobPosting</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('jobposting.dashboard') }}" class="flex items-center px-3 py-2 text-sm bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('jobposting.index') }}" class="flex items-center px-3 py-2 text-sm bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Ver Todas
                    </a>
                    @can('jobposting.create.posting')
                    <a href="{{ route('jobposting.create') }}" class="flex items-center px-3 py-2 text-sm bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva
                    </a>
                    @endcan

                </div>
            </div>
            @endcan

            {{-- MÓDULO: JURADOS --}}
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </span>
                        Jurados
                    </h4>
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-semibold">Jury</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('jury-members.index') }}" class="flex items-center px-3 py-2 text-sm bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Miembros
                    </a>
                    <a href="{{ route('jury-members.create') }}" class="flex items-center px-3 py-2 text-sm bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Nuevo Jurado
                    </a>
                    <a href="{{ route('jury-assignments.index') }}" class="flex items-center px-3 py-2 text-sm bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Asignaciones
                    </a>
                    <a href="{{ route('jury-conflicts.index') }}" class="flex items-center px-3 py-2 text-sm bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Conflictos
                    </a>
                </div>
            </div>

            {{-- MÓDULO: EVALUACIONES --}}
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Evaluaciones
                    </h4>
                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">Evaluation</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('evaluation.index') }}" class="flex items-center px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Ver Todas
                    </a>
                    <a href="{{ route('evaluation.my-evaluations') }}" class="flex items-center px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mis Evaluaciones
                    </a>
                    @can('assign-evaluators')
                    <a href="{{ route('evaluator-assignments.index') }}" class="flex items-center px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Asignaciones
                    </a>
                    @endcan
                    @can('manage-criteria')
                    <a href="{{ route('evaluation-criteria.index') }}" class="flex items-center px-3 py-2 text-sm bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Criterios
                    </a>
                    @endcan
                </div>
            </div>

            {{-- MÓDULO: POSTULACIONES --}}
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Postulaciones
                    </h4>
                    <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">Application</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="#" class="flex items-center px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Ver Todas
                    </a>
                    <a href="#" class="flex items-center px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filtrar
                    </a>
                    <a href="#" class="flex items-center px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Reportes
                    </a>
                    <a href="#" class="flex items-center px-3 py-2 text-sm bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        Exportar
                    </a>
                </div>
            </div>

            {{-- MÓDULO: USUARIOS & ROLES --}}
            @can('user.view.users')
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </span>
                        Usuarios & Roles
                    </h4>
                    <span class="text-xs bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-semibold">User/Auth</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Usuarios
                    </a>
                    @can('user.create.user')
                    <a href="{{ route('users.create') }}" class="flex items-center px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Nuevo Usuario
                    </a>
                    @endcan
                    @can('auth.view.roles')
                    <a href="{{ route('roles.index') }}" class="flex items-center px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Roles
                    </a>
                    @endcan
                    @can('auth.view.permissions')
                    <a href="{{ route('permissions.index') }}" class="flex items-center px-3 py-2 text-sm bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Permisos
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

            {{-- MÓDULO: ORGANIZACIÓN --}}
            @can('organization.view.units')
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-teal-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </span>
                        Organización
                    </h4>
                    <span class="text-xs bg-teal-100 text-teal-700 px-3 py-1 rounded-full font-semibold">Organization</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('organizational-units.index') }}" class="flex items-center px-3 py-2 text-sm bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Unidades
                    </a>
                    @can('organization.create.unit')
                    <a href="{{ route('organizational-units.create') }}" class="flex items-center px-3 py-2 text-sm bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva Unidad
                    </a>
                    @endcan
                    <a href="{{ route('organizational-units.tree') }}" class="flex items-center px-3 py-2 text-sm bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Organigrama
                    </a>
                    <a href="#" class="flex items-center px-3 py-2 text-sm bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Reportes
                    </a>
                </div>
            </div>
            @endcan

            {{-- MÓDULO: PERFILES DE PUESTO --}}
            @if(auth()->user()->hasAnyPermission(['jobprofile.view.profiles','jobprofile.view.own','jobprofile.create.profile']))
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-pink-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Perfiles de Puesto
                    </h4>
                    <span class="text-xs bg-pink-100 text-pink-700 px-3 py-1 rounded-full font-semibold">JobProfile</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @can('jobprofile.view.profiles')
                    <a href="{{ route('jobprofile.index') }}" class="flex items-center px-3 py-2 text-sm bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Ver Todos
                    </a>
                    @elsecan('jobprofile.view.own')
                    <a href="{{ route('jobprofile.index') }}" class="flex items-center px-3 py-2 text-sm bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mis Perfiles
                    </a>
                    @endcan
                    @can('jobprofile.create.profile')
                    <a href="{{ route('jobprofile.profiles.create') }}" class="flex items-center px-3 py-2 text-sm bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Solicitar
                    </a>
                    @endcan
                    @can('jobprofile.review.profile')
                    <a href="{{ route('jobprofile.review.index') }}" class="flex items-center px-3 py-2 text-sm bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Revisar
                    </a>
                    @endcan
                    @if(auth()->user()->can('viewAny', \Modules\JobProfile\Entities\PositionCode::class))
                    <a href="{{ route('jobprofile.positions.index') }}" class="flex items-center px-3 py-2 text-sm bg-pink-50 hover:bg-pink-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Códigos
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- MÓDULO: DOCUMENTOS --}}
            @if(auth()->user()->hasAnyPermission(['documents.view.documents','documents.sign']))
            <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-cyan-500">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center mr-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        Documentos
                    </h4>
                    <span class="text-xs bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full font-semibold">Documents</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('documents.index') }}" class="flex items-center px-3 py-2 text-sm bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Ver Todos
                    </a>
                    <a href="{{ route('documents.pending-signatures') }}" class="flex items-center px-3 py-2 text-sm bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Pendientes
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Estadísticas y Actividad --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Convocatorias por Estado --}}
        @can('jobposting.view.postings')
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg mr-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
                Convocatorias por Estado
            </h3>
            <div class="space-y-3">
                @php
                    $statuses = [
                        'BORRADOR' => ['count' => \Modules\JobPosting\Entities\JobPosting::where('status', 'BORRADOR')->count(), 'color' => 'gray'],
                        'PUBLICADA' => ['count' => \Modules\JobPosting\Entities\JobPosting::where('status', 'PUBLICADA')->count(), 'color' => 'blue'],
                        'EN_PROCESO' => ['count' => \Modules\JobPosting\Entities\JobPosting::where('status', 'EN_PROCESO')->count(), 'color' => 'yellow'],
                        'FINALIZADA' => ['count' => \Modules\JobPosting\Entities\JobPosting::where('status', 'FINALIZADA')->count(), 'color' => 'green'],
                        'CANCELADA' => ['count' => \Modules\JobPosting\Entities\JobPosting::where('status', 'CANCELADA')->count(), 'color' => 'red'],
                    ];
                @endphp
                @foreach($statuses as $status => $data)
                <div class="flex items-center justify-between p-3 bg-{{ $data['color'] }}-50 rounded-lg border border-{{ $data['color'] }}-200">
                    <span class="text-sm font-medium text-{{ $data['color'] }}-800">{{ ucfirst(strtolower(str_replace('_', ' ', $status))) }}</span>
                    <span class="px-3 py-1 bg-{{ $data['color'] }}-600 text-white text-sm font-bold rounded-full">{{ $data['count'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endcan

        {{-- Actividad Reciente --}}
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <span class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg mr-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                Actividad Reciente
            </h3>
            <div class="space-y-3">
                <div class="flex items-center p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-sm">
                                {{ substr(auth()->user()->first_name, 0, 1) }}{{ substr(auth()->user()->last_name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-bold text-gray-900">Iniciaste sesión</p>
                        <p class="text-xs text-gray-600">
                            {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Ahora' }}
                        </p>
                    </div>
                    <div class="text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                @can('jobposting.view.postings')
                @php
                    $recentPostings = \Modules\JobPosting\Entities\JobPosting::orderBy('created_at', 'desc')->take(3)->get();
                @endphp
                @foreach($recentPostings as $posting)
                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ Str::limit($posting->title, 30) }}</p>
                        <p class="text-xs text-gray-600">{{ $posting->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $posting->status->badgeClass() }}">
                        {{ $posting->status->label() }}
                    </span>
                </div>
                @endforeach
                @endcan
            </div>
        </div>
    </div>
</div>
