@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Asignacion de Ganadores</h2>
            <p class="text-gray-500 text-sm">
                Convocatoria: <strong>{{ $posting->code }}</strong> - Fase Final
            </p>
        </div>
        <a href="{{ route('admin.results.cv-processing.list') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tarjetas de Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Puestos Totales</div>
            <div class="text-3xl font-bold text-blue-600">{{ $summary['total_profiles'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Vacantes Totales</div>
            <div class="text-3xl font-bold text-purple-600">{{ $summary['total_vacancies'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Postulantes Elegibles</div>
            <div class="text-3xl font-bold text-green-600">{{ $summary['total_eligible'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="text-sm text-gray-500">Puestos Listos</div>
            <div class="text-3xl font-bold text-orange-600">
                {{ collect($summary['profiles'])->filter(fn($p) => $p['can_assign'])->count() }}/{{ $summary['total_profiles'] }}
            </div>
        </div>
    </div>

    {{-- Panel de Informacion --}}
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Configuracion de Asignacion</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span>Puntaje minimo requerido: <strong>70 puntos</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                    <span>Accesitarios por puesto: <strong>2 personas</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-medal text-yellow-600 mr-2"></i>
                    <span>Ganadores: <strong>Personas = Vacantes</strong></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-list text-blue-500 mr-2"></i>
                    <span>Ordenamiento: <strong>Por puntaje final descendente</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Perfiles por Estado --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Estado por Puesto</h3>

        <div class="grid grid-cols-1 gap-4">
            @forelse($summary['profiles'] as $profile)
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="p-4 {{ $profile['can_assign'] ? 'bg-green-50 border-b border-green-200' : 'bg-red-50 border-b border-red-200' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-semibold text-lg">{{ $profile['position_code'] }} - {{ $profile['position_name'] }}</h4>
                            <p class="text-sm text-gray-600">Unidad: {{ $profile['unit'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold {{ $profile['can_assign'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $profile['eligible_applicants'] }} / {{ $profile['vacancies'] }}
                            </div>
                            <p class="text-xs {{ $profile['can_assign'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $profile['can_assign'] ? 'Listo para asignar' : 'Sin candidatos elegibles' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <p><i class="fas fa-medal text-yellow-500 mr-2"></i><strong>Ganadores:</strong> {{ $profile['vacancies'] }}</p>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p><i class="fas fa-star text-orange-500 mr-2"></i><strong>Accesitarios:</strong> 2 (por defecto)</p>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p><i class="fas fa-users text-gray-500 mr-2"></i><strong>Posibles no seleccionados:</strong>
                                @if($profile['eligible_applicants'] > $profile['vacancies'] + 2)
                                    {{ $profile['eligible_applicants'] - $profile['vacancies'] - 2 }}
                                @else
                                    0
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <p class="text-yellow-800">No hay puestos configurados para esta convocatoria</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Advertencias --}}
    @php
        $profilesWithoutCandidates = collect($summary['profiles'])->filter(fn($p) => !$p['can_assign'])->count();
    @endphp

    @if($profilesWithoutCandidates > 0)
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-1"></i>
                <div>
                    <p class="font-medium text-red-800">Hay puestos sin candidatos elegibles</p>
                    <p class="text-sm text-red-700">
                        {{ $profilesWithoutCandidates }} puesto(s) no tienen candidatos elegibles (puntaje >= 70).
                        Estos puestos no podran asignar ganadores.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Acciones --}}
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Acciones</h3>

            <div class="flex gap-4 flex-wrap">
                @if($summary['total_eligible'] > 0)
                    <form action="{{ route('admin.results.winner-assignment.preview', $posting) }}" method="POST">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="accesitarios_count" value="2" min="0" max="10"
                                   class="px-4 py-3 border border-gray-300 rounded-lg w-24"
                                   placeholder="Accesitarios">
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-search mr-2"></i>
                                Previsualizar Asignacion (Dry Run)
                            </button>
                        </div>
                    </form>
                @else
                    <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                        <i class="fas fa-search mr-2"></i>
                        Previsualizar Asignacion
                    </button>
                    <span class="text-sm text-gray-500 self-center">
                        No hay postulantes elegibles para asignar
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
