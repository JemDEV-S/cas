@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle del Código de Posición</h1>
            <p class="mt-1 text-sm text-gray-600">Información completa del código <code class="px-2 py-1 bg-gray-100 rounded text-sm">{{ $positionCode->code }}</code></p>
        </div>
        <div class="flex space-x-3">
            @can('update', $positionCode)
                <a href="{{ route('jobprofile.positions.edit', $positionCode->id) }}"
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
            @endcan
            <a href="{{ route('jobprofile.positions.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Información General --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Información General</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                            <code class="px-3 py-2 bg-gray-100 text-gray-900 rounded-md font-mono text-lg inline-block">{{ $positionCode->code }}</code>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <div>
                                @if($positionCode->is_active)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Inactivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Puesto</label>
                        <p class="text-xl font-semibold text-gray-900">{{ $positionCode->name }}</p>
                    </div>

                    @if($positionCode->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <p class="text-gray-600 leading-relaxed">{{ $positionCode->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Información Salarial --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Información Salarial</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Salario Base</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $positionCode->formatted_base_salary }}</span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Porcentaje EsSalud</span>
                        <span class="text-sm text-gray-900">{{ number_format($positionCode->essalud_percentage, 2) }}%</span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Monto EsSalud</span>
                        <span class="text-sm text-gray-900">S/ {{ number_format($positionCode->essalud_amount, 2) }}</span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center bg-blue-50">
                        <span class="text-sm font-semibold text-gray-900">Total Mensual</span>
                        <span class="text-lg font-bold text-blue-600">{{ $positionCode->formatted_monthly_total }}</span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Meses de Contrato</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $positionCode->contract_months }} meses
                        </span>
                    </div>
                    <div class="px-6 py-5 flex justify-between items-center bg-green-50">
                        <span class="text-base font-semibold text-gray-900">Total por Periodo</span>
                        <span class="text-2xl font-bold text-green-600">{{ $positionCode->formatted_quarterly_total }}</span>
                    </div>
                </div>
            </div>

            {{-- Requisitos Profesionales --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Requisitos Profesionales</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Experiencia General --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-700">Experiencia Profesional General</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($positionCode->min_professional_experience)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $positionCode->min_professional_experience }} años mínimo
                                        </span>
                                    @else
                                        <span class="text-gray-400">No especificado</span>
                                    @endif
                                </dd>
                            </div>
                        </div>

                        {{-- Experiencia Específica --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-700">Experiencia Específica del Puesto</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($positionCode->min_specific_experience)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $positionCode->min_specific_experience }} años mínimo
                                        </span>
                                    @else
                                        <span class="text-gray-400">No especificado</span>
                                    @endif
                                </dd>
                            </div>
                        </div>

                        {{-- Título Profesional --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-700">Título Profesional</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($positionCode->requires_professional_title)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Requerido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No requerido
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </div>

                        {{-- Colegiatura --}}
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-700">Colegiatura Profesional</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($positionCode->requires_professional_license)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Requerida
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No requerida
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </div>

                    {{-- Nivel Educativo --}}
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <dt class="text-sm font-medium text-gray-700 mb-2">Niveles Educativos Aceptados</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($positionCode->education_levels_accepted && count($positionCode->education_levels_accepted) > 0)
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($positionCode->education_levels_accepted as $level)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    {{ \Modules\JobProfile\Enums\EducationLevelEnum::tryFrom($level)?->label() ?? $level }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($positionCode->education_level_required)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            {{ $positionCode->education_level_required }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">No especificado</span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Perfiles Asociados --}}
            @if($positionCode->jobProfiles->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Perfiles de Puesto Asociados</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($positionCode->jobProfiles as $profile)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="px-2 py-1 text-xs font-mono bg-gray-100 text-gray-800 rounded">{{ $profile->code }}</code>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $profile->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {!! $profile->status_badge !!}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $profile->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <a href="{{ route('jobprofile.profiles.show', $profile->id) }}"
                                               class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Acciones --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Acciones</h3>
                </div>
                <div class="px-6 py-4">
                    @can('toggleStatus', $positionCode)
                        @if($positionCode->is_active)
                            <form action="{{ route('jobprofile.positions.deactivate', $positionCode->id) }}"
                                  method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
                                        onclick="return confirm('¿Está seguro de desactivar este código de posición?')">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Desactivar Código
                                </button>
                            </form>
                        @else
                            <form action="{{ route('jobprofile.positions.activate', $positionCode->id) }}"
                                  method="POST">
                                @csrf
                                <button type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Activar Código
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Información del Registro --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Información del Registro</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Creado</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $positionCode->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Última modificación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $positionCode->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </div>
            </div>

            {{-- Estadísticas --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Estadísticas</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Perfiles Asociados</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $positionCode->jobProfiles->count() }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Perfiles Activos</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $positionCode->jobProfiles->where('status', 'approved')->count() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
