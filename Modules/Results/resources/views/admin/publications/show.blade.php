@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">{{ $publication->title }}</h2>
            <p class="text-gray-500">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2
                    @if($publication->status->color() === 'success') bg-green-100 text-green-800
                    @elseif($publication->status->color() === 'warning') bg-yellow-100 text-yellow-800
                    @elseif($publication->status->color() === 'danger') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $publication->status->label() }}
                </span>
                {{ $publication->phase->label() }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.results.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Información principal --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold">Información General</h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-500">Convocatoria</label>
                            <div class="font-semibold text-gray-900">{{ $publication->jobPosting->code }}</div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Fase</label>
                            <div class="text-gray-900">{{ $publication->phase->label() }}</div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Total Postulantes</label>
                            <div class="font-semibold text-gray-900">{{ $publication->total_applicants }}</div>
                        </div>
                        @if($publication->phase->value === 'PHASE_04')
                            <div>
                                <label class="text-sm text-gray-500">Resultados</label>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $publication->total_eligible }} APTOS</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">{{ $publication->total_not_eligible }} NO APTOS</span>
                                </div>
                            </div>
                        @endif
                        @if($publication->published_at)
                            <div>
                                <label class="text-sm text-gray-500">Fecha de Publicación</label>
                                <div class="text-gray-900">{{ $publication->published_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Publicado por</label>
                                <div class="text-gray-900">{{ $publication->publisher->name ?? 'Sistema' }}</div>
                            </div>
                        @endif
                    </div>

                    @if($publication->description)
                        <div class="mt-6">
                            <label class="text-sm text-gray-500">Descripción</label>
                            <p class="text-gray-900">{{ $publication->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Progreso de firmas --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold">Firmas Digitales</h5>
                </div>
                <div class="p-6">
                    @if($signatureProgress['total'] > 0)
                        <div class="mb-6">
                            <div class="flex justify-between mb-2">
                                <span class="font-semibold text-gray-900">Progreso</span>
                                <span class="text-gray-500">
                                    {{ $signatureProgress['completed'] }}/{{ $signatureProgress['total'] }} completadas
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-6">
                                <div class="h-6 rounded-full flex items-center justify-center text-xs font-medium text-white
                                    @if($signatureProgress['percentage'] === 100) bg-green-500
                                    @elseif($signatureProgress['percentage'] > 0) bg-yellow-500
                                    @else bg-gray-400
                                    @endif"
                                     style="width: {{ $signatureProgress['percentage'] }}%">
                                    {{ $signatureProgress['percentage'] }}%
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($signatureProgress['signers'] as $signer)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $signer['user'] }}</div>
                                            <small class="text-gray-500">{{ $signer['role'] }}</small>
                                        </div>
                                        <div class="text-right">
                                            @if($signer['status'] === 'signed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check"></i> Firmado
                                                </span>
                                                @if($signer['signed_at'])
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ \Carbon\Carbon::parse($signer['signed_at'])->format('d/m/Y H:i') }}
                                                    </div>
                                                @endif
                                            @elseif($signer['status'] === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock"></i> Pendiente
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($signer['status']) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No hay información de firmas disponible</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold">Acciones</h5>
                </div>
                <div class="p-6 space-y-3">
                    @if($publication->document && $publication->document->signed_pdf_path)
                        <a href="{{ route('admin.results.download-pdf', $publication) }}"
                           class="w-full px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-file-pdf mr-2"></i> Descargar PDF Firmado
                        </a>
                    @endif

                    @if($publication->excel_path)
                        <a href="{{ route('admin.results.download-excel', $publication) }}"
                           class="w-full px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-file-excel mr-2"></i> Descargar Excel
                        </a>
                    @else
                        <form action="{{ route('admin.results.generate-excel', $publication) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50 transition-colors">
                                <i class="fas fa-file-excel mr-2"></i> Generar Excel
                            </button>
                        </form>
                    @endif

                    <hr class="my-4">

                    @if($publication->status->value === 'pending_signature' && $publication->canBeUnpublished())
                        <form action="{{ route('admin.results.unpublish', $publication) }}" method="POST"
                              onsubmit="return confirm('¿Está seguro de despublicar estos resultados?')">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 border border-yellow-600 text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors">
                                <i class="fas fa-eye-slash mr-2"></i> Despublicar
                            </button>
                        </form>
                    @endif

                    @if($publication->canBeRepublished())
                        <form action="{{ route('admin.results.republish', $publication) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <i class="fas fa-eye mr-2"></i> Republicar
                            </button>
                        </form>
                    @endif

                    @if($publication->status->value === 'published')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <i class="fas fa-check-circle text-green-600"></i>
                            <strong class="text-green-800">Publicado</strong><br>
                            <span class="text-green-700 text-sm">Los postulantes pueden ver estos resultados</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Información del documento --}}
            @if($publication->document)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-semibold">Documento</h5>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="text-sm text-gray-500">Estado del Documento</label>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $publication->document->signature_status === 'signed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($publication->document->signature_status ?? 'draft') }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Creado</label>
                            <div class="text-gray-900">{{ $publication->document->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
