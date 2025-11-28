@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Documentos Pendientes de Firma</h1>
                <p class="mt-1 text-sm text-gray-600">Documentos que requieren tu firma digital</p>
            </div>
            <a href="{{ route('documents.index') }}">
                <x-button variant="outline">
                    <i class="fas fa-arrow-left mr-2"></i> Ver Todos los Documentos
                </x-button>
            </a>
        </div>
    </div>

    <!-- Documentos Pendientes -->
    @if($pendingDocuments->count() > 0)
        <div class="grid grid-cols-1 gap-6">
            @foreach($pendingDocuments as $document)
                @php
                    $mySignature = $document->signatures->firstWhere('user_id', auth()->id());
                @endphp

                <x-card class="hover:shadow-lg transition-shadow">
                    <div class="flex gap-6">
                        <!-- Información del Documento -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $document->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">Código: {{ $document->code }}</p>
                                </div>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                    Pendiente
                                </span>
                            </div>

                            <dl class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tipo de Documento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->template->name }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tu Rol</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $mySignature->role ?? 'Firmante' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Orden de Firma</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $mySignature->signature_order ?? '-' }} de {{ $document->total_signatures_required ?? 0 }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Generado el</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->generated_at?->format('d/m/Y H:i') }}</dd>
                                </div>

                                @if($document->documentable)
                                    <div class="col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Relacionado con</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ class_basename($document->documentable_type) }}:
                                            {{ $document->documentable->title ?? $document->documentable->name ?? $document->documentable->code ?? $document->documentable->id }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>

                            <!-- Progreso de Firmas -->
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">Progreso de firmas:</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $document->signatures_completed ?? 0 }} / {{ $document->total_signatures_required ?? 0 }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $document->total_signatures_required > 0 ? (($document->signatures_completed ?? 0) / $document->total_signatures_required * 100) : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex gap-2">
                                <a href="{{ route('documents.sign', $document) }}">
                                    <x-button variant="primary">
                                        <i class="fas fa-pen-fancy mr-2"></i> Firmar Ahora
                                    </x-button>
                                </a>

                                <a href="{{ route('documents.show', $document) }}">
                                    <x-button variant="secondary">
                                        <i class="fas fa-eye mr-2"></i> Ver Detalle
                                    </x-button>
                                </a>

                                <a href="{{ route('documents.view', $document) }}" target="_blank">
                                    <x-button variant="outline">
                                        <i class="fas fa-file-pdf mr-2"></i> Ver PDF
                                    </x-button>
                                </a>
                            </div>
                        </div>

                        <!-- Vista previa pequeña -->
                        <div class="hidden lg:block w-48 flex-shrink-0">
                            <div class="aspect-[3/4] bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                                <iframe
                                    src="{{ route('documents.view', $document) }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @else
        <!-- Estado Vacío -->
        <x-card>
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¡Todo al día!</h3>
                <p class="text-gray-600 mb-6">No tienes documentos pendientes de firma en este momento.</p>
                <a href="{{ route('documents.index') }}">
                    <x-button variant="secondary">
                        <i class="fas fa-file-alt mr-2"></i> Ver Todos los Documentos
                    </x-button>
                </a>
            </div>
        </x-card>
    @endif

    <!-- Información Útil -->
    <x-card class="mt-6 bg-blue-50 border-blue-200">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-2xl text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-semibold text-blue-900 mb-2">Información sobre la Firma Digital</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><i class="fas fa-check mr-2"></i> Necesitas tener instalado el componente web de FIRMA PERÚ</li>
                    <li><i class="fas fa-check mr-2"></i> Debes contar con tu DNIe o certificado digital válido</li>
                    <li><i class="fas fa-check mr-2"></i> El proceso de firma es completamente seguro y tiene validez legal</li>
                    <li><i class="fas fa-check mr-2"></i> Una vez firmado, el documento no podrá ser modificado</li>
                </ul>
            </div>
        </div>
    </x-card>
</div>
@endsection
