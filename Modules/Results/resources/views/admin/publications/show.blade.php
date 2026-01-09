@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $publication->title }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $publication->status->color() }} me-2">
                    {{ $publication->status->label() }}
                </span>
                {{ $publication->phase->label() }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Información principal --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Convocatoria</label>
                            <div class="fw-bold">{{ $publication->jobPosting->code }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Fase</label>
                            <div>{{ $publication->phase->label() }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Total Postulantes</label>
                            <div class="fw-bold">{{ $publication->total_applicants }}</div>
                        </div>
                        @if($publication->phase->value === 'PHASE_04')
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Resultados</label>
                                <div>
                                    <span class="badge bg-success">{{ $publication->total_eligible }} APTOS</span>
                                    <span class="badge bg-danger ms-2">{{ $publication->total_not_eligible }} NO APTOS</span>
                                </div>
                            </div>
                        @endif
                        @if($publication->published_at)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Fecha de Publicación</label>
                                <div>{{ $publication->published_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Publicado por</label>
                                <div>{{ $publication->publisher->name ?? 'Sistema' }}</div>
                            </div>
                        @endif
                    </div>

                    @if($publication->description)
                        <div class="mt-3">
                            <label class="text-muted small">Descripción</label>
                            <p class="mb-0">{{ $publication->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Progreso de firmas --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Firmas Digitales</h5>
                </div>
                <div class="card-body">
                    @if($signatureProgress['total'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Progreso</span>
                                <span class="text-muted">
                                    {{ $signatureProgress['completed'] }}/{{ $signatureProgress['total'] }} completadas
                                </span>
                            </div>
                            <div class="progress" style="height: 24px;">
                                <div class="progress-bar
                                    @if($signatureProgress['percentage'] === 100) bg-success
                                    @elseif($signatureProgress['percentage'] > 0) bg-warning
                                    @else bg-secondary
                                    @endif"
                                     role="progressbar"
                                     style="width: {{ $signatureProgress['percentage'] }}%">
                                    {{ $signatureProgress['percentage'] }}%
                                </div>
                            </div>
                        </div>

                        <div class="list-group">
                            @foreach($signatureProgress['signers'] as $signer)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $signer['user'] }}</div>
                                            <small class="text-muted">{{ $signer['role'] }}</small>
                                        </div>
                                        <div class="text-end">
                                            @if($signer['status'] === 'signed')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Firmado
                                                </span>
                                                @if($signer['signed_at'])
                                                    <div class="small text-muted">
                                                        {{ \Carbon\Carbon::parse($signer['signed_at'])->format('d/m/Y H:i') }}
                                                    </div>
                                                @endif
                                            @elseif($signer['status'] === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Pendiente
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($signer['status']) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No hay información de firmas disponible</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    @if($publication->document && $publication->document->signed_pdf_path)
                        <a href="{{ route('admin.results.download-pdf', $publication) }}"
                           class="btn btn-outline-danger w-100 mb-2">
                            <i class="fas fa-file-pdf"></i> Descargar PDF Firmado
                        </a>
                    @endif

                    @if($publication->excel_path)
                        <a href="{{ route('admin.results.download-excel', $publication) }}"
                           class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-file-excel"></i> Descargar Excel
                        </a>
                    @else
                        <form action="{{ route('admin.results.generate-excel', $publication) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-file-excel"></i> Generar Excel
                            </button>
                        </form>
                    @endif

                    <hr>

                    @if($publication->status->value === 'pending_signature' && $publication->canBeUnpublished())
                        <form action="{{ route('admin.results.unpublish', $publication) }}" method="POST"
                              onsubmit="return confirm('¿Está seguro de despublicar estos resultados?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-eye-slash"></i> Despublicar
                            </button>
                        </form>
                    @endif

                    @if($publication->canBeRepublished())
                        <form action="{{ route('admin.results.republish', $publication) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-eye"></i> Republicar
                            </button>
                        </form>
                    @endif

                    @if($publication->status->value === 'published')
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i>
                            <strong>Publicado</strong><br>
                            Los postulantes pueden ver estos resultados
                        </div>
                    @endif
                </div>
            </div>

            {{-- Información del documento --}}
            @if($publication->document)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Documento</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="text-muted small">Estado del Documento</label>
                            <div>
                                <span class="badge bg-{{ $publication->document->signature_status === 'signed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($publication->document->signature_status ?? 'draft') }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted small">Creado</label>
                            <div>{{ $publication->document->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
