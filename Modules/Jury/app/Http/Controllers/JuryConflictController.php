<?php

namespace Modules\Jury\Http\Controllers;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Routing\Controller;
use Modules\Jury\Services\ConflictDetectionService;
use Modules\Jury\Http\Requests\ReportConflictRequest;

class JuryConflictController extends Controller
{
    public function __construct(
        protected ConflictDetectionService $service
    ) {}

    public function index(Request $request)
    {
        $query = \Modules\Jury\Entities\JuryConflict::with([
            'juryMember.user',
            'application',
            'reportedBy',
        ]);

        if ($request->has('job_posting_id')) {
            $query->byJobPosting($request->input('job_posting_id'));
        }

        if ($request->has('jury_member_id')) {
            $query->byJuryMember($request->input('jury_member_id'));
        }

        if ($request->has('status')) {
            $query->byStatus(\Modules\Jury\Enums\ConflictStatus::from($request->input('status')));
        }

        if ($request->boolean('pending_only')) {
            $query->pending();
        }

        if ($request->boolean('high_priority')) {
            $query->highPriority();
        }

        $conflicts = $query->ordered()->paginate($request->input('per_page', 15));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'data' => $conflicts]);
        }

        return view('jury::conflicts.index', compact('conflicts'));
    }

    public function store(ReportConflictRequest $request): JsonResponse
    {
        try {
            $conflict = $this->service->report($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Conflicto reportado exitosamente',
                'data' => $conflict,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            $conflict = \Modules\Jury\Entities\JuryConflict::with([
                'juryMember.user',
                'application',
                'jobPosting',
                'reportedBy',
                'resolvedBy',
            ])->findOrFail($id);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => true, 'data' => $conflict]);
            }

            return view('jury::conflicts.show', compact('conflict'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
            }
            abort(404);
        }
    }

    public function moveToReview(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        try {
            $conflict = $this->service->moveToReview($id, $validated['notes'] ?? null);
            return response()->json(['success' => true, 'message' => 'Conflicto en revisión', 'data' => $conflict]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function confirm(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        try {
            $conflict = $this->service->confirm($id, $validated['notes'] ?? null);
            return response()->json(['success' => true, 'message' => 'Conflicto confirmado', 'data' => $conflict]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function dismiss(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['resolution' => ['required', 'string']]);

        try {
            $conflict = $this->service->dismiss($id, $validated['resolution']);
            return response()->json(['success' => true, 'message' => 'Conflicto desestimado', 'data' => $conflict]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function resolve(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'resolution' => ['required', 'string'],
            'action_taken' => ['required', 'string', 'in:EXCUSED,REASSIGNED,APPLICANT_REMOVED,NO_ACTION,OTHER'],
            'action_notes' => ['nullable', 'string'],
        ]);

        try {
            $conflict = $this->service->resolve(
                $id,
                $validated['resolution'],
                $validated['action_taken'],
                $validated['action_notes'] ?? null
            );
            return response()->json(['success' => true, 'message' => 'Conflicto resuelto', 'data' => $conflict]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function excuse(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        try {
            $conflict = $this->service->excuseJuryMember($id, $validated['notes'] ?? null);
            return response()->json(['success' => true, 'message' => 'Jurado excusado', 'data' => $conflict]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function autoDetect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jury_member_id' => ['required', 'string', 'exists:jury_members,id'],
            'application_id' => ['required', 'string', 'exists:applications,id'],
        ]);

        try {
            $detected = $this->service->autoDetect($validated['jury_member_id'], $validated['application_id']);
            return response()->json(['success' => true, 'data' => $detected]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['job_posting_id', 'from_date']);
            $stats = $this->service->getStatistics($filters);
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
