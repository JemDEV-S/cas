{{-- Dashboard para Usuario de Área (solicita perfiles de puesto) --}}
<div class="space-y-6">

    {{-- Mis Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Perfiles Solicitados --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Perfiles Solicitados</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('requested_by', auth()->id())->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Total creados</div>
        </div>

        {{-- En Revisión --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">En Revisión</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('requested_by', auth()->id())->where('status', 'in_review')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Esperando aprobación</div>
        </div>

        {{-- Aprobados --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Aprobados</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('requested_by', auth()->id())->where('status', 'approved')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Listos para usar</div>
        </div>

        {{-- Requieren Modificación --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Modificar</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('requested_by', auth()->id())->where('status', 'modification_requested')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Requieren cambios</div>
        </div>
    </div>

    {{-- Acciones Rápidas --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>
            ¿Qué deseas hacer?
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @can('jobprofile.create.request')
            <a href="{{ route('jobprofile.profiles.create') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors">Solicitar Perfil</h4>
                    <p class="text-sm text-gray-500">Nuevo perfil de puesto</p>
                </div>
            </a>
            @endcan

            <a href="{{ route('jobprofile.index') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-green-600 transition-colors">Mis Solicitudes</h4>
                    <p class="text-sm text-gray-500">Ver perfiles solicitados</p>
                </div>
            </a>

            <a href="{{ route('profile.show') }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-purple-600 transition-colors">Mi Perfil</h4>
                    <p class="text-sm text-gray-500">Ver información personal</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Mis Perfiles Recientes --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            Solicitudes Recientes
        </h3>
        @php
            $recentProfiles = \Modules\JobProfile\Entities\JobProfile::where('requested_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        @endphp
        @if($recentProfiles->count() > 0)
        <div class="space-y-3">
            @foreach($recentProfiles as $profile)
            <a href="{{ route('jobprofile.profiles.show', $profile->id) }}"
               class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 hover:border-blue-300 transition-all group">
                <div class="flex items-center flex-1">
                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ Str::limit($profile->title, 50) }}</h4>
                        <p class="text-xs text-gray-600">{{ $profile->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $profile->status_badge }}">
                    {{ $profile->status_label }}
                </span>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-lg font-medium">No has solicitado perfiles aún</p>
            <p class="text-sm mt-2">Comienza creando tu primera solicitud</p>
            @can('jobprofile.create.request')
            <a href="{{ route('jobprofile.profiles.create') }}"
               class="inline-block mt-4 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                Crear Solicitud
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
