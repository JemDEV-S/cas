<tr class="schedule-row hover:bg-gray-50">
    <td class="px-4 py-2 text-center text-gray-500 font-bold row-index">
        {{ $index + 1 }}
    </td>
    
    <td class="px-4 py-2">
        <select name="schedules[{{ $index }}][process_phase_id]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @foreach($phases as $phase)
                <option value="{{ $phase->id }}" {{ $schedule->process_phase_id == $phase->id ? 'selected' : '' }}>
                    {{ $phase->name }}
                </option>
            @endforeach
        </select>
    </td>

    <td class="px-4 py-2">
        <input type="date" 
               name="schedules[{{ $index }}][start_date]" 
               value="{{ $schedule->start_date->format('Y-m-d') }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </td>

    <td class="px-4 py-2">
        <input type="date" 
               name="schedules[{{ $index }}][end_date]" 
               value="{{ $schedule->end_date ? $schedule->end_date->format('Y-m-d') : '' }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </td>

    <td class="px-4 py-2">
        <input type="text" 
               name="schedules[{{ $index }}][location]" 
               value="{{ $schedule->location }}"
               placeholder="Lugar..."
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </td>

    <td class="px-4 py-2">
        <select name="schedules[{{ $index }}][responsible_unit_id]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">-- Seleccionar --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ $schedule->responsible_unit_id == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>
    </td>

    <td class="px-4 py-2 text-center">
        <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700" title="Eliminar fila">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </button>
    </td>
</tr>