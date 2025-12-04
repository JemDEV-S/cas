@extends('layouts.admin')

@section('title', 'Editar Configuraciones')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Premium --}}
        <div class="relative overflow-hidden bg-white rounded-3xl shadow-2xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 opacity-95"></div>
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative px-8 py-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl shadow-lg">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-1">Editar Configuraciones</h1>
                            <p class="text-indigo-100 text-lg">{{ $selectedGroup->name ?? 'Grupo de configuración' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('configuration.index') }}"
                       class="px-6 py-3 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl font-semibold hover:bg-white/20 transition-all shadow-lg">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- Mensajes de Feedback --}}
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-2xl shadow-lg animate-pulse">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-2xl shadow-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Sidebar de Grupos --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden sticky top-6">
                    <div class="p-6 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <h2 class="text-xl font-bold text-white">Grupos</h2>
                    </div>
                    <nav class="p-2">
                        @foreach($groups as $group)
                        <a href="{{ route('configuration.edit', $group->id) }}"
                           class="flex items-center px-4 py-3 mb-2 rounded-xl transition-all {{ $selectedGroup && $selectedGroup->id === $group->id ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }}">
                            @if($group->icon)
                            <i class="{{ $group->icon }} mr-3"></i>
                            @endif
                            <div class="flex-1">
                                <div class="font-medium">{{ $group->name }}</div>
                                <div class="text-xs {{ $selectedGroup && $selectedGroup->id === $group->id ? 'text-indigo-100' : 'text-gray-500' }}">
                                    {{ $group->configs->count() }} configs
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Formulario de Configuración --}}
            <div class="lg:col-span-3">
                @if($selectedGroup)
                <form action="{{ route('configuration.update', $selectedGroup->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div class="p-8">
                            @if($selectedGroup->description)
                            <div class="mb-8 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-xl">
                                <p class="text-sm text-indigo-800">{{ $selectedGroup->description }}</p>
                            </div>
                            @endif

                            <div class="space-y-6">
                                @forelse($selectedGroup->configs as $config)
                                <div class="border border-gray-200 rounded-xl p-6 hover:border-indigo-300 hover:shadow-lg transition-all">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <label class="block text-lg font-semibold text-gray-900 mb-1">
                                                {{ $config->display_name }}
                                                @if($config->is_system)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Sistema
                                                </span>
                                                @endif
                                            </label>
                                            @if($config->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ $config->description }}</p>
                                            @endif
                                            @if($config->help_text)
                                            <p class="text-xs text-gray-500 italic">💡 {{ $config->help_text }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center space-x-2 ml-4">
                                            @if($config->value !== $config->default_value)
                                            <button type="button"
                                                    onclick="if(confirm('¿Restaurar a valor por defecto?')) { window.location.href='{{ route('configuration.reset', $config->id) }}'; }"
                                                    class="text-yellow-600 hover:text-yellow-800 transition-colors"
                                                    title="Restaurar valor por defecto">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                            @endif

                                            <a href="{{ route('configuration.history', $config->id) }}"
                                               class="text-indigo-600 hover:text-indigo-800 transition-colors"
                                               title="Ver historial">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    @include('configuration::components.config-input', ['config' => $config])

                                    <div class="mt-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100">
                                            Tipo: {{ $config->value_type->label() }}
                                        </span>
                                        @if($config->min_value !== null || $config->max_value !== null)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-md bg-blue-50 text-blue-700">
                                            Rango: {{ $config->min_value ?? '∞' }} - {{ $config->max_value ?? '∞' }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    </svg>
                                    <p class="text-gray-500 font-medium">No hay configuraciones disponibles en este grupo</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        @if($selectedGroup->configs->count() > 0)
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 mr-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Razón del cambio (opcional)
                                    </label>
                                    <input type="text"
                                           name="change_reason"
                                           placeholder="Ej: Actualización de parámetros por nueva política..."
                                           class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                <button type="submit"
                                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </form>
                @else
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Selecciona un grupo</h3>
                    <p class="text-gray-500">Elige un grupo de configuración de la barra lateral</p>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
