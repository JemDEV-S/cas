@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('jobposting.show', $jobPosting) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Volver a la convocatoria
                </a>
                <h1 class="text-3xl font-extrabold text-gray-900">Gestión de Cronograma</h1>
                <p class="text-gray-500 mt-1">{{ $jobPosting->code }} - {{ $jobPosting->title }}</p>
            </div>
            
            <span class="px-4 py-2 rounded-full text-sm font-bold shadow-sm {{ $jobPosting->status->badgeClass() }}">
                {{ $jobPosting->status->label() }}
            </span>
        </div>

        {{-- FORMULARIO --}}
        <form action="{{ route('jobposting.schedule.update', $jobPosting) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Contenedor de Fases (Lista de Tarjetas) --}}
            <div id="schedule-list" class="space-y-4">
                
                {{-- Encabezados visuales --}}
                <div class="hidden md:grid grid-cols-12 gap-4 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <div class="col-span-1 text-center">#</div>
                    <div class="col-span-3">Fase del Proceso</div>
                    <div class="col-span-2">Inicio</div>
                    <div class="col-span-2">Fin</div>
                    <div class="col-span-2">Lugar</div>
                    <div class="col-span-2">Responsable</div>
                </div>

                {{-- Lógica de renderizado --}}
                @if($schedules->isNotEmpty())
                    {{-- CASO A: Hay datos guardados (Automático o guardado previo) --}}
                    @foreach($schedules as $index => $schedule)
                        @include('jobposting::card', [
                            'index' => $index, 
                            'schedule' => $schedule, 
                            'phases' => $phases, 
                            'units' => $units
                        ])
                    @endforeach
                @else
                    {{-- CASO B: Está vacío (Modo Manual) --}}
                    {{-- CAMBIO: Solo mostramos 1 tarjeta vacía para empezar, no las 12 --}}
                    @include('jobposting::card', [
                        'index' => 0, 
                        'schedule' => null, 
                        'defaultPhaseId' => null, // No pre-seleccionamos nada
                        'phases' => $phases, 
                        'units' => $units
                    ])
                @endif

            </div>

            {{-- Botón Grande para Agregar Fase --}}
            <div class="mt-6">
                <button type="button" onclick="addCard()" 
                    class="group w-full border-2 border-dashed border-blue-300 rounded-xl p-6 flex flex-col items-center justify-center text-blue-500 hover:bg-blue-50 hover:border-blue-500 hover:text-blue-700 transition-all duration-200 cursor-pointer">
                    <div class="bg-blue-100 rounded-full p-3 mb-2 group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <span class="font-bold text-lg">Agregar Nueva Fase Manualmente</span>
                    <span class="text-sm text-blue-400 group-hover:text-blue-600">Click para insertar una fila al final</span>
                </button>
            </div>

            {{-- Barra de Acción Flotante (Sticky Bottom) --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg z-50">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <span id="total-phases-count" class="font-bold text-gray-900">0</span> fases configuradas
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
                <select name="schedules[INDEX][process_phase_id]" class="block w-full rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" required>
                    <option value="">Seleccione fase...</option>
                    @foreach($phases as $phase)
                        <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Inicio --}}
            <div class="col-span-6 md:col-span-2">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Inicio</label>
                <input type="date" name="schedules[INDEX][start_date]" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
            </div>

            {{-- Fin --}}
            <div class="col-span-6 md:col-span-2">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Fin</label>
                <input type="date" name="schedules[INDEX][end_date]" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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
                    <select name="schedules[INDEX][responsible_unit_id]" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        updateIndexes();
    });

    function addCard() {
        const template = document.getElementById('card-template').innerHTML;
        // Usamos un timestamp para asegurar índices únicos en el array de HTML
        const uniqueIndex = Date.now() + Math.floor(Math.random() * 1000);
        
        // Reemplazamos el placeholder INDEX por el índice único
        const newCard = template.replace(/INDEX/g, uniqueIndex);
        
        document.getElementById('schedule-list').insertAdjacentHTML('beforeend', newCard);
        
        // Scroll suave
        const newList = document.getElementById('schedule-list');
        newList.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        updateIndexes();
    }

    function removeCard(btn) {
        // Animación de salida antes de eliminar
        const card = btn.closest('.schedule-card');
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            card.remove();
            
            // Si borra todas, agregamos una vacía automáticamente
            if (document.querySelectorAll('.schedule-card').length === 0) {
                addCard();
            }
            updateIndexes();
        }, 200);
    }

    function updateIndexes() {
        const cards = document.querySelectorAll('.schedule-card');
        document.getElementById('total-phases-count').textContent = cards.length;
        
        cards.forEach((card, index) => {
            // Actualizar número visual
            card.querySelector('.row-index').textContent = index + 1;
            
            // Alternar colores de borde para mejor distinción visual (opcional)
            // card.classList.remove('border-l-blue-500', 'border-l-indigo-500');
            // if(index % 2 === 0) card.classList.add('border-l-blue-500');
            // else card.classList.add('border-l-indigo-500');
        });
    }
</script>

<style>
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection