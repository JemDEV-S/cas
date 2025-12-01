@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Documentos Generados</h1>
                <p class="mt-1 text-sm text-gray-600">Gestión de documentos oficiales y firma digital</p>
            </div>
            <a href="{{ route('documents.pending-signatures') }}">
                <x-button variant="secondary">
                    <i class="fas fa-file-signature mr-2"></i> Documentos Pendientes de Firma
                </x-button>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <x-card class="mb-6">
        <form method="GET" action="{{ route('documents.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-form.select
                    name="status"
                    label="Estado del Documento"
                    :options="[
                        '' => 'Todos',
                        'draft' => 'Borrador',
                        'pending_signature' => 'Pendiente de Firma',
                        'signed' => 'Firmado',
                        'rejected' => 'Rechazado',
                        'cancelled' => 'Cancelado',
                    ]"
                    :value="request('status')"
                />

                <x-form.select
                    name="signature_status"
                    label="Estado de Firma"
                    :options="[
                        '' => 'Todos',
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Proceso',
                        'completed' => 'Completado',
                        'rejected' => 'Rechazado',
                    ]"
                    :value="request('signature_status')"
                />

                <x-form.select
                    name="template"
                    label="Tipo de Documento"
                    :options="$templates->pluck('name', 'id')->prepend('Todos', '')"
                    :value="request('template')"
                />

                <div class="flex items-end">
                    <x-form.checkbox
                        name="my_documents"
                        label="Solo mis documentos"
                        :checked="request('my_documents')"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <x-button type="submit" variant="secondary">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </x-button>
                <a href="{{ route('documents.index') }}">
                    <x-button variant="outline">
                        <i class="fas fa-times mr-2"></i> Limpiar
                    </x-button>
                </a>
            </div>
        </form>
    </x-card>

    <!-- Tabla de Documentos -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Código
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Título
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Firmas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Generado
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $document->code }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $document->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $document->template->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'draft' => 'gray',
                                        'pending_signature' => 'yellow',
                                        'signed' => 'green',
                                        'rejected' => 'red',
                                        'cancelled' => 'gray',
                                    ];
                                    $color = $statusColors[$document->status] ?? 'gray';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($document->signature_required)
                                    {{ $document->signatures_completed ?? 0 }} / {{ $document->total_signatures_required ?? 0 }}
                                @else
                                    <span class="text-gray-400">No requiere</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $document->generatedBy->name ?? 'Sistema' }}</div>
                                <div class="text-xs text-gray-400">{{ $document->generated_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <!-- Ver detalle -->
                                    <a href="{{ route('documents.show', $document) }}" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-blue-600 hover:bg-blue-50 hover:text-blue-900 transition-colors" 
                                    title="Ver detalle">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Descargar PDF -->
                                    <a href="{{ route('documents.download', $document) }}" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-green-600 hover:bg-green-50 hover:text-green-900 transition-colors" 
                                    title="Descargar PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Firmar (condicional) -->
                                    @if($document->canBeSignedBy(auth()->id()))
                                        <a href="{{ route('documents.sign', $document) }}" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-purple-600 hover:bg-purple-50 hover:text-purple-900 transition-colors" 
                                        title="Firmar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-file-alt text-4xl mb-2"></i>
                                    <p>No se encontraron documentos</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $documents->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
