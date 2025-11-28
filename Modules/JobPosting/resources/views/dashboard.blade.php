@extends('layouts.admin')

@section('title', 'Dashboard de Convocatorias')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">

{{-- Botones Flotantes de Acción Rápida - DESPLIEGUE SUAVE --}}
<div class="fixed right-8 bottom-8 z-50 flex flex-col space-y-4">
    @can('jobposting.create.posting')
    <a href="{{ route('jobposting.create') }}"
       class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full shadow-xl hover:shadow-2xl transition-all duration-500">
        {{-- Icono con animación --}}
        <div class="transform transition-transform duration-500 group-hover:rotate-90">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </div>

        {{-- Tooltip con efecto de deslizamiento --}}
        <div class="absolute right-full mr-4">
            <div class="bg-gray-900/95 backdrop-blur-sm text-white text-sm font-medium px-4 py-2 rounded-xl whitespace-nowrap transform translate-x-8 scale-95 opacity-0 group-hover:translate-x-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-500 shadow-xl border border-gray-700">
                Nueva Convocatoria
                {{-- Triángulo indicador --}}
                <div class="absolute top-1/2 -right-2 transform -translate-y-1/2 w-3 h-3 bg-gray-900/95 rotate-45 border-r border-b border-gray-700"></div>
            </div>
        </div>
    </a>
    @endcan

    <a href="{{ route('jobposting.list') }}"
       class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full shadow-xl hover:shadow-2xl transition-all duration-500">
        {{-- Icono --}}
        <svg class="w-6 h-6 transform transition-transform duration-500 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>

        {{-- Tooltip con efecto de deslizamiento --}}
        <div class="absolute right-full mr-4">
            <div class="bg-gray-900/95 backdrop-blur-sm text-white text-sm font-medium px-4 py-2 rounded-xl whitespace-nowrap transform translate-x-8 scale-95 opacity-0 group-hover:translate-x-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-500 shadow-xl border border-gray-700">
                Ver Todas las Convocatorias
                <div class="absolute top-1/2 -right-2 transform -translate-y-1/2 w-3 h-3 bg-gray-900/95 rotate-45 border-r border-b border-gray-700"></div>
            </div>
        </div>
    </a>

    <button onclick="window.location.reload()"
       class="group relative flex items-center justify-center w-14 h-14 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-full shadow-xl hover:shadow-2xl transition-all duration-500">
        {{-- Icono con animación de rotación --}}
        <svg class="w-6 h-6 transform transition-transform duration-500 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>

        {{-- Tooltip con efecto de deslizamiento --}}
        <div class="absolute right-full mr-4">
            <div class="bg-gray-900/95 backdrop-blur-sm text-white text-sm font-medium px-4 py-2 rounded-xl whitespace-nowrap transform translate-x-8 scale-95 opacity-0 group-hover:translate-x-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-500 shadow-xl border border-gray-700">
                Actualizar Página
                <div class="absolute top-1/2 -right-2 transform -translate-y-1/2 w-3 h-3 bg-gray-900/95 rotate-45 border-r border-b border-gray-700"></div>
            </div>
        </div>
    </button>
</div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header Premium --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-12">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-1">Dashboard de Convocatorias</h1>
                            <p class="text-blue-100 text-lg">Vista Ejecutiva - Año {{ $currentYear }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('jobposting.list') }}"
                           class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl font-semibold hover:bg-white/20 transition-all shadow-lg">
                            📋 Ver Listado
                        </a>
                        @can('jobposting.create.posting')
                        <a href="{{ route('jobposting.create') }}"
                           class="px-6 py-3 bg-white text-indigo-600 rounded-2xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl">
                            ➕ Nueva Convocatoria
                        </a>
                        @endcan
                    </div>
                </div>

                {{-- Selector de Año Mejorado --}}
                <form method="GET" action="{{ route('jobposting.dashboard') }}" class="flex items-center space-x-4 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20">
                    <label class="text-white font-semibold">📅 Filtrar por año:</label>
                    <select name="year"
                            onchange="this.form.submit()"
                            class="px-4 py-2 bg-white/90 border-0 rounded-xl text-gray-900 font-medium focus:ring-2 focus:ring-white/50 cursor-pointer">
                        @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>
                            {{ $year }} {{ $year == date('Y') ? '(Actual)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- KPIs Principales - CLICKEABLES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total - Clickeable a listado completo --}}
            <a href="{{ route('jobposting.list') }}"
            class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">📋</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-500 mb-1">Total Convocatorias</div>
                            <div class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                {{ $statistics['total'] }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Año {{ $currentYear }}</span>
                        <span class="text-blue-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver todas →</span>
                    </div>
                </div>
            </a>

            {{-- Activas - Clickeable a activas --}}
            <a href="{{ route('jobposting.list', ['status' => 'PUBLICADA']) }}"
            class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg animate-pulse group-hover:scale-110 transition-transform">
                            <span class="text-3xl">⚡</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-500 mb-1">Activas</div>
                            <div class="text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                                {{ $statistics['activas'] }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">En curso actualmente</span>
                        <span class="text-green-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver →</span>
                    </div>
                </div>
            </a>

            {{-- Finalizadas - Clickeable --}}
            <a href="{{ route('jobposting.list', ['status' => 'FINALIZADA']) }}"
            class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">✅</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-500 mb-1">Finalizadas</div>
                            <div class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-purple-800 bg-clip-text text-transparent">
                                {{ $statistics['por_estado']['finalizadas'] }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Completadas con éxito</span>
                        <span class="text-purple-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver →</span>
                    </div>
                </div>
            </a>

            {{-- En Proceso --}}
            <a href="{{ route('jobposting.list', ['status' => 'EN_PROCESO']) }}"
            class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-1">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">⚙️</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-500 mb-1">En Proceso</div>
                            <div class="text-4xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                                {{ $statistics['por_estado']['en_proceso'] }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">En ejecución</span>
                        <span class="text-amber-600 font-medium opacity-0 group-hover:opacity-100 transition-opacity">Ver →</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Distribución por Estado --}}
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </span>
                    Distribución por Estado
                </h3>
                <div class="space-y-4">
                    @php
                        $estados = [
                            ['key' => 'borradores', 'label' => 'Borradores', 'color' => 'gray', 'gradient' => 'from-gray-400 to-gray-500'],
                            ['key' => 'publicadas', 'label' => 'Publicadas', 'color' => 'blue', 'gradient' => 'from-blue-500 to-indigo-600'],
                            ['key' => 'en_proceso', 'label' => 'En Proceso', 'color' => 'amber', 'gradient' => 'from-amber-500 to-orange-600'],
                            ['key' => 'finalizadas', 'label' => 'Finalizadas', 'color' => 'green', 'gradient' => 'from-green-500 to-emerald-600'],
                            ['key' => 'canceladas', 'label' => 'Canceladas', 'color' => 'red', 'gradient' => 'from-red-500 to-red-600'],
                        ];
                    @endphp

                    @foreach($estados as $estado)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-gray-700">{{ $estado['label'] }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ $statistics['por_estado'][$estado['key']] }}</span>
                        </div>
                        <div class="relative w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r {{ $estado['gradient'] }} h-3 rounded-full transition-all duration-500 shadow-lg"
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado'][$estado['key']] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Distribución Mensual --}}
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    Distribución Mensual
                </h3>
                <div class="flex items-end justify-between h-64 space-x-1">
                    @php
                        $maxValue = max($statistics['por_mes']);
                        $meses = ['E', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'];
                        $colores = [
                            'from-blue-400 to-blue-500',
                            'from-indigo-400 to-indigo-500',
                            'from-purple-400 to-purple-500',
                            'from-pink-400 to-pink-500',
                            'from-rose-400 to-rose-500',
                            'from-red-400 to-red-500',
                            'from-orange-400 to-orange-500',
                            'from-amber-400 to-amber-500',
                            'from-yellow-400 to-yellow-500',
                            'from-lime-400 to-lime-500',
                            'from-green-400 to-green-500',
                            'from-emerald-400 to-emerald-500',
                        ];
                    @endphp
                    @foreach($statistics['por_mes'] as $mes => $count)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="w-full bg-gradient-to-t {{ $colores[$mes - 1] }} rounded-t-xl hover:shadow-lg transition-all cursor-pointer relative"
                             style="height: {{ $maxValue > 0 ? ($count / $maxValue) * 100 : 0 }}%"
                             title="{{ $count }} convocatorias">
                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ $count }}
                            </div>
                        </div>
                        <div class="text-xs font-bold text-gray-600 mt-2">{{ $meses[$mes - 1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        @if($nearingEnd->isNotEmpty())
        <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl shadow-2xl p-6 mb-6 text-white">
            <h3 class="text-xl font-bold mb-4 flex items-center">
                <span class="text-3xl mr-3">⚠️</span>
                Convocatorias Próximas a Vencer ({{ $nearingEnd->count() }})
            </h3>
            <div class="space-y-3">
                @foreach($nearingEnd as $posting)
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 flex items-center justify-between hover:bg-white/20 transition-all border border-white/20">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center h-12 w-12 bg-white/20 backdrop-blur-sm rounded-xl text-white font-bold text-lg">
                            {{ $posting->getDaysRemaining() }}d
                        </div>
                        <div>
                            <div class="font-semibold">{{ $posting->title }}</div>
                            <div class="text-sm text-white/80">{{ $posting->code }} • Vence: {{ $posting->end_date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <a href="{{ route('jobposting.show', $posting) }}"
                       class="px-6 py-2 bg-white text-orange-600 rounded-xl font-semibold hover:bg-orange-50 transition-colors shadow-lg">
                        Ver Detalles
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($delayed->isNotEmpty())
        <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-2xl shadow-2xl p-6 mb-6 text-white">
            <h3 class="text-xl font-bold mb-4 flex items-center">
                <span class="text-3xl mr-3">🚨</span>
                Convocatorias con Fases Retrasadas ({{ $delayed->count() }})
            </h3>
            <div class="space-y-3">
                @foreach($delayed as $posting)
                <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 flex items-center justify-between hover:bg-white/20 transition-all border border-white/20">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center h-12 w-12 bg-white/20 backdrop-blur-sm rounded-xl text-3xl">
                            ⚠️
                        </div>
                        <div>
                            <div class="font-semibold">{{ $posting->title }}</div>
                            <div class="text-sm text-white/80">{{ $posting->code }} • {{ $posting->schedules->count() }} fases retrasadas</div>
                        </div>
                    </div>
                    <a href="{{ route('jobposting.show', $posting) }}"
                       class="px-6 py-2 bg-white text-red-600 rounded-xl font-semibold hover:bg-red-50 transition-colors shadow-lg">
                        Ver Detalles
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($nearingEnd->isEmpty() && $delayed->isEmpty())
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-2xl p-12 text-center text-white">
            <div class="text-7xl mb-4">✅</div>
            <h3 class="text-3xl font-bold mb-2">¡Todo está en orden!</h3>
            <p class="text-xl text-green-50">No hay convocatorias retrasadas ni próximas a vencer</p>
        </div>
        @endif
    </div>
</div>
@endsection
