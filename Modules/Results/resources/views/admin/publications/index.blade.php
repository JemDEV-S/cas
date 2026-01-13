@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Publicaciones de Resultados</h2>
            <p class="text-gray-500 text-sm">Gestión de resultados con firma digital</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fase</label>
                    <select name="phase" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas las fases</option>
                        <option value="PHASE_04" {{ request('phase') === 'PHASE_04' ? 'selected' : '' }}>
                            Fase 4 - Elegibilidad
                        </option>
                        <option value="PHASE_07" {{ request('phase') === 'PHASE_07' ? 'selected' : '' }}>
                            Fase 7 - Evaluación Curricular
                        </option>
                        <option value="PHASE_09" {{ request('phase') === 'PHASE_09' ? 'selected' : '' }}>
                            Fase 9 - Resultados Finales
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending_signature" {{ request('status') === 'pending_signature' ? 'selected' : '' }}>
                            Pendiente de Firma
                        </option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicado</option>
                        <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Despublicado</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.results.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de publicaciones --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            @if($publications->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No hay publicaciones de resultados</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Convocatoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Postulantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Firmas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publicado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($publications as $publication)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $publication->jobPosting->code ?? 'N/A' }}</div>
                                    <small class="text-gray-500">{{ Str::limit($publication->title, 50) }}</small>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $publication->phase->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($publication->status->color() === 'success') bg-green-100 text-green-800
                                        @elseif($publication->status->color() === 'warning') bg-yellow-100 text-yellow-800
                                        @elseif($publication->status->color() === 'danger') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $publication->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $publication->total_applicants }}</div>
                                    @if($publication->phase->value === 'PHASE_04')
                                        <small class="text-green-600">
                                            <i class="fas fa-check-circle"></i> {{ $publication->total_eligible }} APTOS
                                        </small>
                                        <small class="text-red-600 ml-2">
                                            <i class="fas fa-times-circle"></i> {{ $publication->total_not_eligible }} NO APTOS
                                        </small>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $progress = $publication->getSignatureProgress();
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-grow mr-2 bg-gray-200 rounded-full h-2 w-20">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                 style="width: {{ $progress['percentage'] }}%">
                                            </div>
                                        </div>
                                        <small class="text-gray-500 text-xs">
                                            {{ $progress['completed'] }}/{{ $progress['total'] }}
                                        </small>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($publication->published_at)
                                        <div class="text-gray-900">{{ $publication->published_at->format('d/m/Y') }}</div>
                                        <small class="text-gray-500">{{ $publication->published_at->format('H:i') }}</small>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.results.show', $publication) }}"
                                           class="p-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($publication->document && $publication->document->signed_pdf_path)
                                            <a href="{{ route('admin.results.download-pdf', $publication) }}"
                                               class="p-2 text-green-600 border border-green-600 rounded-lg hover:bg-green-50 transition-colors"
                                               title="Descargar PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif

                                        @if($publication->excel_path)
                                            <a href="{{ route('admin.results.download-excel', $publication) }}"
                                               class="p-2 text-green-600 border border-green-600 rounded-lg hover:bg-green-50 transition-colors"
                                               title="Descargar Excel">
                                                <i class="fas fa-file-excel"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-6">
                    {{ $publications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
