@extends('layouts.app')

@section('title', 'Edición Masiva de Evaluaciones')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edición Masiva de Evaluaciones</h1>
        <p class="mt-2 text-gray-600">Seleccione una convocatoria y una fase para editar las evaluaciones de forma masiva</p>
    </div>

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6" x-data="bulkEditSelector()">
        <form method="GET" action="{{ route('evaluation.bulk-edit.edit') }}" @submit="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Selector de Convocatoria -->
                <div>
                    <label for="job_posting_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Convocatoria <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="job_posting_id"
                        name="job_posting_id"
                        x-model="selectedJobPosting"
                        @change="console.log('=== CHANGE EVENT ==='); console.log('Target value:', $event.target.value); console.log('Selected index:', $event.target.selectedIndex); console.log('Selected option text:', $event.target.options[$event.target.selectedIndex].text); console.log('x-model value:', selectedJobPosting); console.log('==================='); loadPhases()"
                        autocomplete="off"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        required
                    >
                        <option value="">-- Seleccione una convocatoria --</option>
                        @foreach($jobPostings as $posting)
                            <option value="{{ $posting->id }}">
                                {{ $posting->title }} ({{ $posting->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Seleccione la convocatoria que desea gestionar</p>
                </div>

                <!-- Selector de Fase -->
                <div>
                    <label for="phase_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Fase del Proceso <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="phase_id"
                        name="phase_id"
                        x-model="selectedPhase"
                        autocomplete="off"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        :disabled="!selectedJobPosting || loadingPhases"
                        required
                    >
                        <option value="">-- Seleccione una fase --</option>
                        <template x-for="phase in phases" :key="phase.id">
                            <option :value="phase.id" x-text="phase.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-sm text-gray-500" x-show="loadingPhases">Cargando fases...</p>
                    <p class="mt-1 text-sm text-gray-500" x-show="!selectedJobPosting && !loadingPhases">Primero seleccione una convocatoria</p>
                    <!-- Debug info -->
                    <div class="mt-1 text-xs text-gray-400">
                        <p>Debug:</p>
                        <ul class="list-disc pl-5">
                            <li>Convocatoria: <span x-text="selectedJobPosting || 'ninguna'"></span></li>
                            <li>Loading: <span x-text="loadingPhases ? 'SÍ' : 'NO'"></span></li>
                            <li>Fases: <span x-text="phases.length"></span></li>
                            <li>Disabled: <span x-text="(!selectedJobPosting || loadingPhases) ? 'SÍ' : 'NO'"></span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a
                    href="{{ route('evaluation.index') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!selectedJobPosting || !selectedPhase || loadingPhases"
                    x-text="loadingPhases ? 'Cargando...' : 'Cargar Evaluaciones'"
                >
                    Cargar Evaluaciones
                </button>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Solo se mostrarán evaluaciones en estado <strong>ENVIADO</strong> o <strong>MODIFICADO</strong></li>
                        <li>Los cambios se guardarán automáticamente al salir de cada campo</li>
                        <li>Cada modificación quedará registrada en el historial de la evaluación</li>
                        <li>Se requiere el permiso de <strong>administrador de evaluaciones</strong> para acceder</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    console.log('Alpine está inicializándose...');
});

function bulkEditSelector() {
    return {
        selectedJobPosting: '',
        selectedPhase: '',
        phases: [],
        loadingPhases: false,

        init() {
            console.log('Alpine.js inicializado en bulkEditSelector');
            console.log('Estado inicial:', {
                selectedJobPosting: this.selectedJobPosting,
                selectedPhase: this.selectedPhase,
                phases: this.phases,
                loadingPhases: this.loadingPhases
            });

            // Watcher para selectedJobPosting
            this.$watch('selectedJobPosting', (value, oldValue) => {
                console.log('Watcher: selectedJobPosting cambió de', oldValue, 'a', value);
            });

            // Sincronizar con el valor actual del select (por si el navegador autocompletó)
            this.$nextTick(() => {
                const jobPostingSelect = document.getElementById('job_posting_id');
                const phaseSelect = document.getElementById('phase_id');

                console.log('Verificando autocompletado...');
                console.log('Select encontrado:', !!jobPostingSelect);
                console.log('Valor del select de convocatoria:', jobPostingSelect ? jobPostingSelect.value : 'select no encontrado');
                console.log('Opciones disponibles:', jobPostingSelect ? jobPostingSelect.options.length : 0);

                if (jobPostingSelect) {
                    for (let i = 0; i < jobPostingSelect.options.length; i++) {
                        console.log(`Opción ${i}: value="${jobPostingSelect.options[i].value}", text="${jobPostingSelect.options[i].text}"`);
                    }
                }

                if (jobPostingSelect && jobPostingSelect.value) {
                    console.log('Detectado valor autocompletado en convocatoria:', jobPostingSelect.value);
                    this.selectedJobPosting = jobPostingSelect.value;
                    this.loadPhases();
                }

                if (phaseSelect && phaseSelect.value) {
                    console.log('Detectado valor autocompletado en fase:', phaseSelect.value);
                    this.selectedPhase = phaseSelect.value;
                }
            });
        },

        async loadPhases() {
            console.log('loadPhases llamado, selectedJobPosting:', this.selectedJobPosting);
            if (!this.selectedJobPosting) {
                this.phases = [];
                this.selectedPhase = '';
                this.loadingPhases = false;
                return;
            }

            this.loadingPhases = true;
            this.selectedPhase = '';
            this.phases = [];

            try {
                const url = `{{ route('evaluation.bulk-edit.phases') }}?job_posting_id=${this.selectedJobPosting}`;
                console.log('Fetching URL:', url);

                const response = await fetch(url);
                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                console.log('Fases recibidas:', result);

                if (result.success) {
                    this.phases = result.data || [];
                    console.log('Fases asignadas:', this.phases);
                    if (this.phases.length === 0) {
                        alert('No hay fases programadas para esta convocatoria');
                    }
                } else {
                    alert('Error al cargar las fases: ' + (result.message || 'Error desconocido'));
                    this.phases = [];
                }
            } catch (error) {
                console.error('Error completo:', error);
                alert('Error al cargar las fases. Revise la consola para más detalles.');
                this.phases = [];
            } finally {
                console.log('Finalizando carga. loadingPhases será false');
                this.loadingPhases = false;
                console.log('loadingPhases ahora es:', this.loadingPhases);
            }
        },

        handleSubmit(event) {
            if (!this.selectedJobPosting || !this.selectedPhase) {
                event.preventDefault();
                alert('Por favor seleccione una convocatoria y una fase');
            }
        }
    };
}
</script>
@endsection
