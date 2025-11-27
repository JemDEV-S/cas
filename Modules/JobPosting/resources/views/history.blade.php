@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('jobposting.show', $jobPosting) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver a la convocatoria
            </a>
        </div>

        {{-- Título --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900">📜 Historial de Cambios</h1>
            <p class="text-gray-600 mt-1">{{ $jobPosting->code }} - {{ $jobPosting->title }}</p>
        </div>

        {{-- Timeline de historial --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            @if($history->count() > 0)
            <div class="space-y-6">
                @foreach($history as $entry)
                <div class="flex items-start space-x-4 pb-6 border-b border-gray-200 last:border-0">
                    
                    {{-- Icono de acción --}}
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full {{ 
                            $entry->action === 'created' ? 'bg-green-100 text-green-600' : 
                            ($entry->action === 'published' ? 'bg-blue-100 text-blue-600' : 
                            ($entry->action === 'cancelled' ? 'bg-red-100 text-red-600' : 
                            'bg-gray-100 text-gray-600')) 
                        }} flex items-center justify-center font-bold">
                            @if($entry->action === 'created')
                                ➕
                            @elseif($entry->action === 'published')
                                📢
                            @elseif($entry->action === 'cancelled')
                                ❌
                            @elseif($entry->action === 'updated')
                                ✏️
                            @elseif($entry->action === 'finalized')
                                ✅
                            @else
                                📝
                            @endif
                        </div>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 capitalize">
                                    {{ ucfirst(str_replace('_', ' ', $entry->action)) }}
                                </h4>
                                
                                {{-- Cambio de estado --}}
                                @if($entry->old_status || $entry->new_status)
                                <div class="text-sm text-gray-600 mt-1">
                                    Estado: 
                                    @if($entry->old_status)
                                    <span class="font-semibold">{{ $entry->old_status }}</span>
                                    →
                                    @endif
                                    <span class="font-semibold text-blue-600">{{ $entry->new_status }}</span>
                                </div>
                                @endif

                                {{-- Descripción --}}
                                @if($entry->description)
                                <p class="text-sm text-gray-700 mt-2">{{ $entry->description }}</p>
                                @endif

                                {{-- Razón (para cancelaciones) --}}
                                @if($entry->reason)
                                <div class="mt-2 p-3 bg-red-50 rounded-lg border border-red-200">
                                    <p class="text-sm text-red-800"><strong>Motivo:</strong> {{ $entry->reason }}</p>
                                </div>
                                @endif

                                {{-- Usuario y fecha --}}
                                <div class="flex items-center space-x-4 mt-3 text-xs text-gray-500">
                                    @if($entry->user)
                                    <span>👤 {{ $entry->user->name }}</span>
                                    @endif
                                    <span>🕐 {{ $entry->created_at->format('d/m/Y H:i:s') }}</span>
                                    @if($entry->ip_address)
                                    <span>🌐 {{ $entry->ip_address }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Valores cambiados (si existen) --}}
                        @if($entry->old_values && $entry->new_values)
                        <details class="mt-3">
                            <summary class="text-sm text-blue-600 cursor-pointer hover:text-blue-800">Ver detalles de cambios</summary>
                            <div class="mt-2 p-3 bg-gray-50 rounded-lg text-xs">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <strong class="text-gray-700">Valores anteriores:</strong>
                                        <pre class="mt-1 text-gray-600">{{ json_encode($entry->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    <div>
                                        <strong class="text-gray-700">Valores nuevos:</strong>
                                        <pre class="mt-1 text-gray-600">{{ json_encode($entry->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </details>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            @if($history->hasPages())
            <div class="mt-6">
                {{ $history->links() }}
            </div>
            @endif

            @else
            <div class="text-center py-12 text-gray-500">
                <div class="text-6xl mb-4">📜</div>
                <p class="text-lg">No hay registros en el historial</p>
                <p class="text-sm mt-2">Los cambios realizados aparecerán aquí</p>
            </div>
            @endif
        </div>

        {{-- Información adicional --}}
        <div class="bg-blue-50 rounded-xl p-4 mt-6 border border-blue-200">
            <p class="text-sm text-blue-800">
                <strong>ℹ️ Información:</strong> Este historial registra todos los cambios realizados en la convocatoria, 
                incluyendo creación, edición, cambios de estado, publicación y cancelación.
            </p>
        </div>
    </div>
</div>
@endsection