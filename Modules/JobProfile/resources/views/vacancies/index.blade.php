@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Vacantes del Perfil</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Perfil: <code class="px-2 py-1 bg-gray-100 rounded">{{ $jobProfile->code }}</code> - {{ $jobProfile->title }}
                </p>
            </div>
            <a href="{{ route('jobprofile.profiles.show', $jobProfile->id) }}">
                <x-button variant="secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al Perfil
                </x-button>
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    @if(isset($statistics))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <i class="fas fa-list text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Vacantes</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Disponibles</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['available'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <i class="fas fa-hourglass-half text-yellow-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En Proceso</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['in_process'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                    <i class="fas fa-user-check text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Cubiertas</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['filled'] ?? 0 }}</p>
                </div>
            </div>
        </x-card>
    </div>
    @endif

    <!-- Tabla de Vacantes -->
    <x-card>
        @if($vacancies->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay vacantes generadas</h3>
                <p class="mt-1 text-sm text-gray-500">Las vacantes se generarán automáticamente cuando el perfil sea aprobado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Código</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Número</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Estado</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Postulante Asignado</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fecha Generación</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Fecha Asignación</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($vacancies as $vacancy)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
                                    <code class="px-2 py-1 bg-gray-100 rounded text-gray-900 font-mono">{{ $vacancy->vacancy_code }}</code>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    Vacante #{{ $vacancy->vacancy_number }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($vacancy->status === 'available') bg-green-100 text-green-800
                                        @elseif($vacancy->status === 'in_process') bg-yellow-100 text-yellow-800
                                        @elseif($vacancy->status === 'filled') bg-blue-100 text-blue-800
                                        @elseif($vacancy->status === 'vacant') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $vacancy->status_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    @if($vacancy->assignedApplicant)
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-blue-600">{{ substr($vacancy->assignedApplicant->name, 0, 2) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium">{{ $vacancy->assignedApplicant->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $vacancy->assignedApplicant->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin asignar</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $vacancy->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $vacancy->assigned_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        @if($vacancy->canBeDeclaredVacant())
                                            @can('declareVacant', $vacancy)
                                                <button
                                                    onclick="showDeclareVacantModal('{{ $vacancy->id }}')"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100"
                                                    title="Declarar Desierta">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>

<!-- Modal para declarar vacante desierta -->
<div id="declareVacantModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Declarar Vacante Desierta</h3>
        <form id="declareVacantForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Razón <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="reason"
                    id="reason"
                    rows="4"
                    required
                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm w-full"
                    placeholder="Explique por qué se declara desierta esta vacante"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <x-button type="button" variant="secondary" onclick="closeDeclareVacantModal()">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="danger">
                    Declarar Desierta
                </x-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showDeclareVacantModal(vacancyId) {
    const form = document.getElementById('declareVacantForm');
    form.action = `/jobprofile/vacancies/${vacancyId}/declare-vacant`;
    document.getElementById('declareVacantModal').classList.remove('hidden');
}

function closeDeclareVacantModal() {
    document.getElementById('declareVacantModal').classList.add('hidden');
    document.getElementById('declareVacantForm').reset();
}
</script>
@endpush
@endsection
