@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('jobposting.dashboard') }}" class="text-gray-500 hover:text-blue-600 transition-colors">
                        Dashboard
                    </a>
                </li>
                <li class="text-gray-400">/</li>
                <li class="text-gray-900 font-medium">Convocatorias</li>
            </ol>
        </nav>

        {{-- Header --}}
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-90"></div>
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

                <div class="relative px-8 py-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-4 mb-3">
                                <div class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-4xl font-bold text-white mb-1">Convocatorias CAS</h1>
                                    <p class="text-blue-100 text-lg">Gestión completa de procesos de contratación</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <a href="{{ route('jobposting.dashboard') }}"
                               class="flex items-center px-6 py-3 bg-white/10 backdrop-blur-md text-white rounded-2xl font-semibold hover:bg-white/20 transition-all border border-white/20 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Dashboard
                            </a>
                            @can('jobposting.create.posting')
                            <a href="{{ route('jobposting.create') }}"
                               class="flex items-center px-6 py-3 bg-white text-indigo-600 rounded-2xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nueva Convocatoria
                            </a>
                            @endcan
                        </div>
                    </div>

                    {{-- Stats Mini Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 text-center border border-white/20">
                            <div class="text-2xl font-bold text-white">{{ $statistics['total'] }}</div>
                            <div class="text-xs text-blue-100 font-medium mt-1">Total</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 text-center border border-white/20">
                            <div class="text-2xl font-bold text-white">{{ $statistics['por_estado']['borradores'] }}</div>
                            <div class="text-xs text-blue-100 font-medium mt-1">Borradores</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 text-center border border-white/20">
                            <div class="text-2xl font-bold text-white">{{ $statistics['por_estado']['publicadas'] }}</div>
                            <div class="text-xs text-blue-100 font-medium mt-1">Publicadas</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 text-center border border-white/20">
                            <div class="text-2xl font-bold text-white">{{ $statistics['por_estado']['en_proceso'] }}</div>
                            <div class="text-xs text-blue-100 font-medium mt-1">En Proceso</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 text-center border border-white/20">
                            <div class="text-2xl font-bold text-white">{{ $statistics['por_estado']['finalizadas'] }}</div>
                            <div class="text-xs text-blue-100 font-medium mt-1">Finalizadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros Mejorados --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
            <form method="GET" action="{{ route('jobposting.list') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar</label>
                        <div class="relative">
                            <input type="text"
                                   name="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   placeholder="Código, título..."
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Año</label>
                        <select name="year" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Todos los años</option>
                            @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Todos los estados</option>
                            <option value="BORRADOR" {{ ($filters['status'] ?? '') == 'BORRADOR' ? 'selected' : '' }}>📝 Borrador</option>
                            <option value="PUBLICADA" {{ ($filters['status'] ?? '') == 'PUBLICADA' ? 'selected' : '' }}>📢 Publicada</option>
                            <option value="EN_PROCESO" {{ ($filters['status'] ?? '') == 'EN_PROCESO' ? 'selected' : '' }}>⚙️ En Proceso</option>
                            <option value="FINALIZADA" {{ ($filters['status'] ?? '') == 'FINALIZADA' ? 'selected' : '' }}>✅ Finalizada</option>
                            <option value="CANCELADA" {{ ($filters['status'] ?? '') == 'CANCELADA' ? 'selected' : '' }}>❌ Cancelada</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                            Filtrar
                        </button>
                        <a href="{{ route('jobposting.list') }}"
                           class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Convocatorias Grid --}}
        @if($jobPostings->isEmpty())
        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-16 text-center">
            <div class="flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No se encontraron convocatorias</h3>
            <p class="text-gray-600 mb-6">Intenta ajustar los filtros o crea una nueva convocatoria</p>
            @can('jobposting.create.posting')
            <a href="{{ route('jobposting.create') }}"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Convocatoria
            </a>
            @endcan
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($jobPostings as $posting)
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 overflow-hidden group">
                {{-- Header --}}
                <div class="bg-gradient-to-r {{ $posting->status->gradientClass() }} p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ substr($posting->year, -2) }}</span>
                                </div>
                                <div>
                                    <div class="text-white/90 text-sm font-medium">{{ $posting->code }}</div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 backdrop-blur-sm text-white">
                                        {{ $posting->status->iconEmoji() }} {{ $posting->status->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white/90 text-xs mb-1">Año</div>
                            <div class="text-white text-xl font-bold">{{ $posting->year }}</div>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        {{ $posting->title }}
                    </h3>

                    @if($posting->description)
                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ $posting->description }}
                    </p>
                    @endif

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">Progreso</span>
                            <span class="text-xs font-bold text-blue-600">{{ number_format($posting->getProgressPercentage(), 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full transition-all duration-500"
                                 style="width: {{ $posting->getProgressPercentage() }}%"></div>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="flex items-center justify-between text-sm mb-4">
                        @if($posting->start_date)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $posting->start_date->format('d/m/Y') }}
                        </div>
                        @endif
                        @if($posting->end_date)
                        <div class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            {{ $posting->end_date->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('jobposting.show', $posting) }}"
                           class="flex-1 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-center rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
                            Ver Detalles
                        </a>

                        @can('jobposting.update.posting')
                        @if($posting->canBeEdited())
                        <a href="{{ route('jobposting.edit', $posting) }}"
                           class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @endif
                        @endcan

                        @can('jobposting.manage.schedule')
                        <a href="{{ route('jobposting.schedule', $posting) }}"
                           class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($jobPostings->hasPages())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 px-6 py-4 mt-8">
            {{ $jobPostings->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
