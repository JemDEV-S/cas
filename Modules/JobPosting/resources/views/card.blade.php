@php
    // Detectar si estamos en modo edición (con datos) o modo inicialización (sin datos)
    $hasData = !is_null($schedule);
    $phaseId = $hasData ? $schedule->process_phase_id : ($defaultPhaseId ?? '');
    
    // Valores
    $startDate = $hasData ? $schedule->start_date->format('Y-m-d') : '';
    $endDate = $hasData && $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '';
    $location = $hasData ? $schedule->location : ''; // Puedes poner un default aquí si quieres
    $respId = $hasData ? $schedule->responsible_unit_id : '';
@endphp

<div class="schedule-card relative bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow border-l-4 border-l-blue-500">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
        
        {{-- Número --}}
        <div class="col-span-1 flex md:justify-center">
            <span class="row-index bg-blue-100 text-blue-700 font-bold w-8 h-8 flex items-center justify-center rounded-full text-sm">
                {{ $index + 1 }}
            </span>
        </div>

        {{-- Fase --}}
        <div class="col-span-11 md:col-span-3">
            <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Fase</label>
            <select name="schedules[{{ $index }}][process_phase_id]" class="block w-full rounded-lg border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" required>
                @foreach($phases as $phase)
                    <option value="{{ $phase->id }}" {{ $phaseId == $phase->id ? 'selected' : '' }}>
                        {{ $phase->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Inicio --}}
        <div class="col-span-6 md:col-span-2">
            <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Inicio</label>
            <input type="date" 
                   name="schedules[{{ $index }}][start_date]" 
                   value="{{ $startDate }}"
                   class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
        </div>

        {{-- Fin --}}
        <div class="col-span-6 md:col-span-2">
            <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Fin</label>
            <input type="date" 
                   name="schedules[{{ $index }}][end_date]" 
                   value="{{ $endDate }}"
                   class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        </div>

        {{-- Lugar --}}
        <div class="col-span-12 md:col-span-2">
            <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Lugar</label>
            <input type="text" 
                   name="schedules[{{ $index }}][location]" 
                   value="{{ $location }}"
                   placeholder="Ej: Portal Web" 
                   class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
        </div>

        {{-- Responsable y Eliminar --}}
        <div class="col-span-12 md:col-span-2 flex gap-2">
            <div class="flex-grow">
                <label class="block md:hidden text-xs font-bold text-gray-500 uppercase mb-1">Responsable</label>
                <select name="schedules[{{ $index }}][responsible_unit_id]" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <option value="">-- Seleccionar --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $respId == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
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