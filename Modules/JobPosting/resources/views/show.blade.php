@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('jobposting.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a convocatorias
            </a>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        {{-- Card Principal --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
            {{-- Header con estado --}}
            <div class="px-6 py-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-xl">
                            {{ substr($jobPosting->year, -2) }}
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 font-medium">{{ $jobPosting->code }}</div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $jobPosting->title }}</h1>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $jobPosting->status->badgeClass() }}">
                            {{ $jobPosting->status->icon() }} {{ $jobPosting->status->label() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Barra de progreso --}}
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progreso General</span>
                    <span class="text-sm font-medium text-blue-600">{{ number_format($progress['percentage'], 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $progress['percentage'] }}%"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-600">
                    <span>{{ $progress['completed'] }} completadas</span>
                    <span>{{ $progress['in_progress'] }} en progreso</span>
                    <span>{{ $progress['pending'] }} pendientes</span>
                </div>
            </div>

            {{-- Contenido principal --}}
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Información principal --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Descripción --}}
                        @if($jobPosting->description)
                        <div class="bg-blue-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-3">Descripción</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $jobPosting->description }}</p>
                        </div>
                        @endif

                        {{-- Fase actual --}}
                        @if($currentPhase)
                        <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-800 mb-2">Fase Actual</h3>
                                    <p class="text-lg font-medium text-gray-900">{{ $currentPhase->phase->name }}</p>
                                    <p class="text-sm text-gray-600 mt-2">
                                        {{ $currentPhase->start_date->format('d/m/Y') }} - {{ $currentPhase->end_date->format('d/m/Y') }}
                                    </p>
                                    @if($currentPhase->responsibleUnit)
                                    <p class="text-sm text-gray-600">
                                        {{ $currentPhase->responsibleUnit->name }}
                                    </p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 bg-blue-500 text-white rounded-lg font-medium text-sm">
                                    Fase {{ $currentPhase->phase->phase_number }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Próxima fase --}}
                        @if($nextPhase)
                        <div class="bg-amber-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-2">Próxima Fase</h3>
                            <p class="text-lg font-medium text-gray-900">{{ $nextPhase->phase->name }}</p>
                            <p class="text-sm text-gray-600 mt-2">
                                Inicia: {{ $nextPhase->start_date->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif

                        {{-- Fases retrasadas --}}
                        @if($delayedPhases->isNotEmpty())
                        <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                            <h3 class="text-base font-semibold text-red-800 mb-3">Fases Retrasadas ({{ $delayedPhases->count() }})</h3>
                            <div class="space-y-2">
                                @foreach($delayedPhases as $delayed)
                                <div class="flex items-center justify-between bg-white rounded-lg p-3">
                                    <span class="text-sm font-medium text-gray-800">{{ $delayed->phase->name }}</span>
                                    <span class="text-xs text-red-600">Venció: {{ $delayed->end_date->format('d/m/Y') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Cronograma resumido --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-800">Cronograma</h3>
                                <a href="{{ route('jobposting.schedule', $jobPosting) }}" 
                                   class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                                    Ver cronograma completo
                                </a>
                            </div>
                            <div class="space-y-3">
                                @foreach($jobPosting->schedules->take(5) as $schedule)
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($schedule->status->value === 'COMPLETED')
                                        <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-medium">
                                            ✓
                                        </div>
                                        @elseif($schedule->status->value === 'IN_PROGRESS')
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium">
                                            ▶
                                        </div>
                                        @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-medium">
                                            {{ $schedule->phase->phase_number }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-800">{{ $schedule->phase->name }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full {{ $schedule->status->badgeClass() }} text-white">
                                                {{ $schedule->status->icon() }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $schedule->start_date->format('d/m/Y') }} - {{ $schedule->end_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="space-y-6">
                        
                        {{-- Información general --}}
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">Información</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Código</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->code }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Año</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->year }}</div>
                                </div>
                                @if($jobPosting->start_date)
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Fecha Inicio</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->start_date->format('d/m/Y') }}</div>
                                </div>
                                @endif
                                @if($jobPosting->end_date)
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Fecha Fin</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->end_date->format('d/m/Y') }}</div>
                                </div>
                                @endif
                                @if($jobPosting->published_at)
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Publicada</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->published_at->format('d/m/Y H:i') }}</div>
                                </div>
                                @endif
                                @if($jobPosting->publisher)
                                <div>
                                    <div class="text-xs text-gray-500 font-medium">Publicada por</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $jobPosting->publisher->name }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-base font-semibold text-gray-800 mb-4">Acciones</h3>
                            <div class="space-y-3">
                                @if($jobPosting->canBeEdited())
                                <a href="{{ route('jobposting.edit', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-amber-500 text-white rounded-lg text-center font-medium hover:bg-amber-600 transition-colors">
                                    Editar
                                </a>
                                @endif

                                @if($jobPosting->canBePublished())
                                <form action="{{ route('jobposting.publish', $jobPosting) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition-colors"
                                            onclick="return confirm('¿Publicar convocatoria?')">
                                        Publicar
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('jobposting.schedule', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-purple-500 text-white rounded-lg text-center font-medium hover:bg-purple-600 transition-colors">
                                    Ver Cronograma
                                </a>

                                <a href="{{ route('jobposting.history', $jobPosting) }}" 
                                   class="block w-full px-4 py-2 bg-cyan-500 text-white rounded-lg text-center font-medium hover:bg-cyan-600 transition-colors">
                                    Ver Historial
                                </a>

                                <form action="{{ route('jobposting.clone', $jobPosting) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-4 py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors"
                                            onclick="return confirm('¿Clonar convocatoria?')">
                                        Clonar
                                    </button>
                                </form>

                                @if($jobPosting->canBeCancelled())
                                <button onclick="showCancelModal()" 
                                        class="w-full px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors">
                                    Cancelar
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Zona de peligro --}}
                        @if($jobPosting->canBeEdited())
                        <div class="bg-red-50 rounded-lg p-6 border border-red-200">
                            <h3 class="text-sm font-medium text-red-800 mb-3">Zona de Peligro</h3>
                            <form action="{{ route('jobposting.destroy', $jobPosting) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                                        onclick="return confirm('¿Está seguro de eliminar esta convocatoria?')">
                                    Eliminar Convocatoria
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de cancelación --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Cancelar Convocatoria</h3>
        <form action="{{ route('jobposting.cancel', $jobPosting) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de cancelación *</label>
                <textarea name="cancellation_reason" 
                          rows="4" 
                          required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Explique detalladamente el motivo..."></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="hideCancelModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Cerrar
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors">
                    Confirmar Cancelación
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}
function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}
</script>
@endpush
@endsection