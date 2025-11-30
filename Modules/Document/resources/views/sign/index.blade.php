@extends('layouts.app')

@section('title', 'Firmar Documento Digitalmente')

@section('content')
<div class="w-full px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header con breadcrumb -->
        <div class="mb-6">
            <nav class="text-sm text-gray-600 mb-2">
                <a href="{{ route('documents.index') }}" class="hover:text-blue-600">Documentos</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Firma Digital</span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">Firma Digital del Documento</h1>
            <p class="text-gray-600 mt-1">{{ $document->title }}</p>
        </div>

        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-white">Proceso de Firma Digital</h3>
                            <p class="text-blue-100 text-sm">Código: {{ $document->code }}</p>
                        </div>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        {{ $document->signature_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                        {{ $document->signature_status_label }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    {{-- VISUALIZADOR DEL PDF --}}
                    <div class="lg:col-span-8">
                        <div class="border-2 border-gray-200 rounded-xl overflow-hidden shadow-inner h-[700px]">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Vista Previa del Documento</span>
                                <a href="{{ route('documents.download', $document) }}"
                                   class="text-sm text-blue-600 hover:text-blue-700 flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span>Descargar</span>
                                </a>
                            </div>
                            <iframe
                                src="{{ route('documents.view', $document) }}"
                                class="w-full h-full"
                                frameborder="0">
                            </iframe>
                        </div>
                    </div>

                    {{-- PANEL LATERAL --}}
                    <div class="lg:col-span-4 space-y-4">

                        {{-- Información del documento --}}
                        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                            <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                                <h5 class="font-semibold text-gray-900 flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Información</span>
                                </h5>
                            </div>
                            <div class="px-4 py-4 space-y-3">
                                <div class="flex justify-between items-start">
                                    <span class="text-sm text-gray-600">Template:</span>
                                    <span class="text-sm font-medium text-gray-900 text-right">{{ $document->template->name }}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm text-gray-600">Generado:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $document->generated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm text-gray-600">Por:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $document->generatedBy->name ?? 'Sistema' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Flujo de firmas --}}
                        @if($workflow)
                        <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-xl shadow-sm overflow-hidden">
                            <div class="bg-blue-100 px-4 py-3 border-b border-blue-200">
                                <h5 class="font-semibold text-blue-900 flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Flujo de Firmas</span>
                                </h5>
                            </div>
                            <div class="px-4 py-4 space-y-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="font-medium text-gray-900">{{ $workflow->workflow_type_label }}</span>
                                </div>

                                {{-- Barra de progreso mejorada --}}
                                <div>
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="text-gray-600">Progreso</span>
                                        <span class="font-semibold text-blue-700">{{ $workflow->current_step }} / {{ $workflow->total_steps }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-5 flex items-center justify-center text-white text-xs font-bold transition-all duration-500"
                                             style="width: {{ $workflow->progress_percentage }}%">
                                            {{ number_format($workflow->progress_percentage, 0) }}%
                                        </div>
                                    </div>
                                </div>

                                {{-- Lista de firmantes --}}
                                <div>
                                    <h6 class="font-semibold text-gray-900 mb-3 text-sm">Firmantes:</h6>
                                    <ul class="space-y-2">
                                        @foreach($document->signatures as $index => $sig)
                                        <li class="flex items-start space-x-3 p-2 rounded-lg
                                            {{ $sig->user_id === auth()->id() ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50' }}">
                                            <span class="flex-shrink-0 mt-0.5">
                                                @if($sig->isSigned())
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $sig->user->name }}
                                                    @if($sig->user_id === auth()->id())
                                                        <span class="text-xs text-yellow-700">(Tú)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $sig->signature_type_label }}</p>
                                                @if($sig->signed_at)
                                                    <p class="text-xs text-green-600 mt-1">Firmado: {{ $sig->signed_at->format('d/m/Y H:i') }}</p>
                                                @endif
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PANEL DE FIRMA --}}
                        <div id="signaturePanel" class="bg-gradient-to-br from-green-50 to-white border-2 border-green-200 rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
                                <h5 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <span>Acción Requerida</span>
                                </h5>
                            </div>
                            <div class="px-4 py-5 text-center">
                                <div id="signatureInstructions">
                                    <div class="mb-4">
                                        <svg class="w-16 h-16 mx-auto text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-700 mb-6 text-sm leading-relaxed">
                                        Al hacer clic en <strong>"Iniciar Firma"</strong>, se abrirá el componente de <strong>FIRMA PERÚ</strong>
                                        para que puedas firmar digitalmente con tu certificado o DNIe.
                                    </p>
                                    <button
                                        id="btnIniciarFirma"
                                        onclick="iniciarFirma()"
                                        class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-4 rounded-lg w-full font-bold text-lg shadow-lg transform transition hover:scale-105 flex items-center justify-center space-x-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        <span>Iniciar Firma Digital</span>
                                    </button>
                                </div>

                                <!-- Estado de carga -->
                                <div id="signatureLoading" class="hidden">
                                    <div class="mb-4">
                                        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-green-600"></div>
                                    </div>
                                    <p class="text-gray-700 font-semibold text-lg mb-2" id="loadingMessage">Iniciando firma digital...</p>
                                    <p class="text-gray-600 text-sm" id="loadingSubMessage">Por favor, espera un momento</p>
                                    <div class="mt-4 flex items-center justify-center space-x-2 text-sm text-gray-500">
                                        <div class="h-2 w-2 bg-green-600 rounded-full animate-pulse"></div>
                                        <div class="h-2 w-2 bg-green-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                                        <div class="h-2 w-2 bg-green-600 rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                                    </div>
                                </div>

                                <form action="{{ route('documents.sign.cancel', $document) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 hover:bg-gray-50 font-semibold text-gray-700 transition flex items-center justify-center space-x-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <span>Cancelar Proceso</span>
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

{{-- Componente requerido por Firma Perú --}}
<div id="addComponent" class="hidden"></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js"></script>

<script>
var jqFirmaPeru = jQuery.noConflict(true);

function signatureInit() {
    console.log('Inicio firma');
    updateLoadingState('Iniciando componente de firma...', 'El componente de FIRMA PERÚ se está cargando');
}

function signatureOk() {
    updateLoadingState('¡Firma exitosa!', 'Redirigiendo...', 'success');
    setTimeout(() => {
        window.location.href = '{{ route("documents.index") }}';
    }, 2000);
}

function signatureCancel() {
    console.log('Firma cancelada');
    hideLoading();
    showNotification('Firma cancelada', 'El proceso de firma ha sido cancelado', 'warning');
}

function iniciarFirma() {
    showLoading();
    updateLoadingState('Preparando firma...', 'Generando token de seguridad');

    const signatureToken = generateToken();

    // Primero guardar el token en cache
    fetch('{{ route("documents.sign.start", $document) }}', {
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
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateLoadingState('Conectando con FIRMA PERÚ...', 'Abriendo componente de firma digital');

            // Ahora iniciar el componente de FIRMA PERÚ
            const param = {
                "param_url": "{{ route('api.documents.signature-params') }}",
                "param_token": signatureToken,
                "document_extension": "pdf"
            };

            const port = {{ config('document.firmaperu.local_port', 48596) }};
            const paramBase64 = btoa(JSON.stringify(param));

            startSignature(port, paramBase64);
        } else {
            hideLoading();
            showNotification('Error', data.message || 'Error desconocido al iniciar la firma', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        showNotification('Error de conexión', 'No se pudo conectar con el servidor. Por favor, intente nuevamente.', 'error');
    });
}

function generateToken() {
    return Array.from(crypto.getRandomValues(new Uint8Array(16)))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');
}

function showLoading() {
    document.getElementById('signatureInstructions').classList.add('hidden');
    document.getElementById('signatureLoading').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('signatureInstructions').classList.remove('hidden');
    document.getElementById('signatureLoading').classList.add('hidden');
}

function updateLoadingState(message, subMessage, type = 'loading') {
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingSubMessage').textContent = subMessage;
}

function showNotification(title, message, type = 'info') {
    const colors = {
        success: 'bg-green-100 border-green-500 text-green-900',
        error: 'bg-red-100 border-red-500 text-red-900',
        warning: 'bg-yellow-100 border-yellow-500 text-yellow-900',
        info: 'bg-blue-100 border-blue-500 text-blue-900'
    };

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 max-w-sm p-4 border-l-4 rounded shadow-lg ${colors[type]} z-50 transform transition-all duration-300`;
    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-1">
                <p class="font-bold">${title}</p>
                <p class="text-sm mt-1">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-xl">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}
</script>
@endpush
