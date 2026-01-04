<?php

namespace Modules\JobPosting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};
use Modules\Organization\Entities\OrganizationalUnit;

class ScheduleController extends Controller
{
    /**
     * Muestra la pantalla de edición (La tabla editable)
     */
    public function edit(JobPosting $jobPosting)
    {
        // Verificar permisos si es necesario
        if (!$jobPosting->canBeEdited()) {
            return redirect()->route('jobposting.show', $jobPosting)
                ->with('error', 'No se puede editar el cronograma en el estado actual.');
        }

        $jobPosting->load(['schedules.phase', 'schedules.responsibleUnit']);
        
        // Ordenar el cronograma existente por el número de fase
        $schedules = $jobPosting->schedules->sortBy('phase.phase_number');

        // CORRECCIÓN: Ordenar las fases disponibles por 'phase_number' (no por 'order' si este es nulo)
        $phases = ProcessPhase::orderBy('phase_number', 'asc')->get();
        
        $units = OrganizationalUnit::orderBy('name')->get();

        return view('jobposting::schedule', compact('jobPosting', 'schedules', 'phases', 'units'));
    }

    /**
     * Guarda los cambios de la tabla
     */
    public function update(Request $request, JobPosting $jobPosting)
    {
        $data = $request->validate([
            'schedules' => 'nullable|array',
            'schedules.*.process_phase_id' => 'required|exists:process_phases,id|distinct',
            'schedules.*.start_date' => 'required|date',
            'schedules.*.end_date' => 'nullable|date|after_or_equal:schedules.*.start_date',
            'schedules.*.location' => 'nullable|string|max:255',
            'schedules.*.responsible_unit_id' => 'nullable|exists:organizational_units,id',
        ]);

        DB::transaction(function () use ($jobPosting, $data) {
            // 1. Obtener los IDs de fases que vienen del formulario
            $phasesInForm = collect($data['schedules'])->pluck('process_phase_id')->toArray();

            // 2. Eliminar REALMENTE (forceDelete) solo las fases que el usuario quitó de la lista
            // Usamos forceDelete para evitar el error de duplicados si se vuelven a agregar luego
            $jobPosting->schedules()
                ->whereNotIn('process_phase_id', $phasesInForm)
                ->forceDelete();

            // 3. Actualizar o Crear las fases enviadas
            if (!empty($data['schedules'])) {
                foreach ($data['schedules'] as $row) {
                    $jobPosting->schedules()->updateOrCreate(
                        [
                            'process_phase_id' => $row['process_phase_id'] // Buscamos por este campo
                        ],
                        [
                            // Actualizamos estos campos
                            'start_date' => $row['start_date'],
                            'end_date' => $row['end_date'] ?? $row['start_date'],
                            'location' => $row['location'] ?? null,
                            'responsible_unit_id' => $row['responsible_unit_id'] ?? null,
                            'notify_before' => true,
                            // No reseteamos el 'status' a PENDING para no perder el progreso si ya estaba completada
                        ]
                    );
                }
            }
        });

        return redirect()->route('jobposting.schedule.edit', $jobPosting)
            ->with('success', '✅ Cronograma actualizado correctamente.');
    }

    /**
     * Genera las 12 fases automáticamente si el usuario lo pide
     */
    public function initialize(Request $request, JobPosting $jobPosting)
    {
        // Llamamos a la lógica que ya tienes en tu Service, o la replicamos aquí brevemente
        // Lo ideal es inyectar el JobPostingService, pero para simplificar lo hago directo:
        
        $phases = ProcessPhase::orderBy('order')->get();
        $currentDate = now(); // O la fecha que elija el usuario

        DB::transaction(function() use ($jobPosting, $phases, $currentDate) {
            $jobPosting->schedules()->delete();

            foreach ($phases as $phase) {
                $days = match($phase->order) { 3 => 2, 6 => 3, 8 => 2, default => 1 };
                $endDate = (clone $currentDate)->addDays($days - 1);
                
                $jobPosting->schedules()->create([
                    'process_phase_id' => $phase->id,
                    'start_date' => $currentDate,
                    'end_date' => ($days > 1) ? $endDate : null,
                    'location' => 'Portal Institucional',
                    'status' => 'PENDING'
                ]);
                $currentDate = (clone $endDate)->addDay();
            }
        });

        return redirect()->route('jobposting.schedule.edit', $jobPosting)
            ->with('success', '⚡ Cronograma automático generado.');
    }

    /**
     * Iniciar fase manualmente
     */
    public function startPhase(\Modules\JobPosting\Entities\JobPostingSchedule $schedule)
    {
        try {
            if ($schedule->status === \Modules\JobPosting\Enums\ScheduleStatusEnum::IN_PROGRESS) {
                return back()->with('info', 'ℹ️ La fase ya está en progreso.');
            }

            if ($schedule->status === \Modules\JobPosting\Enums\ScheduleStatusEnum::COMPLETED) {
                return back()->with('error', '❌ No se puede iniciar una fase ya completada.');
            }

            $schedule->start();

            return back()->with('success', "✅ Fase '{$schedule->phase->name}' iniciada manualmente.");

        } catch (\Exception $e) {
            \Log::error('Error iniciando fase manualmente: ' . $e->getMessage());
            return back()->with('error', '❌ Error al iniciar fase: ' . $e->getMessage());
        }
    }

    /**
     * Completar fase manualmente
     */
    public function completePhase(\Modules\JobPosting\Entities\JobPostingSchedule $schedule)
    {
        try {
            if ($schedule->status === \Modules\JobPosting\Enums\ScheduleStatusEnum::COMPLETED) {
                return back()->with('info', 'ℹ️ La fase ya está completada.');
            }

            $schedule->complete();

            return back()->with('success', "✅ Fase '{$schedule->phase->name}' completada manualmente.");

        } catch (\Exception $e) {
            \Log::error('Error completando fase manualmente: ' . $e->getMessage());
            return back()->with('error', '❌ Error al completar fase: ' . $e->getMessage());
        }
    }

    /**
     * Saltar a la siguiente fase (completa actual e inicia siguiente)
     */
    public function skipToNext(\Modules\JobPosting\Entities\JobPostingSchedule $schedule)
    {
        try {
            // Completar la fase actual
            if ($schedule->status !== \Modules\JobPosting\Enums\ScheduleStatusEnum::COMPLETED) {
                $schedule->complete();
            }

            // Buscar la siguiente fase
            $nextSchedule = \Modules\JobPosting\Entities\JobPostingSchedule::where('job_posting_id', $schedule->job_posting_id)
                ->where('status', \Modules\JobPosting\Enums\ScheduleStatusEnum::PENDING)
                ->whereHas('phase', function($q) use ($schedule) {
                    $q->where('phase_number', '>', $schedule->phase->phase_number);
                })
                ->orderBy('start_date')
                ->first();

            if (!$nextSchedule) {
                return back()->with('info', "✅ Fase '{$schedule->phase->name}' completada. No hay más fases pendientes.");
            }

            // Iniciar la siguiente fase
            $nextSchedule->start();

            return back()->with('success', "✅ Fase '{$schedule->phase->name}' completada y '{$nextSchedule->phase->name}' iniciada.");

        } catch (\Exception $e) {
            \Log::error('Error saltando a siguiente fase: ' . $e->getMessage());
            return back()->with('error', '❌ Error al saltar a siguiente fase: ' . $e->getMessage());
        }
    }
}