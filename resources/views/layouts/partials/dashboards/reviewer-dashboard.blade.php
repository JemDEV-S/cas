{{-- Dashboard para Revisor RRHH (valida perfiles) --}}
<div class="space-y-6">

    {{-- Estadísticas de Revisión --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Pendientes de Revisión --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg animate-pulse">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Pendientes</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('status', 'in_review')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Por revisar</div>
        </div>

        {{-- Revisados Hoy --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Revisados Hoy</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('reviewed_by', auth()->id())
                            ->whereDate('reviewed_at', today())->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Completados</div>
        </div>

        {{-- Total Aprobados --}}
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
                        {{ \Modules\JobProfile\Entities\JobProfile::where('reviewed_by', auth()->id())
                            ->where('status', 'approved')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Por mí</div>
        </div>

        {{-- Modificaciones Solicitadas --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-500 mb-1">Modificaciones</div>
                    <div class="text-4xl font-bold text-gray-900">
                        {{ \Modules\JobProfile\Entities\JobProfile::where('status', 'modification_requested')->count() }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-gray-600">Solicitadas</div>
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
            Panel de Revisión
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('jobprofile.review.index') }}"
               class="group flex items-center p-5 border-2 border-amber-300 bg-amber-50 rounded-xl hover:border-amber-500 hover:bg-amber-100 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-amber-900">⚡ Revisar Perfiles</h4>
                    <p class="text-sm text-amber-700">Pendientes de validación</p>
                </div>
            </a>

            <a href="{{ route('jobprofile.index', ['status' => 'approved']) }}"
               class="group flex items-center p-5 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all">
                <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-base font-bold text-gray-900 group-hover:text-green-600 transition-colors">Perfiles Aprobados</h4>
                    <p class="text-sm text-gray-500">Ver validados</p>
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
                    <p class="text-sm text-gray-500">Ver información</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Perfiles Pendientes de Revisión --}}
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl mr-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            Pendientes de Revisión
        </h3>
        @php
            $pendingProfiles = \Modules\JobProfile\Entities\JobProfile::with(['requestedBy', 'organizationalUnit'])
                ->where('status', 'in_review')
                ->orderBy('requested_at', 'asc')
                ->take(5)
                ->get();
        @endphp
        @if($pendingProfiles->count() > 0)
        <div class="space-y-3">
            @foreach($pendingProfiles as $profile)
            <a href="{{ route('jobprofile.review.show', $profile->id) }}"
               class="flex items-center justify-between p-4 bg-amber-50 hover:bg-amber-100 rounded-xl border-2 border-amber-200 hover:border-amber-400 transition-all group">
                <div class="flex items-center flex-1">
                    <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-bold text-gray-900 group-hover:text-amber-700 transition-colors">{{ Str::limit($profile->title, 50) }}</h4>
                        <p class="text-xs text-gray-600">Solicitado por: {{ $profile->requestedBy->full_name ?? 'N/A' }} • {{ $profile->requested_at ? $profile->requested_at->diffForHumans() : 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-200 text-amber-800">
                        {{ $profile->status_label }}
                    </span>
                    <svg class="w-5 h-5 text-amber-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <div class="text-6xl mb-4">✅</div>
            <p class="text-lg font-medium">¡Todo al día!</p>
            <p class="text-sm mt-2">No hay perfiles pendientes de revisión</p>
        </div>
        @endif
    </div>
</div>
