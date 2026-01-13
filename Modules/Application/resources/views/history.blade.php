@extends('layouts.app')

@section('title', 'Historial de Cambios')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Historial de Cambios</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $application->code }} - {{ $application->full_name }}</p>
                </div>
                <a href="{{ route('application.show', $application->id) }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Volver a la Postulación
                </a>
            </div>
        </div>
    </div>

    <!-- Timeline de historial -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-4">
            @if($history->count() > 0)
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($history as $index => $record)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                {{ $record->event_type === 'CREATED' ? 'bg-green-500' :
                                                   ($record->event_type === 'STATUS_CHANGED' ? 'bg-blue-500' :
                                                   ($record->event_type === 'ELIGIBILITY_CHECKED' ? 'bg-purple-500' : 'bg-gray-400')) }}">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($record->event_type === 'CREATED')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/>
                                                    @elseif($record->event_type === 'STATUS_CHANGED')
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                                    @else
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-900 font-medium">
                                                    {{ $record->event_type_name }}
                                                </p>
                                                @if($record->description)
                                                    <p class="mt-1 text-sm text-gray-600">
                                                        {{ $record->description }}
                                                    </p>
                                                @endif
                                                @if($record->old_status && $record->new_status)
                                                    <p class="mt-1 text-sm">
                                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ $record->old_status }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ $record->new_status }}</span>
                                                    </p>
                                                @endif
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Por: <span class="font-medium">{{ $record->performer->name ?? 'Sistema' }}</span>
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time datetime="{{ $record->performed_at->toIso8601String() }}">
                                                    {{ $record->performed_at->format('d/m/Y H:i') }}
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Paginación -->
                <div class="mt-6">
                    {{ $history->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay historial</h3>
                    <p class="mt-1 text-sm text-gray-500">No se han registrado cambios en esta postulación.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
