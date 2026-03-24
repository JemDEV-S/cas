<?php

namespace Modules\Application\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Application\Entities\IaJob;

class IaJobController extends Controller
{
    /**
     * GET /api/ia/jobs
     * Retorna jobs pendientes para el agente Python.
     * Marca como "procesando" los jobs que entrega.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);

        $jobs = IaJob::where('status', 'pendiente')
            ->where('attempts', '<', 3)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        // Marcar como procesando
        foreach ($jobs as $job) {
            $job->markAsProcesando();
        }

        if ($jobs->isNotEmpty()) {
            Log::channel('ia')->info("Agente solicitó jobs: {$jobs->count()} entregados");
        }

        return response()->json([
            'success' => true,
            'count' => $jobs->count(),
            'jobs' => $jobs->map(fn(IaJob $job) => [
                'id' => $job->id,
                'application_id' => $job->application_id,
                'applicant_career' => $job->applicant_career,
                'applicant_degree_type' => $job->applicant_degree_type,
                'required_careers' => $job->required_careers,
                'attempts' => $job->attempts,
            ]),
        ]);
    }

    /**
     * POST /api/ia/jobs/{id}/result
     * Recibe el resultado del LLM desde el agente.
     */
    public function storeResult(Request $request, string $id): JsonResponse
    {
        $job = IaJob::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job no encontrado',
            ], 404);
        }

        if ($job->status === 'completado') {
            return response()->json([
                'success' => false,
                'message' => 'Job ya fue completado',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'resultado' => 'required|in:cumple_exacto,cumple_equivalente,cumple_afin,no_cumple,indeterminado',
            'score' => 'required|numeric|min:0|max:1',
            'justificacion' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $job->markAsCompletado(
            $validated['resultado'],
            $validated['score'],
            $validated['justificacion']
        );

        Log::channel('ia')->info("Job {$id} completado: {$validated['resultado']} (score: {$validated['score']})");

        return response()->json([
            'success' => true,
            'message' => 'Resultado guardado correctamente',
        ]);
    }

    /**
     * POST /api/ia/jobs/{id}/error
     * Reporta un error desde el agente.
     */
    public function storeError(Request $request, string $id): JsonResponse
    {
        $job = IaJob::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job no encontrado',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'error_message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $job->markAsError($validator->validated()['error_message']);

        Log::channel('ia')->warning("Job {$id} error (intento {$job->attempts}): {$request->input('error_message')}");

        return response()->json([
            'success' => true,
            'message' => 'Error registrado',
            'will_retry' => $job->status === 'pendiente',
        ]);
    }

    /**
     * GET /api/ia/stats
     * Estadísticas para monitoreo.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'pendientes' => IaJob::where('status', 'pendiente')->count(),
            'procesando' => IaJob::where('status', 'procesando')->count(),
            'completados' => IaJob::where('status', 'completado')->count(),
            'errores' => IaJob::where('status', 'error')->count(),
        ]);
    }
}
