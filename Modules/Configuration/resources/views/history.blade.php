@extends('layouts.admin')

@section('title', 'Historial de Configuración')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Premium --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-1">Historial de Cambios</h1>
                            <p class="text-blue-100 text-lg">{{ $config->display_name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('configuration.edit', $config->config_group_id) }}"
                       class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl font-semibold hover:bg-white/20 transition-all shadow-lg">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- Información de la Configuración --}}
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Clave</h3>
                    <p class="text-lg font-mono text-gray-900">{{ $config->key }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Grupo</h3>
                    <p class="text-lg text-gray-900">{{ $config->configGroup->name }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-1">Valor Actual</h3>
                    <p class="text-lg font-semibold text-indigo-600">
                        @if($config->value_type->value === 'boolean')
                            {{ $config->value == '1' ? 'Activado' : 'Desactivado' }}
                        @else
                            {{ $config->value ?? 'No establecido' }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Historial --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
                <h2 class="text-2xl font-bold text-white">Historial de Cambios</h2>
                <p class="text-indigo-100 mt-1">{{ $history->total() }} cambios registrados</p>
            </div>

            @if($history->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($history as $entry)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $entry->changedBy->full_name ?? 'Usuario Desconocido' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $entry->changed_at->format('d/m/Y H:i:s') }}
                                        <span class="text-gray-400">•</span>
                                        {{ $entry->changed_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            @if($entry->change_reason)
                            <div class="mb-3 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                <p class="text-sm text-blue-800">
                                    <span class="font-medium">Razón:</span> {{ $entry->change_reason }}
                                </p>
                            </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-red-700 uppercase">Valor Anterior</span>
                                    </div>
                                    <p class="font-mono text-sm text-red-900 break-all">
                                        {{ $entry->old_value ?? '(vacío)' }}
                                    </p>
                                </div>

                                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-green-700 uppercase">Valor Nuevo</span>
                                    </div>
                                    <p class="font-mono text-sm text-green-900 break-all">
                                        {{ $entry->new_value ?? '(vacío)' }}
                                    </p>
                                </div>
                            </div>

                            @if($entry->ip_address)
                            <div class="mt-3 text-xs text-gray-500">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    IP: {{ $entry->ip_address }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $history->links() }}
            </div>
            @else
            <div class="p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Sin historial</h3>
                <p class="text-gray-500">Esta configuración aún no tiene cambios registrados</p>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
