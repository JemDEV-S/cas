@extends('layouts.app')

@section('title', 'Evaluación en Progreso')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Breadcrumb --}}
    <nav class="mb-6" aria-label="breadcrumb">
        <ol class="flex space-x-2 text-sm text-gray-600">
            <li>
                <a href="{{ route('evaluation.automatic.index') }}" class="hover:text-blue-600">
                    Evaluaciones Automáticas
                </a>
            </li>
            <li class="before:content-['/'] before:mx-2">
                <a href="{{ route('evaluation.automatic.show', $jobPosting->id) }}" class="hover:text-blue-600">
                    {{ $jobPosting->code }}
                </a>
            </li>
            <li class="before:content-['/'] before:mx-2">
                <span class="text-gray-800 font-medium">Progreso</span>
            </li>
        </ol>
    </nav>

    {{-- Encabezado --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-1">
            <i class="fas fa-spinner fa-spin mr-2 text-blue-600" id="spinnerIcon"></i>
            <span id="statusTitle">Evaluación en Progreso</span>
        </h2>
        <p class="text-gray-600 text-sm">
            <code class="bg-gray-100 px-2 py-1 rounded">{{ $jobPosting->code }}</code>
            <span class="mx-2">-</span>
            {{ $jobPosting->title }}
        </p>
    </div>

    {{-- Tarjeta principal de progreso --}}
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 mb-6">
        {{-- Barra de progreso --}}
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Progreso de evaluación</span>
                <span class="text-sm font-semibold text-blue-600" id="percentageText">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-500 ease-out"
                     id="progressBar"
                     style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span id="processedCount">0 procesadas</span>
                <span id="totalCount">de {{ $stats['total'] ?? 0 }} total</span>
            </div>
        </div>

        {{-- Estadísticas en tiempo real --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                <i class="fas fa-file-alt text-2xl text-gray-500 mb-2"></i>
                <h3 class="text-2xl font-bold text-gray-900" id="statTotal">{{ $stats['total'] ?? 0 }}</h3>
                <p class="text-gray-600 text-xs">Total</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                <h3 class="text-2xl font-bold text-green-700" id="statEligible">0</h3>
                <p class="text-green-600 text-xs">APTOS</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center border border-red-100">
                <i class="fas fa-times-circle text-2xl text-red-500 mb-2"></i>
                <h3 class="text-2xl font-bold text-red-700" id="statNotEligible">0</h3>
                <p class="text-red-600 text-xs">NO APTOS</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center border border-yellow-100">
                <i class="fas fa-exclamation-triangle text-2xl text-yellow-500 mb-2"></i>
                <h3 class="text-2xl font-bold text-yellow-700" id="statErrors">0</h3>
                <p class="text-yellow-600 text-xs">Errores</p>
            </div>
        </div>

        {{-- Información adicional --}}
        <div class="border-t border-gray-200 pt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-clock mr-2"></i>
                    <span>Iniciado: <strong id="startedAt">{{ \Carbon\Carbon::parse($stats['started_at'] ?? now())->format('d/m/Y H:i:s') }}</strong></span>
                </div>
                <div class="flex items-center text-gray-600" id="finishedAtContainer" style="display: none;">
                    <i class="fas fa-flag-checkered mr-2"></i>
                    <span>Finalizado: <strong id="finishedAt">-</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mensaje de información --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6" id="infoMessage">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div>
                <p class="text-blue-800 font-medium">Procesando en segundo plano</p>
                <p class="text-blue-700 text-sm mt-1">
                    La evaluación se está procesando automáticamente. Puedes quedarte en esta página para ver el progreso
                    o continuar navegando. Los resultados estarán disponibles una vez complete el proceso.
                </p>
            </div>
        </div>
    </div>

    {{-- Mensaje de completado (oculto inicialmente) --}}
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 hidden" id="completedMessage">
        <div class="flex items-start">
            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
            <div>
                <p class="text-green-800 font-medium">Evaluación completada</p>
                <p class="text-green-700 text-sm mt-1">
                    La evaluación automática ha finalizado. Puedes ver los resultados detallados haciendo clic en el botón de abajo.
                </p>
            </div>
        </div>
    </div>

    {{-- Botones de acción --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('evaluation.automatic.show', $jobPosting->id) }}"
           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Detalles
        </a>
        <a href="{{ route('evaluation.automatic.show', $jobPosting->id) }}"
           class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors hidden"
           id="viewResultsBtn">
            <i class="fas fa-list mr-2"></i>
            Ver Resultados
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jobPostingId = '{{ $jobPosting->id }}';
    const statusUrl = '{{ route("evaluation.automatic.progress.status", $jobPosting->id) }}';
    let pollInterval = null;
    let isCompleted = false;

    // Elementos del DOM
    const progressBar = document.getElementById('progressBar');
    const percentageText = document.getElementById('percentageText');
    const processedCount = document.getElementById('processedCount');
    const statTotal = document.getElementById('statTotal');
    const statEligible = document.getElementById('statEligible');
    const statNotEligible = document.getElementById('statNotEligible');
    const statErrors = document.getElementById('statErrors');
    const spinnerIcon = document.getElementById('spinnerIcon');
    const statusTitle = document.getElementById('statusTitle');
    const infoMessage = document.getElementById('infoMessage');
    const completedMessage = document.getElementById('completedMessage');
    const viewResultsBtn = document.getElementById('viewResultsBtn');
    const finishedAtContainer = document.getElementById('finishedAtContainer');
    const finishedAt = document.getElementById('finishedAt');

    function updateProgress(data) {
        // Actualizar barra de progreso
        const percentage = data.percentage || 0;
        progressBar.style.width = percentage + '%';
        percentageText.textContent = percentage + '%';

        // Actualizar contadores
        processedCount.textContent = (data.processed || 0) + ' procesadas';
        statTotal.textContent = data.total || 0;
        statEligible.textContent = data.eligible || 0;
        statNotEligible.textContent = data.not_eligible || 0;
        statErrors.textContent = data.errors || 0;

        // Verificar si completó
        if (data.status === 'completed' && !isCompleted) {
            isCompleted = true;
            onCompleted(data);
        }
    }

    function onCompleted(data) {
        // Detener polling
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }

        // Actualizar UI
        spinnerIcon.classList.remove('fa-spinner', 'fa-spin');
        spinnerIcon.classList.add('fa-check-circle', 'text-green-600');
        statusTitle.textContent = 'Evaluación Completada';

        // Mostrar/ocultar mensajes
        infoMessage.classList.add('hidden');
        completedMessage.classList.remove('hidden');
        viewResultsBtn.classList.remove('hidden');

        // Mostrar hora de finalización
        if (data.finished_at) {
            const finishedDate = new Date(data.finished_at);
            finishedAt.textContent = finishedDate.toLocaleString('es-PE');
            finishedAtContainer.style.display = 'flex';
        }

        // Cambiar color de la barra a verde
        progressBar.classList.remove('from-blue-500', 'to-blue-600');
        progressBar.classList.add('from-green-500', 'to-green-600');
    }

    function fetchProgress() {
        fetch(statusUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error fetching progress');
            }
            return response.json();
        })
        .then(data => {
            updateProgress(data);
        })
        .catch(error => {
            console.error('Error fetching progress:', error);
        });
    }

    // Iniciar polling cada 2 segundos
    fetchProgress(); // Primera llamada inmediata
    pollInterval = setInterval(fetchProgress, 2000);

    // Limpiar interval al salir de la página
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    });
});
</script>
@endpush
@endsection
