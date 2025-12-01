@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $document->title }}</h1>
                <p class="mt-1 text-sm text-gray-600">Código: {{ $document->code }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('documents.index') }}">
                    <x-button variant="outline">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </x-button>
                </a>
                <a href="{{ route('documents.download', $document) }}" target="_blank">
                    <x-button variant="secondary">
                        <i class="fas fa-download mr-2"></i> Descargar PDF
                    </x-button>
                </a>
                @if($document->canBeSignedBy(auth()->id()))
                    <a href="{{ route('documents.sign', $document) }}">
                        <x-button variant="primary">
                            <i class="fas fa-pen-fancy mr-2"></i> Firmar Documento
                        </x-button>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Detalles del Documento -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <i class="fas fa-info-circle mr-2"></i> Información del Documento
                </h3>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo de Documento</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $document->template->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
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
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Generado por</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $document->generatedBy->name ?? 'Sistema' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Generación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $document->generated_at?->format('d/m/Y H:i') }}</dd>
                    </div>

                    @if($document->documentable)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Relacionado con</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ class_basename($document->documentable_type) }}:
                                {{ $document->documentable->title ?? $document->documentable->name ?? $document->documentable->code ?? $document->documentable->id }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <!-- Flujo de Firmas -->
            @if($document->signature_required)
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-pen-fancy mr-2"></i> Flujo de Firmas
                    </h3>

                    <div class="space-y-4">
                        @foreach($document->signatures as $signature)
                            <div class="flex items-start gap-4 p-4 rounded-lg {{ $signature->status === 'signed' ? 'bg-green-50' : ($signature->status === 'pending' ? 'bg-yellow-50' : 'bg-gray-50') }}">
                                <div class="flex-shrink-0">
                                    @if($signature->status === 'signed')
                                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    @elseif($signature->status === 'pending')
                                        <div class="w-10 h-10 rounded-full bg-yellow-500 flex items-center justify-center text-white">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-400 flex items-center justify-center text-white">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $signature->user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $signature->role }}</p>
                                        </div>
                                        <span class="text-xs text-gray-500">Orden: {{ $signature->signature_order }}</span>
                                    </div>

                                    @if($signature->status === 'signed')
                                        <div class="mt-2 text-sm text-gray-600">
                                            <i class="fas fa-check-circle text-green-600 mr-1"></i>
                                            Firmado el {{ $signature->signed_at?->format('d/m/Y H:i') }}
                                        </div>
                                    @elseif($signature->status === 'pending')
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <i class="fas fa-hourglass-half mr-1"></i>
                                            Pendiente de firma
                                        </div>
                                    @elseif($signature->status === 'rejected')
                                        <div class="mt-2 text-sm text-red-700">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rechazado: {{ $signature->rejection_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($document->signatureWorkflow)
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Progreso del flujo:</span>
                                <span class="font-semibold text-gray-900">
                                    {{ $document->signatures_completed ?? 0 }} / {{ $document->total_signatures_required ?? 0 }} firmas completadas
                                </span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $document->total_signatures_required > 0 ? (($document->signatures_completed ?? 0) / $document->total_signatures_required * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endif
                </x-card>
            @endif

            <!-- Historial de Auditoría -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <i class="fas fa-history mr-2"></i> Historial de Actividad
                </h3>

                <div class="space-y-3">
                    @forelse($document->audits as $audit)
                        <div class="flex gap-3 text-sm">
                            <div class="flex-shrink-0 text-gray-400">
                                {{ $audit->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="flex-1">
                                <span class="font-medium text-gray-900">{{ $audit->user->name ?? 'Sistema' }}</span>
                                <span class="text-gray-600">{{ $audit->description }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No hay actividad registrada</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Visor de PDF -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                    <i class="fas fa-file-pdf mr-2"></i> Vista Previa
                </h3>

                <div class="aspect-[3/4] bg-gray-100 rounded-lg overflow-hidden">
                    <iframe
                        src="{{ route('documents.view', $document) }}"
                        class="w-full h-full"
                        frameborder="0"
                    ></iframe>
                </div>

                <div class="mt-4 space-y-2">
                    <a href="{{ route('documents.view', $document) }}" target="_blank" class="block">
                        <x-button variant="outline" class="w-full">
                            <i class="fas fa-external-link-alt mr-2"></i> Abrir en nueva pestaña
                        </x-button>
                    </a>

                    @if($document->signed_pdf_path)
                        <a href="{{ route('documents.download', ['document' => $document, 'signed' => true]) }}" class="block">
                            <x-button variant="secondary" class="w-full">
                                <i class="fas fa-download mr-2"></i> Descargar Versión Firmada
                            </x-button>
                        </a>
                    @endif
                </div>
            </x-card>

            <!-- Acciones Rápidas -->
            @can('update', $document)
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">
                        <i class="fas fa-cog mr-2"></i> Acciones
                    </h3>

                    <div class="space-y-2">
                        <form action="{{ route('documents.regenerate', $document) }}" method="POST">
                            @csrf
                            <x-button variant="outline" class="w-full" type="submit">
                                <i class="fas fa-sync mr-2"></i> Regenerar PDF
                            </x-button>
                        </form>

                        @can('delete', $document)
                            <form action="{{ route('documents.destroy', $document) }}" method="POST"
                                  onsubmit="return confirm('¿Está seguro de eliminar este documento?')">
                                @csrf
                                @method('DELETE')
                                <x-button variant="danger" class="w-full" type="submit">
                                    <i class="fas fa-trash mr-2"></i> Eliminar Documento
                                </x-button>
                            </form>
                        @endcan
                    </div>
                </x-card>
            @endcan
        </div>
    </div>
</div>
@endsection
