@extends('layouts.app')

@section('title', 'Crear Jurado')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Card Header con breadcrumb -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('jury-members.index') }}" class="text-decoration-none">Jurados</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Nuevo Jurado</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-primary">
                        <i class="fas fa-user-tie me-2"></i>
                        Registrar Nuevo Jurado
                    </h1>
                    <p class="text-muted mb-0">Complete la información del nuevo jurado evaluador</p>
                </div>
                <div class="avatar-circle bg-primary-subtle d-none d-md-flex">
                    <i class="fas fa-user-plus fa-lg text-primary"></i>
                </div>
            </div>

            <!-- Tarjeta principal del formulario -->
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-id-card me-2"></i>
                        Información del Jurado
                    </h5>
                </div>

                <div class="card-body p-4">
                    <!-- Notificaciones de validación -->
                    <div id="form-alerts" class="d-none"></div>

                    <form id="createJuryMemberForm" class="needs-validation" novalidate>
                        @csrf

                        <!-- Sección: Información del Usuario -->
                        <div class="section-card mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-primary-subtle text-primary me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h6 class="mb-0 text-primary">Información del Usuario</h6>
                            </div>

                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-semibold">
                                    Usuario <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <select name="user_id" id="user_id" class="form-select form-select-lg" required>
                                        <option value="" selected disabled>Seleccione un usuario...</option>
                                        @foreach(\App\Models\User::all() as $user)
                                            <option value="{{ $user->id }}"
                                                    data-email="{{ $user->email }}"
                                                    data-name="{{ ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') }}">
                                                {{ $user->first_name ?? $user->email }}
                                                {{ $user->last_name ?? '' }}
                                                ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Selecciona el usuario que será jurado evaluador
                                </div>
                                <div class="invalid-feedback">
                                    Por favor seleccione un usuario.
                                </div>
                            </div>

                            <div id="userPreview" class="card border-light p-3 mb-3 d-none">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Nombre:</strong> <span id="previewName"></span></p>
                                        <p class="mb-1"><strong>Email:</strong> <span id="previewEmail"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Usuario ID:</strong> <span id="previewId"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Especialidad y Experiencia -->
                        <div class="section-card mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-info-subtle text-info me-3">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h6 class="mb-0 text-primary">Especialidad y Experiencia</h6>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="specialty" class="form-label fw-semibold">
                                        <i class="fas fa-tags me-1"></i>Especialidad
                                    </label>
                                    <input type="text" name="specialty" id="specialty" class="form-control"
                                           placeholder="Ej: Derecho Administrativo, Gestión Pública, Recursos Humanos...">
                                    <div class="form-text">
                                        Campo opcional para especificar la especialidad principal
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="experience_years" class="form-label fw-semibold">
                                        <i class="fas fa-calendar-alt me-1"></i>Años de Experiencia
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="experience_years" id="experience_years"
                                               class="form-control" min="0" max="50" value="0">
                                        <span class="input-group-text">años</span>
                                    </div>
                                    <div class="form-text">
                                        Años de experiencia en evaluación
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="max_evaluations" class="form-label fw-semibold">
                                        <i class="fas fa-chart-line me-1"></i>Capacidad Máxima
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="max_evaluations" id="max_evaluations"
                                               class="form-control" min="1" max="20" value="10">
                                        <span class="input-group-text">evaluaciones</span>
                                    </div>
                                    <div class="form-text">
                                        Máximo de evaluaciones simultáneas permitidas
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Estado y Configuración -->
                        <div class="section-card mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-warning-subtle text-warning me-3">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h6 class="mb-0 text-primary">Estado y Configuración</h6>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check card-checkbox">
                                        <input type="checkbox" name="is_active" class="form-check-input"
                                               id="isActive" checked>
                                        <label class="form-check-label d-flex align-items-center" for="isActive">
                                            <span class="status-indicator bg-success me-2"></span>
                                            <div>
                                                <strong>Activo</strong>
                                                <div class="form-text">El jurado puede recibir asignaciones</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check card-checkbox">
                                        <input type="checkbox" name="is_available" class="form-check-input"
                                               id="isAvailable" checked>
                                        <label class="form-check-label d-flex align-items-center" for="isAvailable">
                                            <span class="status-indicator bg-info me-2"></span>
                                            <div>
                                                <strong>Disponible</strong>
                                                <div class="form-text">Disponible para nuevas evaluaciones</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check card-checkbox">
                                        <input type="checkbox" name="training_completed" class="form-check-input"
                                               id="trainingCompleted">
                                        <label class="form-check-label d-flex align-items-center" for="trainingCompleted">
                                            <span class="status-indicator bg-primary me-2"></span>
                                            <div>
                                                <strong>Capacitación</strong>
                                                <div class="form-text">Capacitación completada</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo condicional para certificado -->
                            <div class="mt-3" id="certificateField" style="display: none;">
                                <label for="training_certificate_path" class="form-label fw-semibold">
                                    <i class="fas fa-file-certificate me-1"></i>Certificado de Capacitación
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-paperclip"></i>
                                    </span>
                                    <input type="text" name="training_certificate_path" id="training_certificate_path"
                                           class="form-control" placeholder="ruta/del/certificado.pdf">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('training_certificate_path').value = ''">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Ruta del certificado de capacitación (opcional)
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Notas Adicionales -->
                        <div class="section-card mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-success-subtle text-success me-3">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <h6 class="mb-0 text-primary">Notas Adicionales</h6>
                            </div>

                            <div class="mb-0">
                                <label for="notes" class="form-label fw-semibold">
                                    <i class="fas fa-edit me-1"></i>Observaciones
                                </label>
                                <textarea name="notes" id="notes" class="form-control" rows="3"
                                          placeholder="Información adicional relevante sobre el jurado, observaciones, restricciones, etc."></textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Información adicional para referencia</span>
                                    <span id="notesCounter">0/500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <a href="{{ route('jury-members.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                            <div>
                                <button type="reset" class="btn btn-light me-2 px-4">
                                    <i class="fas fa-redo me-2"></i>Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="btnSubmit">
                                    <span id="btnText">
                                        <i class="fas fa-save me-2"></i>Crear Jurado
                                    </span>
                                    <span id="btnSpinner" class="d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Creando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Pie de tarjeta con información -->
                <div class="card-footer bg-light py-3">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Sistema de Evaluación
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.section-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1.5rem;
    border-left: 4px solid #4e73df;
    transition: all 0.3s ease;
}

.section-card:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.card-checkbox {
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.card-checkbox:hover {
    border-color: #4e73df;
    background: rgba(78, 115, 223, 0.05);
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.form-control:focus, .form-select:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
}

.input-group-text {
    background-color: #f8fafc;
    border-color: #e2e8f0;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

#userPreview {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const form = document.getElementById('createJuryMemberForm');
    const userSelect = document.getElementById('user_id');
    const userPreview = document.getElementById('userPreview');
    const previewName = document.getElementById('previewName');
    const previewEmail = document.getElementById('previewEmail');
    const previewId = document.getElementById('previewId');
    const trainingCheckbox = document.getElementById('trainingCompleted');
    const certificateField = document.getElementById('certificateField');
    const notesTextarea = document.getElementById('notes');
    const notesCounter = document.getElementById('notesCounter');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const formAlerts = document.getElementById('form-alerts');

    // Mostrar vista previa del usuario seleccionado
    userSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            previewName.textContent = selectedOption.dataset.name.trim() || 'No especificado';
            previewEmail.textContent = selectedOption.dataset.email;
            previewId.textContent = this.value;
            userPreview.classList.remove('d-none');
        } else {
            userPreview.classList.add('d-none');
        }
    });

    // Mostrar/ocultar campo de certificado
    trainingCheckbox.addEventListener('change', function() {
        certificateField.style.display = this.checked ? 'block' : 'none';
        if (this.checked) {
            certificateField.style.animation = 'fadeIn 0.5s ease';
        }
    });

    // Contador de caracteres para notas
    notesTextarea.addEventListener('input', function() {
        const length = this.value.length;
        notesCounter.textContent = `${length}/500`;

        if (length > 450) {
            notesCounter.style.color = length > 500 ? '#dc3545' : '#ffc107';
        } else {
            notesCounter.style.color = '#6c757d';
        }
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            e.stopPropagation();
            showAlert('Por favor complete los campos obligatorios correctamente.', 'warning');
            form.classList.add('was-validated');
            return;
        }

        // Deshabilitar botón y mostrar spinner
        btnSubmit.disabled = true;
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');

        // Preparar datos del formulario
        const formData = new FormData(form);

        // Convertir checkboxes a valores booleanos
        formData.set('is_active', document.getElementById('isActive').checked ? '1' : '0');
        formData.set('is_available', document.getElementById('isAvailable').checked ? '1' : '0');
        formData.set('training_completed', trainingCheckbox.checked ? '1' : '0');

        // Enviar solicitud
        fetch('{{ route('jury-members.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('¡Jurado creado exitosamente! Redirigiendo...', 'success');

                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = '{{ route('jury-members.index') }}';
                }, 1500);
            } else {
                throw new Error(data.message || 'Error al crear el jurado');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert(error.message || 'Error al procesar la solicitud', 'danger');

            // Restaurar botón
            btnSubmit.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        });
    });

    // Función para mostrar alertas
    function showAlert(message, type) {
        const alertClass = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        formAlerts.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        formAlerts.classList.remove('d-none');

        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            const alert = formAlerts.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => formAlerts.classList.add('d-none'), 150);
            }
        }, 5000);
    }

    // Limpiar formulario
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        form.classList.remove('was-validated');
        userPreview.classList.add('d-none');
        certificateField.style.display = 'none';
        trainingCheckbox.checked = false;
        notesCounter.textContent = '0/500';
        showAlert('Formulario restablecido', 'info');
    });
});
</script>
@endsection