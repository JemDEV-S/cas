@extends('layouts.app')

@section('title', 'Firmar Documento Digitalmente')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Firma Digital - {{ $document->title }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Visualizador de PDF -->
                            <div class="border rounded p-3 mb-3" style="min-height: 600px;">
                                <iframe
                                    src="{{ route('documents.view', $document) }}"
                                    width="100%"
                                    height="600px"
                                    frameborder="0">
                                </iframe>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Información del Documento -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5>Información del Documento</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Código:</strong> {{ $document->code }}</p>
                                    <p><strong>Template:</strong> {{ $document->template->name }}</p>
                                    <p><strong>Generado:</strong> {{ $document->generated_at->format('d/m/Y H:i') }}</p>
                                    <p><strong>Estado:</strong>
                                        <span class="badge bg-warning">{{ $document->status_label }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Flujo de Firmas -->
                            @if($workflow)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5>Flujo de Firmas</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Tipo:</strong> {{ $workflow->workflow_type_label }}</p>
                                    <p><strong>Progreso:</strong> {{ $workflow->current_step }} / {{ $workflow->total_steps }}</p>

                                    <div class="progress mb-3">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $workflow->progress_percentage }}%">
                                            {{ number_format($workflow->progress_percentage, 0) }}%
                                        </div>
                                    </div>

                                    <h6>Firmantes:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($document->signatures as $sig)
                                        <li class="mb-2">
                                            <i class="fas fa-{{ $sig->isSigned() ? 'check-circle text-success' : 'clock text-warning' }}"></i>
                                            {{ $sig->user->name }}
                                            <br>
                                            <small class="text-muted">{{ $sig->signature_type_label }}</small>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @endif

                            <!-- Botón de Firma -->
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="mb-3">Firmar Documento</h5>
                                    <p class="text-muted">
                                        Al hacer clic en "Iniciar Firma", se abrirá el componente de FIRMA PERÚ
                                        para que pueda firmar digitalmente este documento.
                                    </p>

                                    <button type="button" class="btn btn-primary btn-lg" onclick="iniciarFirma()">
                                        <i class="fas fa-pen-nib"></i> Iniciar Firma
                                    </button>

                                    <form action="{{ route('documents.sign.cancel', $document) }}" method="POST" class="mt-3">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Componente oculto requerido por FIRMA PERÚ -->
<div id="addComponent" style="display:none;"></div>
@endsection

@push('scripts')
<!-- jQuery 3.6.0 requerido por FIRMA PERÚ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Componente Web de FIRMA PERÚ -->
<script src="https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js"></script>

<script>
// Variable requerida por firmaperu.min.js
var jqFirmaPeru = jQuery.noConflict(true);

// Funciones requeridas por FIRMA PERÚ
function signatureInit() {
    console.log('FIRMA PERÚ: Proceso de firma iniciado');
    Swal.fire({
        title: 'Proceso Iniciado',
        text: 'Iniciando el proceso de firma digital...',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
    });
}

function signatureOk() {
    console.log('FIRMA PERÚ: Documento firmado exitosamente');
    Swal.fire({
        title: '¡Firma Exitosa!',
        text: 'El documento ha sido firmado digitalmente.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        window.location.href = '{{ route("documents.index") }}';
    });
}

function signatureCancel() {
    console.log('FIRMA PERÚ: Operación cancelada');
    Swal.fire({
        title: 'Operación Cancelada',
        text: 'La firma digital ha sido cancelada.',
        icon: 'warning',
        confirmButtonText: 'Aceptar'
    });
}

// Función para iniciar el proceso de firma
function iniciarFirma() {
    // Generar token único para esta sesión de firma
    const signatureToken = '{{ Str::random(32) }}';

    // Guardar en sesión los IDs necesarios
    fetch('{{ route("documents.sign", $document) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            signature_token: signatureToken,
            document_id: '{{ $document->id }}',
            signature_id: '{{ $signature->id }}'
        })
    });

    // Parámetros para FIRMA PERÚ
    const param = {
        "param_url": "{{ route('api.documents.signature-params') }}",
        "param_token": signatureToken,
        "document_extension": "pdf"
    };

    // Iniciar FIRMA PERÚ
    const port = {{ config('document.firmaperu.local_port', 48596) }};
    const paramBase64 = btoa(JSON.stringify(param));

    console.log('Iniciando FIRMA PERÚ en puerto:', port);
    console.log('Parámetros:', param);

    // Llamar a la función del componente web de FIRMA PERÚ
    startSignature(port, paramBase64);
}
</script>
@endpush
