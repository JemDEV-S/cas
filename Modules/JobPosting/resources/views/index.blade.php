@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header minimalista --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800 mb-1">
                            Convocatorias CAS
                        </h1>
                        <p class="text-gray-500 text-sm">
                            Gestión de procesos de contratación
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('jobposting.dashboard') }}" 
                           class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('jobposting.create') }}" 
                           class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                            Nueva Convocatoria
                        </a>
                    </div>
                </div>

                {{-- Estadísticas simples --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xl font-semibold text-gray-800">{{ $statistics['total'] }}</div>
                        <div class="text-gray-500 text-xs">Total</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xl font-semibold text-gray-800">{{ $statistics['por_estado']['borradores'] }}</div>
                        <div class="text-gray-500 text-xs">Borradores</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xl font-semibold text-gray-800">{{ $statistics['por_estado']['publicadas'] }}</div>
                        <div class="text-gray-500 text-xs">Publicadas</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xl font-semibold text-gray-800">{{ $statistics['por_estado']['en_proceso'] }}</div>
                        <div class="text-gray-500 text-xs">En Proceso</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-xl font-semibold text-gray-800">{{ $statistics['por_estado']['finalizadas'] }}</div>
                        <div class="text-gray-500 text-xs">Finalizadas</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas/Mensajes --}}
        @if(session('success'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" action="{{ route('jobposting.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Código, título..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="BORRADOR" {{ ($filters['status'] ?? '') == 'BORRADOR' ? 'selected' : '' }}>Borrador</option>
                        <option value="PUBLICADA" {{ ($filters['status'] ?? '') == 'PUBLICADA' ? 'selected' : '' }}>Publicada</option>
                        <option value="EN_PROCESO" {{ ($filters['status'] ?? '') == 'EN_PROCESO' ? 'selected' : '' }}>En Proceso</option>
                        <option value="FINALIZADA" {{ ($filters['status'] ?? '') == 'FINALIZADA' ? 'selected' : '' }}>Finalizada</option>
                        <option value="CANCELADA" {{ ($filters['status'] ?? '') == 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors">
                        Filtrar
                    </button>
                    <a href="{{ route('jobposting.index') }}" 
                       class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de convocatorias --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($jobPostings as $posting)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                                        {{ substr($posting->year, -2) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $posting->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $posting->title }}</div>
                                @if($posting->description)
                                <div class="text-xs text-gray-500 line-clamp-1">{{ Str::limit($posting->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $posting->year }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $posting->status->badgeClass() }}">
                                    {{ $posting->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mr-2">
                                        <div class="bg-blue-500 h-1.5 rounded-full" 
                                             style="width: {{ $posting->getProgressPercentage() }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600">{{ number_format($posting->getProgressPercentage(), 0) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($posting->start_date)
                                <div>{{ $posting->start_date->format('d/m/Y') }}</div>
                                @endif
                                @if($posting->end_date)
                                <div>{{ $posting->end_date->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">
                                <a href="{{ route('jobposting.show', $posting) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition-colors">
                                    Ver
                                </a>
                                @if($posting->canBeEdited())
                                <a href="{{ route('jobposting.edit', $posting) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                                    Editar
                                </a>
                                @endif
                                <a href="{{ route('jobposting.schedule', $posting) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                                    Calendario
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400 text-lg">
                                    No se encontraron convocatorias
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($jobPostings->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $jobPostings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection