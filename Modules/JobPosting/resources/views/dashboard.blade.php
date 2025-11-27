@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800 mb-1">
                            Dashboard de Convocatorias
                        </h1>
                        <p class="text-gray-500 text-sm">
                            Vista ejecutiva - Año {{ $currentYear }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('jobposting.index') }}" 
                           class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition-colors">
                            Ver Convocatorias
                        </a>
                        <a href="{{ route('jobposting.create') }}" 
                           class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                            Nueva Convocatoria
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selector de Año --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" action="{{ route('jobposting.dashboard') }}" class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Filtrar por año:</label>
                <select name="year" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ $currentYear == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Estadísticas Principales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Total --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-3xl text-blue-500">📋</div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Total</div>
                        <div class="text-3xl font-semibold text-gray-800">{{ $statistics['total'] }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-500">Convocatorias {{ $currentYear }}</div>
            </div>

            {{-- Activas --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-3xl text-green-500">⚡</div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Activas</div>
                        <div class="text-3xl font-semibold text-gray-800">{{ $statistics['activas'] }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-500">En curso actualmente</div>
            </div>

            {{-- Finalizadas --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-3xl text-purple-500">✅</div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Finalizadas</div>
                        <div class="text-3xl font-semibold text-gray-800">{{ $statistics['por_estado']['finalizadas'] }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-500">Completadas con éxito</div>
            </div>

            {{-- En Proceso --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-3xl text-amber-500">⚙️</div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">En Proceso</div>
                        <div class="text-3xl font-semibold text-gray-800">{{ $statistics['por_estado']['en_proceso'] }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-500">En ejecución</div>
            </div>
        </div>

        {{-- Distribución por Estado --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Gráfico de Estados --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución por Estado</h3>
                <div class="space-y-4">
                    {{-- Borradores --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Borradores</span>
                            <span class="text-sm font-medium text-gray-900">{{ $statistics['por_estado']['borradores'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gray-500 h-2 rounded-full" 
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado']['borradores'] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- Publicadas --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Publicadas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $statistics['por_estado']['publicadas'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" 
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado']['publicadas'] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- En Proceso --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">En Proceso</span>
                            <span class="text-sm font-medium text-gray-900">{{ $statistics['por_estado']['en_proceso'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" 
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado']['en_proceso'] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- Finalizadas --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Finalizadas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $statistics['por_estado']['finalizadas'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado']['finalizadas'] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- Canceladas --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Canceladas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $statistics['por_estado']['canceladas'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" 
                                 style="width: {{ $statistics['total'] > 0 ? ($statistics['por_estado']['canceladas'] / $statistics['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Distribución Mensual --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución Mensual</h3>
                <div class="flex items-end justify-between h-64 space-x-2">
                    @php
                        $maxValue = max($statistics['por_mes']);
                        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    @endphp
                    @foreach($statistics['por_mes'] as $mes => $count)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-blue-500 rounded-t-lg hover:bg-blue-600 transition-colors cursor-pointer relative group"
                             style="height: {{ $maxValue > 0 ? ($count / $maxValue) * 100 : 0 }}%"
                             title="{{ $count }} convocatorias">
                            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                {{ $count }}
                            </div>
                        </div>
                        <div class="text-xs font-medium text-gray-600 mt-2">{{ $meses[$mes - 1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Convocatorias Próximas a Vencer --}}
        @if($nearingEnd->isNotEmpty())
        <div class="bg-orange-50 rounded-lg shadow-sm border border-orange-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-orange-800 mb-4">Convocatorias Próximas a Vencer ({{ $nearingEnd->count() }})</h3>
            <div class="space-y-3">
                @foreach($nearingEnd as $posting)
                <div class="bg-white rounded-lg p-4 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 font-medium">
                            {{ $posting->getDaysRemaining() }}d
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $posting->title }}</div>
                            <div class="text-sm text-gray-600">{{ $posting->code }} • Vence: {{ $posting->end_date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <a href="{{ route('jobposting.show', $posting) }}" 
                       class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                        Ver
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Convocatorias Retrasadas --}}
        @if($delayed->isNotEmpty())
        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-6">
            <h3 class="text-lg font-semibold text-red-800 mb-4">Convocatorias con Fases Retrasadas ({{ $delayed->count() }})</h3>
            <div class="space-y-3">
                @foreach($delayed as $posting)
                <div class="bg-white rounded-lg p-4 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center space-x-4">
                        <div class="h-10 w-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600 text-xl">
                            ⚠️
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $posting->title }}</div>
                            <div class="text-sm text-gray-600">{{ $posting->code }} • {{ $posting->schedules->count() }} fases retrasadas</div>
                        </div>
                    </div>
                    <a href="{{ route('jobposting.show', $posting) }}" 
                       class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                        Ver
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sin alertas --}}
        @if($nearingEnd->isEmpty() && $delayed->isEmpty())
        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-8 text-center">
            <div class="text-5xl mb-4">✅</div>
            <h3 class="text-xl font-semibold text-green-800 mb-2">¡Todo está en orden!</h3>
            <p class="text-green-700">No hay convocatorias retrasadas ni próximas a vencer.</p>
        </div>
        @endif
    </div>
</div>
@endsection