
<nav class="bg-white shadow-md sticky top-0 z-50 border-b border-gray-100" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-xl">CAS</span>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 bg-clip-text text-transparent hidden sm:block">
                            Sistema CAS
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-2" x-data="{ open: null }">
                    <!-- Dashboard - Todos -->
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all
                              {{ request()->routeIs('dashboard')
                                  ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md'
                                  : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Convocatorias - Para quienes tienen permisos -->
                    @if(auth()->user()->hasAnyPermission(['jobposting.view.postings', 'jobposting.create.posting']))
                    <div class="relative" @mouseenter="open = 'convocatorias'" @mouseleave="open = null">
                        <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all
                                     {{ request()->routeIs('jobposting.*')
                                         ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md'
                                         : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Convocatorias
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open === 'convocatorias'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-gray-200 overflow-hidden"
                             style="display: none;">
                            <div class="py-1">
                                @can('jobposting.view.postings')
                                <a href="{{ route('jobposting.dashboard') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-blue-500">Dashboard</span>
                                </a>
                                @endcan

                                @can('jobposting.view.postings')
                                <a href="{{ route('jobposting.list') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-green-500">Ver Todas</span>
                                </a>
                                @endcan

                                @can('jobposting.create.posting')
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="{{ route('jobposting.create') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-purple-500">Nueva Convocatoria</span>
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Perfiles de Puesto -->
                    @if(auth()->user()->hasAnyPermission(['jobprofile.view.profiles', 'jobprofile.create.request']))
                    <div class="relative" @mouseenter="open = 'perfiles'" @mouseleave="open = null">
                        <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all
                                     {{ request()->routeIs('jobprofile.*')
                                         ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-md'
                                         : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Perfiles
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open === 'perfiles'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute left-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-gray-200 overflow-hidden"
                             style="display: none;">
                            <div class="py-1">
                                @can('jobprofile.view.profiles')
                                <a href="{{ route('jobprofile.index') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-purple-500">Ver Perfiles</span>
                                </a>
                                @endcan

                                @can('jobprofile.create.request')
                                <a href="{{ route('jobprofile.profiles.create') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-blue-500">Solicitar Perfil</span>
                                </a>
                                @endcan

                                @can('jobprofile.review.profiles')
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="{{ route('jobprofile.review.index') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-amber-500">Revisar Perfiles</span>
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Organización -->
                    @can('organization.view.units')
                    <a href="{{ route('organizational-units.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all
                              {{ request()->routeIs('organizational-units.*')
                                  ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md'
                                  : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Organización
                    </a>
                    @endcan

                    <!-- Usuarios -->
                    @can('user.view.users')
                    <div class="relative" @mouseenter="open = 'usuarios'" @mouseleave="open = null">
                        <button class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-all
                                     {{ request()->routeIs('users.*') || request()->routeIs('roles.*')
                                         ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md'
                                         : 'text-gray-700 hover:bg-gray-100' }}">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Usuarios
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open === 'usuarios'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute left-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-gray-200 overflow-hidden"
                             style="display: none;">
                            <div class="py-1">
                                <a href="{{ route('users.index') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-blue-500">Ver Usuarios</span>
                                </a>

                                @can('user.create.user')
                                <a href="{{ route('users.create') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-green-500">Nuevo Usuario</span>
                                </a>
                                @endcan

                                @can('auth.view.roles')
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="{{ route('roles.index') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-purple-500">Roles</span>
                                </a>
                                @endcan

                                @can('auth.view.permissions')
                                <a href="{{ route('permissions.index') }}"
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-amber-50 hover:to-orange-50 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    <span class="font-medium group-hover:text-amber-500">Permisos</span>
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan
                    @can('jobprofile.view.profiles')
                    <x-nav-link :href="route('jobprofile.index')" :active="request()->routeIs('jobprofiles.*')">
                        {{ __('Perfiles de Puesto') }}
                    </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- User Menu -->
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <!-- Notifications -->
                <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-all relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- User Dropdown -->
                <div class="ml-3 relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-sm font-bold text-gray-900">{{ auth()->user()->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</div>
                        </div>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-gray-200 overflow-hidden"
                         style="display: none;">
                        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <p class="text-sm font-bold text-gray-900">{{ auth()->user()->full_name }}</p>
                            <p class="text-xs text-gray-600">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile.show') }}"
                               class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all group">
                                <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="font-medium group-hover:text-blue-500">Mi Perfil</span>
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all group">
                                <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span class="font-medium group-hover:text-green-500">Editar Perfil</span>
                            </a>
                            <a href="{{ route('profile.preferences') }}"
                               class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all group">
                                <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-medium group-hover:text-purple-500">Preferencias</span>
                            </a>
                        </div>
                        <div class="border-t border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center w-full px-4 py-3 text-sm text-red-500 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-100 transition-all group">
                                    <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <span class="font-medium">Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="flex items-center sm:hidden">
                <button @click="mobileOpen = !mobileOpen"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu (Se muestra solo en móvil) -->
    <div x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="sm:hidden bg-white border-t border-gray-200"
         style="display: none;">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="block px-4 py-3 text-base font-medium text-center {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-500 border-l-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50' }}">
                Dashboard
            </a>

            @can('jobposting.view.postings')
            <a href="{{ route('jobposting.list') }}"
               class="block px-4 py-3 text-base font-medium text-center {{ request()->routeIs('jobposting.*') ? 'bg-green-50 text-green-500 border-l-4 border-green-500' : 'text-gray-700 hover:bg-gray-50' }}">
                Convocatorias
            </a>
            @endcan

            @can('organization.view.units')
            <a href="{{ route('organizational-units.index') }}"
               class="block px-4 py-3 text-base font-medium text-center {{ request()->routeIs('organizational-units.*') ? 'bg-amber-50 text-amber-500 border-l-4 border-amber-500' : 'text-gray-700 hover:bg-gray-50' }}">
                Organización
            </a>
            @endcan

            @can('user.view.users')
            <a href="{{ route('users.index') }}"
               class="block px-4 py-3 text-base font-medium text-center {{ request()->routeIs('users.*') ? 'bg-indigo-50 text-indigo-500 border-l-4 border-indigo-500' : 'text-gray-700 hover:bg-gray-50' }}">
                Usuarios
            </a>
            @endcan
        </div>

        <!-- Mobile User Menu -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center shadow-md">
                    <span class="text-white font-bold">
                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                    </span>
                </div>
                <div class="ml-3">
                    <div class="text-base font-bold text-gray-900">{{ auth()->user()->full_name }}</div>
                    <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-base font-medium text-center text-gray-700 hover:bg-gray-50">Mi Perfil</a>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-base font-medium text-center text-gray-700 hover:bg-gray-50">Editar Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-center px-4 py-2 text-base font-medium text-red-500 hover:bg-red-50">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
