@extends('layouts.app')

@section('title', 'Cronograma de Entrevistas')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100" x-data="interviewScheduleManager()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calendar-check text-indigo-600 mr-3"></i>
                        Cronograma de Entrevistas
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">Gestiona y programa las fechas de entrevistas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Overview -->
        @if(request()->has('job_posting_id') && request()->has('phase_id'))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full p-4">
                        <i class="fas fa-list text-indigo-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Programadas</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['scheduled'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-4">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Completadas</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-check-double text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-filter text-gray-600 mr-2"></i>
                        Filtros de Búsqueda
                    </h3>
                    <div class="flex gap-3">
                        @if(request()->has('job_posting_id') && request()->has('phase_id'))
                        <button type="button"
                                @click="openAutoScheduleModal()"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all text-sm font-medium shadow-md">
                            <i class="fas fa-magic mr-2"></i>
                            Asignación Automática
                        </button>
                        <a href="{{ route('interview-schedules.pdf', array_merge(request()->all(), ['only_scheduled' => 1])) }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Generar PDF
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('interview-schedules.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                            Convocatoria *
                        </label>
                        <select name="job_posting_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccione...</option>
                            @foreach($jobPostings ?? [] as $posting)
                                <option value="{{ $posting->id }}" {{ request('job_posting_id') == $posting->id ? 'selected' : '' }}>
                                    {{ $posting->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-layer-group text-gray-400 mr-1"></i>
                            Fase *
                        </label>
                        <select name="phase_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Seleccione...</option>
                            @foreach($phases ?? [] as $phase)
                                <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tie text-gray-400 mr-1"></i>
                            Evaluador
                        </label>
                        <select name="evaluator_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos los evaluadores</option>
                            @foreach($evaluators ?? [] as $evaluator)
                                <option value="{{ $evaluator->id }}" {{ request('evaluator_id') == $evaluator->id ? 'selected' : '' }}>
                                    {{ $evaluator->first_name }} {{ $evaluator->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg hover:from-indigo-700 hover:to-indigo-800 text-sm font-semibold">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Interviews List -->
        @if($interviews->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-list text-gray-600 mr-2"></i>
                    Entrevistas ({{ $interviews->count() }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha/Hora</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Postulante</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Evaluador</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Duración</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Ubicación</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($interviews as $interview)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($interview->interview_scheduled_at)
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($interview->interview_scheduled_at)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-sm text-indigo-600 font-semibold">
                                        {{ \Carbon\Carbon::parse($interview->interview_scheduled_at)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Sin programar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $interview->application->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $interview->application->code ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $interview->user->getFullNameAttribute() ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $interview->interview_duration_minutes ?? 30 }} min</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($interview->interview_location)
                                    <span class="text-sm text-gray-900">{{ \Str::limit($interview->interview_location, 30) }}</span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($interview->interview_scheduled_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Programada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button type="button"
                                        @click="openScheduleModal({{ json_encode($interview) }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors text-xs font-medium">
                                    <i class="fas fa-calendar-plus mr-1"></i>
                                    {{ $interview->interview_scheduled_at ? 'Editar' : 'Programar' }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif(request()->has('job_posting_id') && request()->has('phase_id'))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="bg-gray-100 rounded-full p-6 inline-block mb-4">
                <i class="fas fa-calendar-times text-gray-400 text-5xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay entrevistas</h3>
            <p class="text-gray-500">No se encontraron entrevistas con los filtros seleccionados</p>
        </div>
        @endif
    </div>

    <!-- Modal para programar/editar entrevista -->
    <div x-show="showScheduleModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showScheduleModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 bg-gray-500 bg-opacity-75"
                @click="showScheduleModal = false"></div>

            <div x-show="showScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">

                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-calendar-check text-indigo-600 mr-2"></i>
                    Programar Entrevista
                </h3>

                <form @submit.prevent="scheduleInterview()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora *</label>
                            <input type="datetime-local"
                                   x-model="scheduleForm.interview_scheduled_at"
                                   required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duración (minutos)</label>
                            <input type="number"
                                   x-model="scheduleForm.interview_duration_minutes"
                                   min="15"
                                   max="240"
                                   placeholder="30"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                            <input type="text"
                                   x-model="scheduleForm.interview_location"
                                   placeholder="Ej: Sala de reuniones 3, Videoconferencia"
                                   maxlength="255"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                            <textarea x-model="scheduleForm.interview_notes"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Notas adicionales..."
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="button"
                                @click="showScheduleModal = false"
                                class="flex-1 px-4 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="isScheduling"
                                class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50">
                            <span x-show="!isScheduling">Guardar</span>
                            <span x-show="isScheduling">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Asignación Automática -->
    <div x-show="showAutoScheduleModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showAutoScheduleModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showAutoScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 bg-gray-500 bg-opacity-75"
                @click="showAutoScheduleModal = false"></div>

            <div x-show="showAutoScheduleModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full z-10 max-h-[90vh] overflow-y-auto">

                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-5 rounded-t-2xl border-b border-purple-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                                <i class="fas fa-magic text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">
                                    Asignación Automática de Entrevistas
                                </h3>
                                <p class="text-purple-100 text-sm mt-1">Programa todas las entrevistas agrupadas por unidad orgánica y perfil</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="showAutoScheduleModal = false"
                                class="text-white hover:text-purple-100 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="autoScheduleInterviews()">
                    <!-- Body -->
                    <div class="px-6 py-6 space-y-5">
                        <!-- Info Box -->
                        <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-purple-600 text-xl mr-3 mt-1"></i>
                                <div class="flex-1">
                                    <h4 class="text-purple-900 font-semibold text-sm mb-2">¿Cómo funciona?</h4>
                                    <ul class="text-sm text-purple-800 space-y-1">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-purple-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                            <span>Las entrevistas se <strong>agrupan por unidad orgánica</strong> y luego por <strong>perfil de puesto</strong> para organizarlas mejor</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-purple-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                            <span>Se programan <strong>secuencialmente</strong> en los slots de tiempo especificados</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-purple-600 mr-2 mt-0.5 flex-shrink-0"></i>
                                            <span>Puedes elegir <strong>múltiples fechas</strong> para distribuir la carga</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas de Evaluación -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                Fechas de Evaluación *
                            </label>
                            <div class="space-y-2">
                                <template x-for="(date, index) in autoScheduleForm.dates" :key="index">
                                    <div class="flex gap-2">
                                        <input type="date"
                                               x-model="autoScheduleForm.dates[index]"
                                               :min="new Date().toISOString().split('T')[0]"
                                               required
                                               class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                        <button type="button"
                                                @click="removeDateField(index)"
                                                x-show="autoScheduleForm.dates.length > 1"
                                                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </template>
                                <button type="button"
                                        @click="addDateField()"
                                        class="w-full px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 text-sm font-medium">
                                    <i class="fas fa-plus mr-2"></i>
                                    Agregar Fecha
                                </button>
                            </div>
                        </div>

                        <!-- Rango de Horas -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock text-gray-400 mr-2"></i>
                                    Hora de Inicio *
                                </label>
                                <input type="time"
                                       x-model="autoScheduleForm.start_time"
                                       required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock text-gray-400 mr-2"></i>
                                    Hora de Fin *
                                </label>
                                <input type="time"
                                       x-model="autoScheduleForm.end_time"
                                       required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- Evaluaciones por Hora -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-users text-gray-400 mr-2"></i>
                                Evaluaciones por Hora *
                            </label>
                            <input type="number"
                                   x-model="autoScheduleForm.evaluations_per_hour"
                                   min="1"
                                   max="200"
                                   required
                                   placeholder="Ej: 30"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Número de postulantes que se citarán a cada hora
                            </p>
                        </div>

                        <!-- Ubicación -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                Ubicación
                            </label>
                            <input type="text"
                                   x-model="autoScheduleForm.interview_location"
                                   placeholder="Ej: Sala de reuniones, Videoconferencia, etc."
                                   maxlength="255"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>

                        <!-- Exclusiones por Unidad y Fecha -->
                        <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4">
                            <h4 class="text-amber-900 font-semibold text-sm mb-3 flex items-center">
                                <i class="fas fa-ban text-amber-600 mr-2"></i>
                                Exclusiones (Opcional)
                            </h4>
                            <p class="text-xs text-amber-800 mb-3">
                                Excluye postulaciones de una unidad orgánica específica en una fecha determinada
                            </p>

                            <!-- Lista de exclusiones -->
                            <div x-show="autoScheduleForm.exclusions.length > 0" class="mb-3 space-y-2">
                                <template x-for="(exclusion, index) in autoScheduleForm.exclusions" :key="index">
                                    <div class="flex items-center gap-2 bg-white border border-amber-300 rounded-lg p-2">
                                        <span class="flex-1 text-sm" x-text="`${exclusion.unit_name} - ${exclusion.date}`"></span>
                                        <button type="button"
                                                @click="removeExclusion(index)"
                                                class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Formulario para agregar exclusión -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <select x-model="newExclusion.unit_id"
                                            class="w-full px-3 py-2 text-sm border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                        <option value="">Seleccionar unidad...</option>
                                        <template x-for="unit in availableUnits" :key="unit.id">
                                            <option :value="unit.id" x-text="unit.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <input type="date"
                                           x-model="newExclusion.date"
                                           :min="new Date().toISOString().split('T')[0]"
                                           class="w-full px-3 py-2 text-sm border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>
                            <button type="button"
                                    @click="addExclusion()"
                                    :disabled="!newExclusion.unit_id || !newExclusion.date"
                                    class="w-full mt-2 px-3 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-plus mr-2"></i>
                                Agregar Exclusión
                            </button>
                        </div>

                        <!-- Opciones -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox"
                                       x-model="autoScheduleForm.overwrite_existing"
                                       class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200">
                                <span class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Sobrescribir entrevistas ya programadas</span>
                                    <span class="block text-xs text-gray-600 mt-1">Si está marcado, reprogramará todas las entrevistas. Si no, solo programará las que no tienen fecha asignada.</span>
                                </span>
                            </label>
                        </div>

                        <!-- Preview de Slots -->
                        <div x-show="calculatedSlots > 0" class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
                            <h4 class="text-green-900 font-semibold mb-2 flex items-center">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Slots Disponibles
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-green-700 mb-1">Total de Slots</p>
                                    <p class="text-2xl font-bold text-green-900" x-text="calculatedSlots"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-green-700 mb-1">Entrevistas por Día</p>
                                    <p class="text-2xl font-bold text-green-900" x-text="slotsPerDay"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="sticky bottom-0 bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200 rounded-b-2xl">
                        <button type="button"
                                @click="showAutoScheduleModal = false"
                                :disabled="isAutoScheduling"
                                class="px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium disabled:opacity-50">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="isAutoScheduling || autoScheduleForm.dates.length === 0"
                                class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 font-semibold disabled:opacity-50 shadow-md">
                            <i :class="isAutoScheduling ? 'fas fa-spinner fa-spin' : 'fas fa-magic'" class="mr-2"></i>
                            <span x-text="isAutoScheduling ? 'Programando...' : 'Programar Automáticamente'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function interviewScheduleManager() {
    return {
        showScheduleModal: false,
        showAutoScheduleModal: false,
        isScheduling: false,
        isAutoScheduling: false,
        scheduleForm: {
            assignment_id: null,
            interview_scheduled_at: '',
            interview_duration_minutes: 30,
            interview_location: '',
            interview_notes: '',
        },
        autoScheduleForm: {
            job_posting_id: '{{ request('job_posting_id') }}',
            phase_id: '{{ request('phase_id') }}',
            dates: [''],
            start_time: '09:00',
            end_time: '17:00',
            evaluations_per_hour: 30,
            interview_location: '',
            overwrite_existing: false,
            exclusions: [],
        },
        availableUnits: [],
        newExclusion: {
            unit_id: '',
            date: '',
        },

        get calculatedSlots() {
            const validDates = this.autoScheduleForm.dates.filter(d => d !== '');
            if (validDates.length === 0 || !this.autoScheduleForm.start_time || !this.autoScheduleForm.end_time) {
                return 0;
            }

            const evaluationsPerHour = parseInt(this.autoScheduleForm.evaluations_per_hour) || 30;
            const hoursPerDay = this.hoursPerDay;

            // Total de slots = días * horas por día * evaluaciones por hora
            return validDates.length * hoursPerDay * evaluationsPerHour;
        },

        get hoursPerDay() {
            if (!this.autoScheduleForm.start_time || !this.autoScheduleForm.end_time) {
                return 0;
            }

            const [startHour, startMin] = this.autoScheduleForm.start_time.split(':').map(Number);
            const [endHour, endMin] = this.autoScheduleForm.end_time.split(':').map(Number);

            const startMinutes = startHour * 60 + startMin;
            const endMinutes = endHour * 60 + endMin;
            const availableMinutes = endMinutes - startMinutes;

            // Número de franjas horarias completas
            return Math.floor(availableMinutes / 60);
        },

        get slotsPerDay() {
            const evaluationsPerHour = parseInt(this.autoScheduleForm.evaluations_per_hour) || 30;
            return this.hoursPerDay * evaluationsPerHour;
        },

        openScheduleModal(interview) {
            this.scheduleForm.assignment_id = interview.id;
            this.scheduleForm.interview_scheduled_at = interview.interview_scheduled_at || '';
            this.scheduleForm.interview_duration_minutes = interview.interview_duration_minutes || 30;
            this.scheduleForm.interview_location = interview.interview_location || '';
            this.scheduleForm.interview_notes = interview.interview_notes || '';
            this.showScheduleModal = true;
        },

        async openAutoScheduleModal() {
            // Resetear formulario
            this.autoScheduleForm.dates = [''];
            this.autoScheduleForm.start_time = '09:00';
            this.autoScheduleForm.end_time = '17:00';
            this.autoScheduleForm.evaluations_per_hour = 30;
            this.autoScheduleForm.interview_location = '';
            this.autoScheduleForm.overwrite_existing = false;
            this.autoScheduleForm.exclusions = [];
            this.newExclusion = { unit_id: '', date: '' };
            this.showAutoScheduleModal = true;

            // Cargar unidades disponibles
            await this.loadAvailableUnits();
        },

        async loadAvailableUnits() {
            if (!this.autoScheduleForm.job_posting_id) {
                this.availableUnits = [];
                return;
            }

            try {
                const url = "{{ route('api.job-postings.requesting-units', ['id' => '__ID__']) }}".replace('__ID__', this.autoScheduleForm.job_posting_id);
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.availableUnits = data.data || [];
                } else {
                    console.error('Error loading units');
                    this.availableUnits = [];
                }
            } catch (error) {
                console.error('Error loading units:', error);
                this.availableUnits = [];
            }
        },

        addExclusion() {
            if (!this.newExclusion.unit_id || !this.newExclusion.date) {
                console.log('Faltan datos para agregar exclusión');
                return;
            }

            const unit = this.availableUnits.find(u => u.id == this.newExclusion.unit_id);
            if (!unit) {
                console.log('Unidad no encontrada');
                return;
            }

            // Verificar si ya existe esta exclusión
            const exists = this.autoScheduleForm.exclusions.some(e =>
                e.unit_id == this.newExclusion.unit_id && e.date === this.newExclusion.date
            );

            if (exists) {
                alert('Esta exclusión ya ha sido agregada');
                return;
            }

            this.autoScheduleForm.exclusions.push({
                unit_id: this.newExclusion.unit_id,
                unit_name: unit.name,
                date: this.newExclusion.date
            });

            console.log('Exclusión agregada:', {
                unit_id: this.newExclusion.unit_id,
                unit_name: unit.name,
                date: this.newExclusion.date
            });
            console.log('Total de exclusiones:', this.autoScheduleForm.exclusions);

            // Limpiar formulario
            this.newExclusion = { unit_id: '', date: '' };
        },

        removeExclusion(index) {
            this.autoScheduleForm.exclusions.splice(index, 1);
        },

        addDateField() {
            this.autoScheduleForm.dates.push('');
        },

        removeDateField(index) {
            this.autoScheduleForm.dates.splice(index, 1);
        },

        async scheduleInterview() {
            if (!this.scheduleForm.interview_scheduled_at) {
                alert('Por favor ingrese fecha y hora de la entrevista');
                return;
            }

            this.isScheduling = true;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('assignment_id', this.scheduleForm.assignment_id);
            formData.append('interview_scheduled_at', this.scheduleForm.interview_scheduled_at);
            formData.append('interview_duration_minutes', this.scheduleForm.interview_duration_minutes);
            formData.append('interview_location', this.scheduleForm.interview_location);
            formData.append('interview_notes', this.scheduleForm.interview_notes);

            try {
                const response = await fetch('{{ route('interview-schedules.schedule') }}', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Entrevista programada exitosamente');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al programar la entrevista');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al programar la entrevista');
            } finally {
                this.isScheduling = false;
            }
        },

        async autoScheduleInterviews() {
            // Validar fechas
            const validDates = this.autoScheduleForm.dates.filter(d => d !== '');
            if (validDates.length === 0) {
                alert('Por favor agregue al menos una fecha');
                return;
            }

            // Validar horarios
            if (!this.autoScheduleForm.start_time || !this.autoScheduleForm.end_time) {
                alert('Por favor especifique el rango de horas');
                return;
            }

            const confirmMsg = `¿Está seguro de programar automáticamente todas las entrevistas?\n\n` +
                             `📅 Fechas: ${validDates.length} día(s)\n` +
                             `⏰ Horario: ${this.autoScheduleForm.start_time} - ${this.autoScheduleForm.end_time}\n` +
                             `👥 Evaluaciones por hora: ${this.autoScheduleForm.evaluations_per_hour}\n` +
                             `📊 Capacidad total: ${this.calculatedSlots} postulantes\n\n` +
                             `Las entrevistas se agruparán por unidad orgánica y luego por perfil de puesto.`;

            if (!confirm(confirmMsg)) {
                return;
            }

            this.isAutoScheduling = true;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('job_posting_id', this.autoScheduleForm.job_posting_id);
                formData.append('phase_id', this.autoScheduleForm.phase_id);

                validDates.forEach((date, index) => {
                    formData.append(`dates[${index}]`, date);
                });

                // Agregar exclusiones
                console.log('Exclusiones a enviar:', this.autoScheduleForm.exclusions);
                this.autoScheduleForm.exclusions.forEach((exclusion, index) => {
                    console.log(`Agregando exclusión ${index}:`, exclusion);
                    formData.append(`exclusions[${index}][unit_id]`, exclusion.unit_id);
                    formData.append(`exclusions[${index}][date]`, exclusion.date);
                });

                formData.append('start_time', this.autoScheduleForm.start_time);
                formData.append('end_time', this.autoScheduleForm.end_time);
                formData.append('evaluations_per_hour', this.autoScheduleForm.evaluations_per_hour);
                formData.append('interview_location', this.autoScheduleForm.interview_location);
                formData.append('overwrite_existing', this.autoScheduleForm.overwrite_existing ? '1' : '0');

                const response = await fetch('{{ route('interview-schedules.auto-schedule') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    let message = data.message || 'Asignación automática completada exitosamente';

                    if (data.data) {
                        message += `\n\n✅ Programadas: ${data.data.scheduled}`;
                        if (data.data.skipped > 0) {
                            message += `\n⏭️ Omitidas: ${data.data.skipped}`;
                        }
                        message += `\n🏢 Unidades agrupadas: ${data.data.units_count}`;
                    }

                    alert(message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Error en la asignación automática');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al programar automáticamente las entrevistas');
            } finally {
                this.isAutoScheduling = false;
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
