@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('jobposting.show', $jobPosting) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver a la convocatoria
                </a>
                <h1 class="text-3xl font-extrabold text-gray-900">Gestión de Cronograma</h1>
                <p class="text-gray-500 mt-1">{{ $jobPosting->code }} - {{ $jobPosting->title }}</p>
            </div>

            <span class="px-4 py-2 rounded-full text-sm font-bold shadow-sm {{ $jobPosting->status->badgeClass() }}">
                {{ $jobPosting->status->label() }}
            </span>
        </div>

        {{-- Alertas y Mensajes --}}
        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-xl mb-6 shadow-lg animate-fade-in-up">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-lg animate-fade-in-up">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-lg animate-fade-in-up">
            <div class="flex items-start">
                <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-bold mb-2">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Información de ayuda --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>💡 Consejos:</strong> Puedes agregar, editar o eliminar fases. El sistema <strong>previene automáticamente los duplicados</strong>. Las fechas son completamente flexibles.
                    </p>
                </div>
            </div>
        </div>

        {{-- FORMULARIO --}}
        <form id="scheduleForm" action="{{ route('jobposting.schedule.update', $jobPosting) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Botones de acción rápida --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <div class="flex flex-wrap gap-3">
                        <button type="button" id="add-phase-btn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Agregar Fase</span>
                        </button>

                        @if($schedules->isEmpty())
                        <button type="button" id="generate-all-btn" class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-semibold hover:from-green-600 hover:to-emerald-700 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Generar 12 Fases Automáticamente</span>
                        </button>
                        @else
                        <button type="button" id="regenerate-all-btn" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-lg font-semibold hover:from-amber-600 hover:to-orange-700 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Regenerar Todo</span>
                        </button>
                        @endif

                        <button type="button" id="clear-all-btn" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Limpiar Todo</span>
                        </button>
                    </div>

                    <div class="flex items-center space-x-2 text-sm">
                        <span class="font-medium text-gray-700">Total de fases:</span>
                        <span id="total-phases-badge" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold">
                            {{ $schedules->count() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Contenedor de Fases --}}
            <div id="schedule-list" class="space-y-4">

                {{-- Encabezados visuales (desktop) --}}
                <div class="hidden md:grid grid-cols-12 gap-4 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-3">Fase del Proceso</div>
                    <div class="col-span-2">Inicio</div>
                    <div class="col-span-2">Fin</div>
                    <div class="col-span-2">Lugar</div>
                    <div class="col-span-2">Responsable</div>
                </div>

                {{-- Renderizado de tarjetas existentes --}}
                @if($schedules->isNotEmpty())
                    @foreach($schedules as $index => $schedule)
                        @include('jobposting::card', [
                            'index' => $index,
                            'schedule' => $schedule,
                            'phases' => $phases,
                            'units' => $units
                        ])
                    @endforeach
                @else
                    {{-- Mensaje de lista vacía --}}
                    <div id="empty-message" class="bg-white rounded-xl shadow-lg p-12 text-center">
                        <div class="flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No hay fases en el cronograma</h3>
                        <p class="text-gray-600 mb-6">Comienza agregando fases manualmente o genera las 12 fases automáticamente</p>
                        <div class="flex justify-center space-x-3">
                            <button type="button" onclick="document.getElementById('add-phase-btn').click()" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                                Agregar Fase
                            </button>
                            <button type="button" onclick="document.getElementById('generate-all-btn').click()" class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                Generar Automáticamente
                            </button>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Botón grande para agregar fase --}}
            <div class="mt-6">
                <button type="button" id="add-phase-btn-bottom" class="group w-full border-2 border-dashed border-blue-300 rounded-xl p-6 flex flex-col items-center justify-center text-blue-500 hover:bg-blue-50 hover:border-blue-500 hover:text-blue-700 transition-all duration-200 cursor-pointer">
                    <div class="bg-blue-100 rounded-full p-3 mb-2 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="font-bold text-lg">Agregar Nueva Fase</span>
                    <span class="text-sm text-blue-400 group-hover:text-blue-600">Click para insertar una fase al final</span>
                </button>
            </div>

            {{-- Barra de Acción Flotante (Sticky Bottom) --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg z-50">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <span id="total-phases-count" class="font-bold text-gray-900">{{ $schedules->count() }}</span> fases configuradas
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('jobposting.show', $jobPosting) }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors focus:ring-4 focus:ring-gray-100">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 focus:ring-4 focus:ring-blue-300">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>

            {{-- Espaciador para que el footer no tape contenido --}}
            <div class="h-24"></div>

        </form>
    </div>
</div>

{{-- TEMPLATE JAVASCRIPT (Tarjeta) --}}
<template id="card-template">
    <div class="schedule-card relative bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow border-l-4 border-l-blue-500 animate-fade-in-up">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">

            {{-- Número --}}
            <div class="col-span-1 flex md:justify-center">
                <span class="row-index bg-blue-100 text-blue-700 font-bold w-8 h-8 flex items-center justify-center rounded-full text-sm"></span>
            </div>

            {{-- Fase --}}
            <div class="col-span-11 md:col-span-3">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Fase</label>
                <select name="schedules[INDEX][process_phase_id]" class="phase-select block w-full rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    <option value="">Seleccione fase...</option>
                    @foreach($phases as $phase)
                        <option value="{{ $phase->id }}" data-phase-number="{{ $phase->phase_number }}">{{ $phase->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Inicio --}}
            <div class="col-span-6 md:col-span-2">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Inicio</label>
                <input type="date" name="schedules[INDEX][start_date]" class="start-date block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
            </div>

            {{-- Fin --}}
            <div class="col-span-6 md:col-span-2">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Fin</label>
                <input type="date" name="schedules[INDEX][end_date]" class="end-date block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>

            {{-- Lugar --}}
            <div class="col-span-12 md:col-span-2">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Lugar</label>
                <input type="text" name="schedules[INDEX][location]" placeholder="Ej: Portal Web" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>

            {{-- Responsable y Eliminar --}}
            <div class="col-span-12 md:col-span-2 flex gap-2">
                <div class="flex-grow">
                    <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Responsable</label>
                    <select name="schedules[INDEX][responsible_unit_id]" class="unit-select block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">-- Seleccionar --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <button type="button" onclick="removeCard(this)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar fase">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Select2 personalizado */
    .select2-container--default .select2-selection--single {
        height: 42px !important;
        padding: 6px 12px !important;
        border-radius: 0.5rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {

    const jobPostingId = "{{ $jobPosting->id }}";
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ========================================================================
    // INICIALIZACIÓN
    // ========================================================================

    updateIndexes();
    initializeSelect2();
    checkEmptyState();

    // ========================================================================
    // SELECT2 CON BÚSQUEDA DINÁMICA
    // ========================================================================

    function initializeSelect2() {
        // Select de fases con búsqueda
        $('.phase-select').select2({
            placeholder: 'Buscar fase...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Select de unidades con búsqueda
        $('.unit-select').select2({
            placeholder: 'Buscar unidad...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '/api/search/organizational-units',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        limit: 20
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            language: {
                inputTooShort: function() {
                    return "Escribe al menos 2 caracteres";
                },
                noResults: function() {
                    return "No se encontraron unidades";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    }

    // ========================================================================
    // AGREGAR FASE
    // ========================================================================

    $('#add-phase-btn, #add-phase-btn-bottom').on('click', function() {
        addCard();
    });

    function addCard() {
        const template = document.getElementById('card-template').innerHTML;
        const uniqueIndex = Date.now() + Math.floor(Math.random() * 1000);
        const newCard = template.replace(/INDEX/g, uniqueIndex);

        $('#schedule-list').append(newCard);

        // Inicializar Select2 en los nuevos selects
        const $newCard = $('#schedule-list').children().last();
        $newCard.find('.phase-select').select2({
            placeholder: 'Buscar fase...',
            allowClear: true,
            width: '100%'
        });

        $newCard.find('.unit-select').select2({
            placeholder: 'Buscar unidad...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '/api/search/organizational-units',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, limit: 20 };
                },
                processResults: function (data) {
                    return {
                        results: data.data.map(function(item) {
                            return { id: item.id, text: item.text };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Scroll suave al nuevo elemento
        $newCard[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

        updateIndexes();
        checkEmptyState();

        // Animación de entrada
        $newCard.addClass('animate-fade-in-up');
    }

    // ========================================================================
    // ELIMINAR FASE
    // ========================================================================

    window.removeCard = function(btn) {
        const card = $(btn).closest('.schedule-card');

        // Animación de salida
        card.css({
            'opacity': '0',
            'transform': 'scale(0.95)',
            'transition': 'all 0.2s ease-out'
        });

        setTimeout(() => {
            card.remove();
            updateIndexes();
            checkEmptyState();
        }, 200);
    };

    // ========================================================================
    // LIMPIAR TODO
    // ========================================================================

    $('#clear-all-btn').on('click', function() {
        if (!confirm('⚠️ ¿Estás seguro de eliminar TODAS las fases? Esta acción no se puede deshacer.')) {
            return;
        }

        $('.schedule-card').fadeOut(300, function() {
            $(this).remove();
            updateIndexes();
            checkEmptyState();
        });
    });

    // ========================================================================
    // GENERAR TODAS LAS FASES AUTOMÁTICAMENTE
    // ========================================================================

    $('#generate-all-btn').on('click', function() {
        const startDate = prompt('📅 Ingresa la fecha de inicio del cronograma (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);

        if (!startDate) return;

        // Validar formato de fecha
        if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate)) {
            alert('❌ Formato de fecha inválido. Usa: YYYY-MM-DD');
            return;
        }

        generateAllPhases(startDate);
    });

    function generateAllPhases(startDate) {
        const btn = $('#generate-all-btn');
        const originalHtml = btn.html();

        btn.prop('disabled', true).html(`
            <svg class="animate-spin h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generando...
        `);

        $.post('/api/preview/schedule', {
            start_date: startDate,
            _token: csrfToken
        })
        .done(function(response) {
            if (response.success) {
                // Limpiar tarjetas existentes
                $('.schedule-card').remove();

                // Agregar las 12 fases
                response.preview.forEach(function(phase, index) {
                    const template = document.getElementById('card-template').innerHTML;
                    const uniqueIndex = Date.now() + index;
                    let newCard = template.replace(/INDEX/g, uniqueIndex);

                    $('#schedule-list').append(newCard);

                    const $card = $('#schedule-list').children().last();

                    // Buscar la fase por número y seleccionarla
                    const phaseOption = $card.find('.phase-select option').filter(function() {
                        return $(this).data('phase-number') == phase.phase_number;
                    });

                    if (phaseOption.length) {
                        $card.find('.phase-select').val(phaseOption.val());
                    }

                    // Establecer fechas
                    $card.find('.start-date').val(phase.start_date);
                    $card.find('.end-date').val(phase.end_date);
                    $card.find('input[name*="[location]"]').val('Portal Institucional');

                    // Inicializar Select2
                    $card.find('.phase-select').select2({
                        placeholder: 'Buscar fase...',
                        allowClear: true,
                        width: '100%'
                    });

                    $card.find('.unit-select').select2({
                        placeholder: 'Buscar unidad...',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: '/api/search/organizational-units',
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return { q: params.term, limit: 20 };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.data.map(function(item) {
                                        return { id: item.id, text: item.text };
                                    })
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 2
                    });
                });

                updateIndexes();
                checkEmptyState();

                showNotification('✅ Se generaron ' + response.total_phases + ' fases automáticamente', 'success');
            }
        })
        .fail(function(xhr) {
            alert('❌ Error al generar fases: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        })
        .always(function() {
            btn.prop('disabled', false).html(originalHtml);
        });
    }

    // ========================================================================
    // REGENERAR TODO
    // ========================================================================

    $('#regenerate-all-btn').on('click', function() {
        if (!confirm('⚠️ ¿Estás seguro de ELIMINAR el cronograma actual y regenerar las 12 fases? Esta acción no se puede deshacer.')) {
            return;
        }

        const startDate = prompt('📅 Ingresa la nueva fecha de inicio (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);

        if (!startDate) return;

        if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate)) {
            alert('❌ Formato de fecha inválido. Usa: YYYY-MM-DD');
            return;
        }

        regenerateSchedule(startDate);
    });

    function regenerateSchedule(startDate) {
        const btn = $('#regenerate-all-btn');
        const originalHtml = btn.html();

        btn.prop('disabled', true).html(`
            <svg class="animate-spin h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Regenerando...
        `);

        $.post('/api/regenerate/schedule/' + jobPostingId, {
            start_date: startDate,
            _token: csrfToken
        })
        .done(function(response) {
            if (response.success) {
                showNotification('✅ ' + response.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        })
        .fail(function(xhr) {
            alert('❌ Error: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        })
        .always(function() {
            btn.prop('disabled', false).html(originalHtml);
        });
    }

    // ========================================================================
    // UTILIDADES
    // ========================================================================

    function updateIndexes() {
        const cards = $('.schedule-card');
        $('#total-phases-count').text(cards.length);
        $('#total-phases-badge').text(cards.length);

        cards.each(function(index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function checkEmptyState() {
        const hasCards = $('.schedule-card').length > 0;

        if (hasCards) {
            $('#empty-message').remove();
        } else {
            if ($('#empty-message').length === 0) {
                $('#schedule-list').append(`
                    <div id="empty-message" class="bg-white rounded-xl shadow-lg p-12 text-center animate-fade-in-up">
                        <div class="flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No hay fases en el cronograma</h3>
                        <p class="text-gray-600 mb-6">Comienza agregando fases manualmente o genera las 12 fases automáticamente</p>
                    </div>
                `);
            }
        }
    }

    function showNotification(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
        const borderColor = type === 'success' ? 'border-green-500' : 'border-red-500';
        const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';

        const notification = $(`
            <div class="fixed top-4 right-4 ${bgColor} border-l-4 ${borderColor} ${textColor} px-6 py-4 rounded-xl shadow-xl z-50 max-w-md animate-fade-in-up">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium">${message}</p>
                </div>
            </div>
        `);

        $('body').append(notification);

        setTimeout(() => {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // ========================================================================
    // VALIDACIÓN ANTES DE ENVIAR
    // ========================================================================

    $('#scheduleForm').on('submit', function(e) {
        const cards = $('.schedule-card');

        if (cards.length === 0) {
            e.preventDefault();
            alert('⚠️ Debes agregar al menos una fase al cronograma');
            return false;
        }

        // Validar duplicados por fase
        const phaseIds = [];
        let hasDuplicates = false;

        cards.each(function() {
            const phaseId = $(this).find('.phase-select').val();
            if (phaseId && phaseIds.includes(phaseId)) {
                hasDuplicates = true;
                $(this).addClass('border-red-500');
            } else if (phaseId) {
                phaseIds.push(phaseId);
            }
        });

        if (hasDuplicates) {
            e.preventDefault();
            alert('❌ Hay fases duplicadas. Cada fase solo puede aparecer una vez en el cronograma.');
            return false;
        }

        // Validar fechas
        let invalidDates = false;

        cards.each(function() {
            const startDate = $(this).find('.start-date').val();
            const endDate = $(this).find('.end-date').val();

            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                invalidDates = true;
                $(this).find('.end-date').addClass('border-red-500');
            }
        });

        if (invalidDates) {
            e.preventDefault();
            alert('❌ Hay fechas de fin anteriores a las fechas de inicio. Por favor, corrígelas.');
            return false;
        }

        // Todo OK, mostrar loading
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html(`
            <svg class="animate-spin h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Guardando...
        `);
    });

    console.log('✅ Cronograma inicializado correctamente');
    console.log('📋 Funcionalidades activas:');
    console.log('  - Búsqueda dinámica en selectores');
    console.log('  - Prevención de duplicados');
    console.log('  - Generación automática de 12 fases');
    console.log('  - Validación en tiempo real');
    console.log('  - Fechas sin restricciones');
});
</script>
@endpush
@endsection
