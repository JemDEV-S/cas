@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Publicaciones de Resultados</h2>
            <p class="text-muted mb-0">Gestión de resultados con firma digital</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fase</label>
                    <select name="phase" class="form-select">
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
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending_signature" {{ request('status') === 'pending_signature' ? 'selected' : '' }}>
                            Pendiente de Firma
                        </option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicado</option>
                        <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Despublicado</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Lista de publicaciones --}}
    <div class="card">
        <div class="card-body">
            @if($publications->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay publicaciones de resultados</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Convocatoria</th>
                                <th>Fase</th>
                                <th>Estado</th>
                                <th>Total Postulantes</th>
                                <th>Firmas</th>
                                <th>Publicado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($publications as $publication)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $publication->jobPosting->code ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ Str::limit($publication->title, 50) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $publication->phase->label() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $publication->status->color() }}">
                                        {{ $publication->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $publication->total_applicants }}</div>
                                    @if($publication->phase->value === 'PHASE_04')
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> {{ $publication->total_eligible }} APTOS
                                        </small>
                                        <small class="text-danger ms-2">
                                            <i class="fas fa-times-circle"></i> {{ $publication->total_not_eligible }} NO APTOS
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $progress = $publication->getSignatureProgress();
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px; width: 80px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: {{ $progress['percentage'] }}%"
                                                 aria-valuenow="{{ $progress['percentage'] }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $progress['completed'] }}/{{ $progress['total'] }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($publication->published_at)
                                        <div>{{ $publication->published_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $publication->published_at->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.results.show', $publication) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($publication->document && $publication->document->signed_pdf_path)
                                            <a href="{{ route('admin.results.download-pdf', $publication) }}"
                                               class="btn btn-sm btn-outline-success"
                                               title="Descargar PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif

                                        @if($publication->excel_path)
                                            <a href="{{ route('admin.results.download-excel', $publication) }}"
                                               class="btn btn-sm btn-outline-success"
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
                <div class="mt-3">
                    {{ $publications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
