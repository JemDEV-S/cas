@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Publicar Resultados de Elegibilidad</h2>
            <p class="text-muted mb-0">
                Fase 4 - Evaluación de Requisitos Mínimos
            </p>
        </div>
        <div>
            <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </div>

    {{-- Información de la convocatoria --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-briefcase"></i> Convocatoria
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="text-muted small">Código de Convocatoria</label>
                    <div class="fw-bold">{{ $posting->code }}</div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small">Postulaciones Evaluadas</label>
                    <div class="fw-bold">{{ $evaluatedCount }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <form action="{{ route('admin.results.store-phase4', $posting) }}" method="POST" id="publishForm">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-pen-fancy"></i> Configuración de Firmas Digitales
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Importante:</strong> Los resultados solo se publicarán automáticamente cuando todos los jurados
                    hayan firmado el documento digitalmente.
                </div>

                {{-- Modo de firma --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Modo de Firma *</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-check-card">
                                <input class="form-check-input" type="radio" name="signature_mode"
                                       id="mode_sequential" value="sequential" checked>
                                <label class="form-check-label w-100" for="mode_sequential">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><i class="fas fa-arrow-right"></i> Secuencial</h6>
                                            <p class="text-muted small mb-0">
                                                Los jurados firman uno después del otro en el orden especificado
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-card">
                                <input class="form-check-input" type="radio" name="signature_mode"
                                       id="mode_parallel" value="parallel">
                                <label class="form-check-label w-100" for="mode_parallel">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><i class="fas fa-layer-group"></i> Paralelo</h6>
                                            <p class="text-muted small mb-0">
                                                Todos los jurados pueden firmar al mismo tiempo
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Jurados firmantes --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Jurados Firmantes *</label>
                    <p class="text-muted small">Seleccione los jurados que deben firmar el acta de resultados (mínimo 2)</p>

                    <div id="signers-container">
                        {{-- Jurado 1 --}}
                        <div class="card mb-3 signer-card">
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">Jurado</label>
                                        <select name="jury_signers[0][user_id]" class="form-select" required>
                                            <option value="">Seleccione un jurado</option>
                                            @foreach($jurors as $juror)
                                                <option value="{{ $juror->id }}">{{ $juror->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Rol</label>
                                        <input type="text" name="jury_signers[0][role]"
                                               class="form-control" value="Presidente del Jurado" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger remove-signer" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Jurado 2 --}}
                        <div class="card mb-3 signer-card">
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">Jurado</label>
                                        <select name="jury_signers[1][user_id]" class="form-select" required>
                                            <option value="">Seleccione un jurado</option>
                                            @foreach($jurors as $juror)
                                                <option value="{{ $juror->id }}">{{ $juror->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Rol</label>
                                        <input type="text" name="jury_signers[1][role]"
                                               class="form-control" value="Jurado Titular" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger remove-signer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary" id="add-signer">
                        <i class="fas fa-plus"></i> Agregar Jurado
                    </button>
                </div>

                {{-- Notificaciones --}}
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="send_notifications"
                           id="send_notifications" value="1" checked>
                    <label class="form-check-label" for="send_notifications">
                        <strong>Enviar notificaciones a postulantes</strong>
                        <p class="text-muted small mb-0">
                            Se enviará un correo electrónico a cada postulante cuando los resultados sean publicados
                        </p>
                    </label>
                </div>
            </div>
        </div>

        {{-- Confirmación --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirm" required>
                    <label class="form-check-label" for="confirm">
                        Confirmo que he revisado las postulaciones y deseo publicar los resultados de elegibilidad.
                        Entiendo que el documento será enviado a los jurados para firma digital.
                    </label>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.results.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-check"></i> Publicar Resultados
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let signerIndex = 2;

    // Agregar firmante
    document.getElementById('add-signer').addEventListener('click', function() {
        const container = document.getElementById('signers-container');
        const jurors = @json($jurors);

        const html = `
            <div class="card mb-3 signer-card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Jurado</label>
                            <select name="jury_signers[${signerIndex}][user_id]" class="form-select" required>
                                <option value="">Seleccione un jurado</option>
                                ${jurors.map(j => `<option value="${j.id}">${j.name}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Rol</label>
                            <input type="text" name="jury_signers[${signerIndex}][role]"
                                   class="form-control" value="Jurado Titular ${signerIndex}" required>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger remove-signer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        signerIndex++;
        updateRemoveButtons();
    });

    // Eliminar firmante
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-signer')) {
            e.target.closest('.signer-card').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const cards = document.querySelectorAll('.signer-card');
        cards.forEach((card, index) => {
            const btn = card.querySelector('.remove-signer');
            btn.disabled = index === 0 && cards.length === 1;
        });
    }

    // Validar formulario
    document.getElementById('publishForm').addEventListener('submit', function(e) {
        const selectedJurors = Array.from(document.querySelectorAll('select[name^="jury_signers"]'))
            .map(select => select.value)
            .filter(value => value);

        if (selectedJurors.length < 2) {
            e.preventDefault();
            alert('Debe seleccionar al menos 2 jurados firmantes');
            return false;
        }

        // Verificar duplicados
        const uniqueJurors = new Set(selectedJurors);
        if (uniqueJurors.size !== selectedJurors.length) {
            e.preventDefault();
            alert('No puede seleccionar el mismo jurado más de una vez');
            return false;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.form-check-card .card {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid #dee2e6;
}

.form-check-card input:checked ~ label .card {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.form-check-card .card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
@endpush
@endsection
