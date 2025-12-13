@extends('layouts.app')

@section('title', 'Jurados')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-gavel mr-2 text-indigo-600"></i>
                        Gestión de Jurados
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">Administración de miembros del jurado evaluador</p>
                </div>
                <a href="{{ route('jury-members.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i>
                    Nuevo Jurado
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-users text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Jurados</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $members->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Disponibles</p>
                        <p class="text-2xl font-semibold text-gray-900 text-green-600">{{ $members->where('is_available', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-cyan-100 rounded-lg p-3">
                            <i class="fas fa-graduation-cap text-cyan-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Capacitados</p>
                        <p class="text-2xl font-semibold text-gray-900 text-cyan-600">{{ $members->where('training_completed', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-user-check text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Activos</p>
                        <p class="text-2xl font-semibold text-gray-900 text-indigo-600">{{ $members->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Búsqueda</h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('jury-members.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Búsqueda</label>
                        <input type="text" name="search" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nombre o email" value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="1" {{ ($filters['is_active'] ?? '') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ ($filters['is_active'] ?? '') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disponibilidad</label>
                        <select name="is_available" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="1" {{ ($filters['is_available'] ?? '') == '1' ? 'selected' : '' }}>Disponible</option>
                            <option value="0" {{ ($filters['is_available'] ?? '') == '0' ? 'selected' : '' }}>No disponible</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Capacitación</label>
                        <select name="training_completed" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            <option value="1" {{ ($filters['training_completed'] ?? '') == '1' ? 'selected' : '' }}>Completada</option>
                            <option value="0" {{ ($filters['training_completed'] ?? '') == '0' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad</label>
                        <input type="text" name="specialty" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Especialidad" value="{{ $filters['specialty'] ?? '' }}">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Listado de Jurados</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidad</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experiencia</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignaciones</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carga</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($members as $member)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-sm">{{ strtoupper(substr($member->full_name, 0, 2)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $member->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->specialty)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800">
                                        {{ $member->specialty }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->years_of_experience)
                                    <span class="text-sm text-gray-900">{{ $member->years_of_experience }} años</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $member->active_assignments_count ?? 0 }}
                                    </span>
                                    <span class="text-sm text-gray-500">/ {{ $member->total_assignments }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $member->workload_percentage > 80 ? 'red' : ($member->workload_percentage > 50 ? 'yellow' : 'green') }} h-2 rounded-full" style="width: {{ $member->workload_percentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $member->workload_percentage }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member->status_badge['color'] }}">
                                        {{ $member->status_badge['label'] }}
                                    </span>
                                    @if($member->training_completed)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-graduation-cap mr-1"></i>
                                            Capacitado
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('jury-members.show', $member->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('jury-members.edit', $member->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteMember('{{ $member->id }}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($members->count() > 0)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $members->links() }}
            </div>
            @endif
        </div>

        <!-- Empty State -->
        @if($members->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <div class="flex flex-col items-center">
                <i class="fas fa-user-times text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay jurados registrados</h3>
                <p class="text-gray-500 mb-6">Los jurados registrados aparecerán aquí</p>
                <a href="{{ route('jury-members.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i>
                    Registrar Primer Jurado
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function deleteMember(id) {
    if (confirm('¿Está seguro de eliminar este jurado?')) {
        fetch(`/jury-members/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}
</script>
@endsection
