@extends('application::layouts.master')

@section('title', 'Postulaciones')

@section('content')
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Postulaciones</h2>
            @can('create', \Modules\Application\Entities\Application::class)
                <a href="{{ route('application.create') }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Nueva Postulación
                </a>
            @endcan
        </div>

        <!-- Filtros -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <form method="GET" action="{{ route('application.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">DNI</label>
                    <input type="text" name="dni" value="{{ $filters['dni'] ?? '' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                           placeholder="12345678">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="name" value="{{ $filters['name'] ?? '' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                           placeholder="Nombre del postulante">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <option value="PRESENTADA" {{ ($filters['status'] ?? '') === 'PRESENTADA' ? 'selected' : '' }}>Presentada</option>
                        <option value="APTO" {{ ($filters['status'] ?? '') === 'APTO' ? 'selected' : '' }}>Apto</option>
                        <option value="NO_APTO" {{ ($filters['status'] ?? '') === 'NO_APTO' ? 'selected' : '' }}>No Apto</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla -->
        @if($applications->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postulante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($applications as $application)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium">{{ $application->code }}</td>
                            <td class="px-6 py-4 text-sm">{{ $application->full_name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $application->dni }}</td>
                            <td class="px-6 py-4 text-sm">{{ $application->application_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 text-xs font-semibold rounded-full
                                    {{ $application->status === 'APTO' ? 'bg-green-100 text-green-800' :
                                       ($application->status === 'NO_APTO' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $application->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('application.show', $application->id) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $applications->links() }}</div>
        @else
            <p class="text-center py-12 text-gray-500">No hay postulaciones registradas.</p>
        @endif
    </div>
</div>
@endsection
