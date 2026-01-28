<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Evaluation\Entities\EvaluatorAssignment;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InterviewScheduleController extends Controller
{
    /**
     * Vista principal para gestionar cronograma de entrevistas
     * GET /interview-schedules
     */
    public function index(Request $request)
    {
        try {
            // Obtener convocatorias activas
            $jobPostings = JobPosting::where('status', 'PUBLICADA')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get(['id', 'title', 'code']);

            // Obtener fase de entrevista
            $interviewPhase = ProcessPhase::where('code', 'PHASE_08_INTERVIEW')
                ->orWhere('name', 'like', '%Entrevista%')
                ->first();

            $phases = ProcessPhase::where('is_active', true)
                ->orderBy('order')
                ->get(['id', 'name', 'code']);

            // Si hay filtros aplicados, obtener las entrevistas
            $interviews = collect([]);
            $stats = [
                'total' => 0,
                'scheduled' => 0,
                'pending' => 0,
                'completed' => 0,
            ];

            if ($request->has('job_posting_id') && $request->has('phase_id')) {
                $query = EvaluatorAssignment::with([
                    'user',
                    'application.applicant',
                    'application.jobProfile.jobPosting',
                    'application.jobProfile.positionCode',
                    'phase'
                ])
                ->where('job_posting_id', $request->input('job_posting_id'))
                ->where('phase_id', $request->input('phase_id'));

                // Filtro por estado de programación
                if ($request->has('schedule_status')) {
                    $scheduleStatus = $request->input('schedule_status');
                    if ($scheduleStatus === 'scheduled') {
                        $query->whereNotNull('interview_scheduled_at');
                    } elseif ($scheduleStatus === 'pending') {
                        $query->whereNull('interview_scheduled_at');
                    }
                }

                // Filtro por evaluador
                if ($request->has('evaluator_id') && $request->input('evaluator_id') != '') {
                    $query->where('user_id', $request->input('evaluator_id'));
                }

                $interviews = $query->orderBy('interview_scheduled_at', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Calcular estadísticas
                $stats = [
                    'total' => $interviews->count(),
                    'scheduled' => $interviews->whereNotNull('interview_scheduled_at')->count(),
                    'pending' => $interviews->whereNull('interview_scheduled_at')->count(),
                    'completed' => $interviews->where('status', 'COMPLETED')->count(),
                ];
            }

            // Obtener evaluadores (jurados) para filtro
            $evaluators = collect([]);
            if ($request->has('job_posting_id')) {
                $evaluators = \Modules\User\Entities\User::whereHas('juryAssignments', function($q) use ($request) {
                    $q->where('job_posting_id', $request->input('job_posting_id'))
                      ->where('status', 'ACTIVE');
                })->get(['id', 'first_name', 'last_name', 'email']);
            }

            return view('evaluation::interviews.schedule', compact(
                'jobPostings',
                'phases',
                'interviewPhase',
                'interviews',
                'stats',
                'evaluators'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in interview schedule index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return view('evaluation::interviews.schedule', [
                'jobPostings' => collect([]),
                'phases' => collect([]),
                'interviewPhase' => null,
                'interviews' => collect([]),
                'stats' => ['total' => 0, 'scheduled' => 0, 'pending' => 0, 'completed' => 0],
                'evaluators' => collect([]),
            ])->with('error', 'Error al cargar el cronograma de entrevistas: ' . $e->getMessage());
        }
    }

    /**
     * Programar fecha/hora de entrevista
     * POST /interview-schedules/schedule
     */
    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => ['required', 'exists:evaluator_assignments,id'],
            'interview_scheduled_at' => ['required', 'date', 'after:now'],
            'interview_duration_minutes' => ['nullable', 'integer', 'min:15', 'max:240'],
            'interview_location' => ['nullable', 'string', 'max:255'],
            'interview_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $assignment = EvaluatorAssignment::findOrFail($validated['assignment_id']);

            $assignment->update([
                'interview_scheduled_at' => $validated['interview_scheduled_at'],
                'interview_duration_minutes' => $validated['interview_duration_minutes'] ?? 30,
                'interview_location' => $validated['interview_location'] ?? null,
                'interview_notes' => $validated['interview_notes'] ?? null,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Entrevista programada exitosamente',
                    'data' => $assignment->fresh(),
                ]);
            }

            return back()->with('success', 'Entrevista programada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error scheduling interview: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al programar la entrevista',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Error al programar la entrevista: ' . $e->getMessage());
        }
    }

    /**
     * Generar PDF del cronograma de entrevistas
     * GET /interview-schedules/pdf
     */
    public function generatePDF(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'phase_id' => ['required', 'exists:process_phases,id'],
            'evaluator_id' => ['nullable', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'only_scheduled' => ['nullable', 'boolean'],
        ]);

        try {
            // Obtener convocatoria y fase
            $jobPosting = JobPosting::findOrFail($validated['job_posting_id']);
            $phase = ProcessPhase::findOrFail($validated['phase_id']);

            // Query de entrevistas
            $query = EvaluatorAssignment::with([
                'user',
                'application.applicant',
                'application.jobProfile.positionCode',
                'application.jobProfile.requestingUnit',
                'phase'
            ])
            ->where('job_posting_id', $validated['job_posting_id'])
            ->where('phase_id', $validated['phase_id']);

            // Filtros opcionales
            if (isset($validated['evaluator_id'])) {
                $query->where('user_id', $validated['evaluator_id']);
            }

            if (isset($validated['date_from'])) {
                $query->whereDate('interview_scheduled_at', '>=', $validated['date_from']);
            }

            if (isset($validated['date_to'])) {
                $query->whereDate('interview_scheduled_at', '<=', $validated['date_to']);
            }

            if ($validated['only_scheduled'] ?? false) {
                $query->whereNotNull('interview_scheduled_at');
            }

            $interviews = $query->orderBy('interview_scheduled_at', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            // Datos para el PDF (el agrupamiento se hace en la vista)
            $data = [
                'jobPosting' => $jobPosting,
                'phase' => $phase,
                'interviews' => $interviews,
                'generatedAt' => now(),
                'stats' => [
                    'total' => $interviews->count(),
                    'scheduled' => $interviews->whereNotNull('interview_scheduled_at')->count(),
                    'pending' => $interviews->whereNull('interview_scheduled_at')->count(),
                ],
            ];

            // Generar PDF
            $pdf = Pdf::loadView('evaluation::interviews.pdf', $data);
            $pdf->setPaper('a4', 'landscape'); // Horizontal para más espacio

            $filename = 'cronograma_entrevistas_' .
                        \Str::slug($jobPosting->code) . '_' .
                        now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error generating interview schedule PDF: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar programación de entrevista
     * DELETE /interview-schedules/{id}
     */
    public function unschedule(string $id)
    {
        try {
            $assignment = EvaluatorAssignment::findOrFail($id);

            $assignment->update([
                'interview_scheduled_at' => null,
                'interview_duration_minutes' => null,
                'interview_location' => null,
                'interview_notes' => null,
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Programación de entrevista eliminada',
                ]);
            }

            return back()->with('success', 'Programación de entrevista eliminada');

        } catch (\Exception $e) {
            \Log::error('Error unscheduling interview: ' . $e->getMessage());

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la programación',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Error al eliminar la programación');
        }
    }

    /**
     * Asignación automática de entrevistas agrupadas por job_profile
     * POST /interview-schedules/auto-schedule
     */
    public function autoSchedule(Request $request)
    {
        $validated = $request->validate([
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'phase_id' => ['required', 'exists:process_phases,id'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'evaluations_per_hour' => ['required', 'integer', 'min:1', 'max:200'],
            'interview_location' => ['nullable', 'string', 'max:255'],
            'overwrite_existing' => ['nullable', 'boolean'],
            'exclusions' => ['nullable', 'array'],
            'exclusions.*.unit_id' => ['required', 'string'],
            'exclusions.*.date' => ['required', 'date'],
        ]);

        try {
            // Obtener todas las asignaciones sin programar (o todas si overwrite_existing es true)
            $query = EvaluatorAssignment::with([
                'application.jobProfile.positionCode',
                'application.jobProfile.requestingUnit',
            ])
            ->where('job_posting_id', $validated['job_posting_id'])
            ->where('phase_id', $validated['phase_id']);

            if (!($validated['overwrite_existing'] ?? false)) {
                $query->whereNull('interview_scheduled_at');
            }

            $assignments = $query->get();

            if ($assignments->isEmpty()) {
                return back()->with('error', 'No hay asignaciones disponibles para programar');
            }

            // Agrupar primero por nombre de unidad orgánica, luego por nombre de perfil de puesto
            $groupedByUnit = $assignments->groupBy(function($assignment) {
                $unitName = $assignment->application->jobProfile->requestingUnit->name ?? 'Sin Unidad';
                return $unitName;
            })->sortKeys();

            // Crear lista ordenada de asignaciones: primero por unidad, luego por perfil
            $orderedAssignments = collect();
            foreach ($groupedByUnit as $unitName => $unitAssignments) {
                // Dentro de cada unidad, agrupar por nombre de perfil y ordenar
                $groupedByProfile = $unitAssignments->groupBy(function($assignment) {
                    $profileName = $assignment->application->jobProfile->profile_name ?? $assignment->application->jobProfile->title ?? 'Sin Perfil';
                    return $profileName;
                })->sortKeys();

                // Agregar las asignaciones de cada perfil a la lista ordenada
                foreach ($groupedByProfile as $profileName => $profileAssignments) {
                    $orderedAssignments = $orderedAssignments->merge($profileAssignments);
                }
            }

            // Generar franjas horarias
            $timeSlots = $this->generateHourlyTimeSlots(
                $validated['dates'],
                $validated['start_time'],
                $validated['end_time']
            );

            if (empty($timeSlots)) {
                return back()->with('error', 'No se pudieron generar franjas horarias válidas');
            }

            // Distribuir entrevistas en franjas horarias
            $scheduled = 0;
            $skipped = 0;
            $slotIndex = 0;
            $assignmentIndex = 0;
            $evaluationsPerHour = $validated['evaluations_per_hour'];
            $totalSlots = count($timeSlots);
            $totalAssignments = $orderedAssignments->count();

            // Log de información inicial
            \Log::info('Iniciando asignación automática de entrevistas', [
                'total_assignments' => $totalAssignments,
                'total_units' => $groupedByUnit->count(),
                'total_slots' => $totalSlots,
                'evaluations_per_hour' => $evaluationsPerHour,
                'dates' => $validated['dates'],
            ]);

            // Procesar exclusiones
            $exclusions = $validated['exclusions'] ?? [];
            $exclusionMap = [];
            foreach ($exclusions as $exclusion) {
                $date = $exclusion['date'];
                if (!isset($exclusionMap[$date])) {
                    $exclusionMap[$date] = [];
                }
                // Asegurar que el unit_id sea string para comparación consistente
                $exclusionMap[$date][] = (string) $exclusion['unit_id'];
            }

            // Log del mapa de exclusiones
            \Log::info('Mapa de exclusiones creado', [
                'exclusionMap' => $exclusionMap,
                'exclusions_raw' => $exclusions,
            ]);

            // Asignar por franjas horarias
            $excludedAssignments = collect();
            while ($assignmentIndex < $totalAssignments && $slotIndex < $totalSlots) {
                $currentSlot = $timeSlots[$slotIndex];
                $currentDate = $currentSlot->format('Y-m-d');
                $assignedInCurrentSlot = 0;

                // Asignar hasta evaluationsPerHour postulantes a esta franja
                while ($assignedInCurrentSlot < $evaluationsPerHour && $assignmentIndex < $totalAssignments) {
                    $assignment = $orderedAssignments[$assignmentIndex];
                    $profileName = $assignment->application->jobProfile->profile_name ?? 'N/A';
                    $unitName = $assignment->application->jobProfile->requestingUnit->name ?? 'N/A';
                    $unitId = $assignment->application->jobProfile->requesting_unit_id ?? null;

                    // Verificar si esta unidad está excluida en esta fecha
                    // Convertir a string para comparación consistente
                    $unitIdStr = $unitId ? (string) $unitId : null;
                    $isExcluded = isset($exclusionMap[$currentDate]) &&
                                 $unitIdStr &&
                                 in_array($unitIdStr, $exclusionMap[$currentDate], true);

                    if ($isExcluded) {
                        // Guardar para procesar después
                        $excludedAssignments->push($assignment);
                        $assignmentIndex++;
                        \Log::info('Entrevista excluida temporalmente', [
                            'assignment_id' => $assignment->id,
                            'date' => $currentDate,
                            'unit' => $unitName,
                            'unit_id' => $unitIdStr,
                            'excluded_units_for_date' => $exclusionMap[$currentDate] ?? [],
                        ]);
                        continue;
                    }

                    // Log para debug (solo si hay exclusiones configuradas)
                    if (!empty($exclusionMap) && isset($exclusionMap[$currentDate])) {
                        \Log::debug('Verificando exclusión', [
                            'assignment_id' => $assignment->id,
                            'unit' => $unitName,
                            'unit_id' => $unitIdStr,
                            'current_date' => $currentDate,
                            'excluded_units_for_date' => $exclusionMap[$currentDate],
                            'is_excluded' => $isExcluded,
                        ]);
                    }

                    $assignment->update([
                        'interview_scheduled_at' => $currentSlot,
                        'interview_duration_minutes' => null,
                        'interview_location' => $validated['interview_location'] ?? null,
                        'interview_notes' => 'Programado automáticamente - Unidad: ' . $unitName . ' - Perfil: ' . $profileName,
                    ]);

                    $scheduled++;
                    $assignmentIndex++;
                    $assignedInCurrentSlot++;

                    \Log::info('Entrevista programada', [
                        'assignment_id' => $assignment->id,
                        'slot' => $currentSlot->format('Y-m-d H:i'),
                        'applicant' => $assignment->application->full_name ?? 'N/A',
                        'unit' => $unitName,
                        'profile' => $profileName,
                        'position_in_slot' => $assignedInCurrentSlot,
                    ]);
                }

                // Pasar a la siguiente franja horaria
                $slotIndex++;
            }

            // Intentar asignar las entrevistas excluidas en slots disponibles
            foreach ($excludedAssignments as $assignment) {
                $unitId = $assignment->application->jobProfile->requesting_unit_id ?? null;
                $unitIdStr = $unitId ? (string) $unitId : null;
                $profileName = $assignment->application->jobProfile->profile_name ?? 'N/A';
                $unitName = $assignment->application->jobProfile->requestingUnit->name ?? 'N/A';

                // Buscar un slot que no esté excluido para esta unidad
                foreach ($timeSlots as $slot) {
                    $slotDate = $slot->format('Y-m-d');
                    $isExcluded = isset($exclusionMap[$slotDate]) &&
                                 $unitIdStr &&
                                 in_array($unitIdStr, $exclusionMap[$slotDate], true);

                    if (!$isExcluded) {
                        $assignment->update([
                            'interview_scheduled_at' => $slot,
                            'interview_duration_minutes' => null,
                            'interview_location' => $validated['interview_location'] ?? null,
                            'interview_notes' => 'Programado automáticamente (reasignado) - Unidad: ' . $unitName . ' - Perfil: ' . $profileName,
                        ]);

                        $scheduled++;
                        \Log::info('Entrevista excluida reasignada', [
                            'assignment_id' => $assignment->id,
                            'slot' => $slot->format('Y-m-d H:i'),
                            'unit' => $unitName,
                        ]);
                        break;
                    }
                }
            }

            // Contar los que no se pudieron programar
            $skipped = $totalAssignments - $scheduled;

            $message = "Asignación automática completada: {$scheduled} entrevistas programadas";
            if ($skipped > 0) {
                $message .= " | {$skipped} omitidas (sin franjas horarias disponibles)";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'scheduled' => $scheduled,
                        'skipped' => $skipped,
                        'total' => $totalAssignments,
                        'units_count' => $groupedByUnit->count(),
                        'slots_used' => $slotIndex,
                    ],
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Error in auto-schedule: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al programar automáticamente las entrevistas',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Error al programar automáticamente: ' . $e->getMessage());
        }
    }

    /**
     * Generar franjas horarias para las entrevistas (cada hora completa)
     * Omite la hora de almuerzo (13:00 - 14:00)
     *
     * @param array $dates Array de fechas en formato Y-m-d
     * @param string $startTime Hora de inicio (H:i)
     * @param string $endTime Hora de fin (H:i)
     * @return array Array de objetos Carbon con las franjas horarias disponibles
     */
    private function generateHourlyTimeSlots(array $dates, string $startTime, string $endTime): array
    {
        $slots = [];

        foreach ($dates as $date) {
            $currentDate = Carbon::parse($date);

            // Parsear horas de inicio y fin
            list($startHour, $startMinute) = explode(':', $startTime);
            list($endHour, $endMinute) = explode(':', $endTime);

            $start = $currentDate->copy()->setTime($startHour, $startMinute, 0);
            $end = $currentDate->copy()->setTime($endHour, $endMinute, 0);

            // Generar franjas horarias (cada hora)
            $currentSlot = $start->copy();

            while ($currentSlot->lessThan($end)) {
                // Omitir la hora de almuerzo (13:00 - 14:00)
                if ($currentSlot->hour !== 13) {
                    $slots[] = $currentSlot->copy();
                }
                $currentSlot->addHour();
            }
        }

        return $slots;
    }
}
