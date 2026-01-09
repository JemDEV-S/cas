@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="mb-1">Mis Resultados</h2>
        <p class="text-muted mb-0">
            Consulta los resultados de tus postulaciones
        </p>
    </div>

    @if(empty($results))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay resultados publicados</h5>
                <p class="text-muted">
                    Aún no se han publicado resultados para tus postulaciones.
                    Te notificaremos por correo cuando estén disponibles.
                </p>
            </div>
        </div>
    @else
        @foreach($results as $result)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase"></i>
                        {{ $result['posting']->code }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Mi Postulación</label>
                            <div class="fw-bold">{{ $result['application']->code }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Vacante</label>
                            <div>{{ $result['application']->vacancy->code }}</div>
                        </div>
                    </div>

                    @if($result['publications']->isNotEmpty())
                        <h6 class="mb-3">Resultados Publicados</h6>
                        <div class="list-group">
                            @foreach($result['publications'] as $publication)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-info me-2">
                                                    {{ $publication->phase->label() }}
                                                </span>
                                                <span class="text-muted small">
                                                    Publicado el {{ $publication->published_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="fw-bold">{{ $publication->title }}</div>
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('applicant.results.show', $publication) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Ver Resultado
                                            </a>
                                            @if($publication->document && $publication->document->signed_pdf_path)
                                                <a href="{{ route('applicant.results.download-pdf', $publication) }}"
                                                   class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
