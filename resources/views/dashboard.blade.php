@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header Premium con Saludo Personalizado --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <span class="text-4xl font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-1">
                                @php
                                    $hour = now()->hour;
                                    if ($hour < 12) echo '¡Buenos días';
                                    elseif ($hour < 19) echo '¡Buenas tardes';
                                    else echo '¡Buenas noches';
                                @endphp, {{ auth()->user()->first_name }}!
                            </h1>
                            <p class="text-blue-100 text-lg">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</p>
                        </div>
                    </div>
                    <div class="text-right text-white">
                        <div class="text-sm text-blue-100">Último acceso</div>
                        <div class="text-lg font-semibold">
                            {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Primera vez' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dashboard Dinámico según Rol --}}
        @php
            $userRole = auth()->user()->roles->first()?->code;
        @endphp

        {{-- SUPER ADMIN / ADMIN GENERAL / ADMIN RRHH - Dashboard Completo --}}
        @if(in_array($userRole, ['admin_general', 'admin_rrhh']))
            @include('layouts.partials.dashboards.admin-dashboard')

        {{-- USUARIO DE ÁREA - Dashboard de Solicitudes --}}
        @elseif($userRole === 'usuario_area')
            @include('layouts.partials.dashboards.area-user-dashboard')

        {{-- REVISOR RRHH - Dashboard de Revisión --}}
        @elseif($userRole === 'revisor_rrhh')
            @include('layouts.partials.dashboards.reviewer-dashboard')

        {{-- JURADO/EVALUADOR - Dashboard de Evaluaciones --}}
        @elseif($userRole === 'jurado')
            @include('layouts.partials.dashboards.jury-dashboard')

        {{-- CONSULTA - Dashboard de Solo Lectura --}}
        @elseif($userRole === 'consulta')
            @include('layouts.partials.dashboards.readonly-dashboard')

        {{-- FALLBACK - Dashboard Genérico --}}
        @else
            @include('layouts.partials.dashboards.generic-dashboard')
        @endif

    </div>
</div>
@endsection
