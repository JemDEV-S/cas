@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $publication->phase->label() }}</h2>
            <p class="text-muted mb-0">{{ $publication->jobPosting->code }}</p>
        </div>
        <div>
            <a href="{{ route('applicant.results.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Resultado principal --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-3">Tu Resultado</h4>

                    @if($publication->phase->value === 'PHASE_04')
                        {{-- Fase 4: APTO/NO APTO --}}
                        <div class="alert alert-{{ $resultData['result_class'] }} mb-0">
                            <h3 class="mb-2">
                                @if($resultData['result'] === 'APTO')
                                    <i class="fas fa-check-circle"></i> APTO
                                @else
                                    <i class="fas fa-times-circle"></i> NO APTO
                                @endif
                            </h3>
                            <p class="mb-0">
                                @if($resultData['result'] === 'APTO')
                                    <strong>¡Felicidades!</strong> Has cumplido con los requisitos mínimos de la convocatoria.
                                @else
                                    No cumples con uno o más requisitos mínimos de la convocatoria.
                                @endif
                            </p>
                            @if($resultData['reason'])
                                <hr>
                                <p class="mb-0">
                                    <strong>Motivo:</strong><br>
                                    {{ $resultData['reason'] }}
                                </p>
                            @endif
                        </div>

                    @elseif($publication->phase->value === 'PHASE_07')
                        {{-- Fase 7: Ranking Curricular --}}
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Tu Ranking</h6>
                                        <h2 class="mb-0 text-primary">
                                            <i class="fas fa-trophy"></i> {{ $resultData['ranking'] }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Puntaje Curricular</h6>
                                        <h2 class="mb-0 text-success">
                                            {{ number_format($resultData['curriculum_score'], 2) }}
                                        </h2>
                                        <small class="text-muted">de {{ $resultData['max_score'] }} puntos</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif($publication->phase->value === 'PHASE_09')
                        {{-- Fase 9: Resultados Finales --}}
                        @if($resultData['is_winner'])
                            <div class="alert alert-success mb-3">
                                <h4><i class="fas fa-trophy"></i> ¡FELICIDADES!</h4>
                                <p class="mb-0">Has obtenido el <strong>PRIMER LUGAR</strong> en esta convocatoria.</p>
                            </div>
                        @endif

                        <div class="row text-center mb-3">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted small mb-2">Ranking Final</h6>
                                        <h3 class="mb-0 {{ $resultData['is_winner'] ? 'text-warning' : 'text-primary' }}">
                                            <i class="fas fa-trophy"></i> {{ $resultData['ranking'] }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted small mb-2">P. Curricular</h6>
                                        <h4 class="mb-0">{{ number_format($resultData['curriculum_score'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted small mb-2">P. Entrevista</h6>
                                        <h4 class="mb-0">{{ number_format($resultData['interview_score'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="text-muted small mb-2">Bonificación</h6>
                                        <h4 class="mb-0">{{ number_format($resultData['bonus'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h6 class="mb-2">Puntaje Final</h6>
                                <h2 class="mb-0">{{ number_format($resultData['final_score'], 2) }}</h2>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">Información</h6>
                            <div class="mb-2">
                                <small class="text-muted">Publicado el</small>
                                <div>{{ $publication->published_at->format('d/m/Y') }}</div>
                                <div class="text-muted small">{{ $publication->published_at->format('H:i') }} hrs</div>
                            </div>
                            @if($publication->document && $publication->document->signed_pdf_path)
                                <hr>
                                <a href="{{ route('applicant.results.download-pdf', $publication) }}"
                                   class="btn btn-danger w-100">
                                    <i class="fas fa-file-pdf"></i> Descargar Acta Oficial
                                </a>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-certificate"></i> Documento con firmas digitales
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Información adicional según fase --}}
    @if($publication->phase->value === 'PHASE_04' && $resultData['result'] === 'APTO')
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Próximos Pasos</h5>
            <p class="mb-0">
                Has sido declarado APTO para continuar en el proceso. Estarás habilitado para las siguientes fases
                de evaluación. Te mantendremos informado sobre las siguientes etapas del proceso.
            </p>
        </div>
    @elseif($publication->phase->value === 'PHASE_04' && $resultData['result'] === 'NO APTO')
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Información Importante</h5>
            <p class="mb-0">
                Lamentablemente no cumples con los requisitos mínimos para continuar en esta convocatoria.
                Puedes postular a otras convocatorias disponibles en nuestro portal.
            </p>
        </div>
    @elseif($publication->phase->value === 'PHASE_09')
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Fin del Proceso</h5>
            <p class="mb-0">
                Estos son los resultados finales del proceso de selección. Agradecemos tu participación.
                @if($resultData['is_winner'])
                    Un representante de la institución se contactará contigo próximamente con las instrucciones
                    para la contratación.
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
