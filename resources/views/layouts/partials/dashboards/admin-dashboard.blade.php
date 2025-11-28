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

    {{-- Acciones Rápidas --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            Acciones Rápidas
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @can('user.create.user')
            <a href="{{ route('users.create') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Crear Usuario</h4>
                    <p class="text-xs text-gray-500">Agregar nuevo usuario</p>
                </div>
            </a>
            @endcan

            @can('jobposting.create.posting')
            <a href="{{ route('jobposting.create') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-green-600 transition-colors">Nueva Convocatoria</h4>
                    <p class="text-xs text-gray-500">Crear convocatoria CAS</p>
                </div>
            </a>
            @endcan

            @can('auth.create.role')
            <a href="{{ route('roles.create') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Crear Rol</h4>
                    <p class="text-xs text-gray-500">Nuevo rol del sistema</p>
                </div>
            </a>
            @endcan

            @can('organization.create.unit')
            <a href="{{ route('organizational-units.create') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Nueva Unidad</h4>
                    <p class="text-xs text-gray-500">Crear unidad organizacional</p>
                </div>
            </a>
            @endcan

            @can('jobprofile.create.request')
            <a href="{{ route('jobprofile.profiles.create') }}"
               class="group flex items-center p-4 border-2 border-gray-200 rounded-xl hover:border-pink-500 hover:bg-pink-50 transition-all">
                <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-pink-600 transition-colors">Perfil de Puesto</h4>
                    <p class="text-xs text-gray-500">Crear nuevo perfil</p>
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
                    <p class="text-xs text-gray-500">Ver mi información</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Estadísticas Adicionales --}}
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
