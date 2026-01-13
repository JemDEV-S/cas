@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold mb-1">Publicar Resultados de Elegibilidad</h2>
            <p class="text-gray-500 text-sm">
                Fase 4 - Evaluación de Requisitos Mínimos
            </p>
        </div>
        <div>
            <a href="{{ route('admin.results.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </div>

    {{-- Información de la convocatoria --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 bg-blue-600 text-white rounded-t-lg">
            <h5 class="text-lg font-semibold">
                <i class="fas fa-briefcase"></i> Convocatoria
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-500">Código de Convocatoria</label>
                    <div class="font-semibold text-gray-900">{{ $posting->code }}</div>
                </div>
                <div>
                    <label class="text-sm text-gray-500">Postulaciones Evaluadas</label>
                    <div class="font-semibold text-gray-900">{{ $evaluatedCount }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <form action="{{ route('admin.results.store-phase4', $posting) }}" method="POST" id="publishForm">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold">
                    <i class="fas fa-pen-fancy"></i> Configuración de Firmas Digitales
                </h5>
            </div>
            <div class="p-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <i class="fas fa-info-circle text-blue-600"></i>
                    <strong class="text-blue-800">Importante:</strong> <span class="text-blue-700">Los resultados solo se publicarán automáticamente cuando todos los jurados
                    hayan firmado el documento digitalmente.</span>
                </div>

                {{-- Modo de firma --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">Modo de Firma *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-check-card">
                            <input class="hidden peer" type="radio" name="signature_mode"
                                   id="mode_sequential" value="sequential" checked>
                            <label class="block cursor-pointer" for="mode_sequential">
                                <div class="border-2 border-gray-300 rounded-lg p-4 transition-all hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                    <h6 class="font-semibold text-gray-900 mb-2"><i class="fas fa-arrow-right text-blue-600"></i> Secuencial</h6>
                                    <p class="text-gray-600 text-sm">
                                        Los jurados firman uno después del otro en el orden especificado
                                    </p>
                                </div>
                            </label>
                        </div>
                        <div class="form-check-card">
                            <input class="hidden peer" type="radio" name="signature_mode"
                                   id="mode_parallel" value="parallel">
                            <label class="block cursor-pointer" for="mode_parallel">
                                <div class="border-2 border-gray-300 rounded-lg p-4 transition-all hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-blue-50">
                                    <h6 class="font-semibold text-gray-900 mb-2"><i class="fas fa-layer-group text-blue-600"></i> Paralelo</h6>
                                    <p class="text-gray-600 text-sm">
                                        Todos los jurados pueden firmar al mismo tiempo
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Jurados firmantes --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Jurados Firmantes *</label>
                    <p class="text-gray-600 text-sm mb-4">Seleccione los jurados que deben firmar el acta de resultados (mínimo 2)</p>

                    <div id="signers-container" class="space-y-3">
                        {{-- Jurado 1 --}}
                        <div class="bg-white border border-gray-200 rounded-lg p-4 signer-card">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jurado</label>
                                    <select name="jury_signers[0][user_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Seleccione un jurado</option>
                                        @foreach($jurors as $juror)
                                            <option value="{{ $juror->id }}">{{ $juror->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                                    <input type="text" name="jury_signers[0][role]"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="Presidente del Jurado" required>
                                </div>
                                <div class="md:col-span-1">
                                    <button type="button" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors remove-signer" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Jurado 2 --}}
                        <div class="bg-white border border-gray-200 rounded-lg p-4 signer-card">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jurado</label>
                                    <select name="jury_signers[1][user_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Seleccione un jurado</option>
                                        @foreach($jurors as $juror)
                                            <option value="{{ $juror->id }}">{{ $juror->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                                    <input type="text" name="jury_signers[1][role]"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="Jurado Titular" required>
                                </div>
                                <div class="md:col-span-1">
                                    <button type="button" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors remove-signer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="mt-4 px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" id="add-signer">
                        <i class="fas fa-plus"></i> Agregar Jurado
                    </button>
                </div>

                {{-- Notificaciones --}}
                <div class="flex items-start">
                    <input class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" name="send_notifications"
                           id="send_notifications" value="1" checked>
                    <label class="ml-3 block" for="send_notifications">
                        <strong class="text-gray-900">Enviar notificaciones a postulantes</strong>
                        <p class="text-gray-600 text-sm">
                            Se enviará un correo electrónico a cada postulante cuando los resultados sean publicados
                        </p>
                    </label>
                </div>
            </div>
        </div>

        {{-- Confirmación --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-start">
                    <input class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" id="confirm" required>
                    <label class="ml-3 block text-gray-700" for="confirm">
                        Confirmo que he revisado las postulaciones y deseo publicar los resultados de elegibilidad.
                        Entiendo que el documento será enviado a los jurados para firma digital.
                    </label>
                </div>
            </div>
        </div>

        {{-- Botones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.results.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg font-semibold">
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
            <div class="bg-white border border-gray-200 rounded-lg p-4 signer-card">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jurado</label>
                        <select name="jury_signers[${signerIndex}][user_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Seleccione un jurado</option>
                            ${jurors.map(j => `<option value="${j.id}">${j.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="md:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                        <input type="text" name="jury_signers[${signerIndex}][role]"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="Jurado Titular ${signerIndex}" required>
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors remove-signer">
                            <i class="fas fa-trash"></i>
                        </button>
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
@endsection
