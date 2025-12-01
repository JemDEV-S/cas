<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\User\Services\AssignmentService;
use Modules\User\Services\UserService;
use Modules\Organization\Services\OrganizationalUnitService;
use Modules\User\Entities\User;
use Modules\User\Entities\UserOrganizationUnit;
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\User\Http\Requests\AssignUserRequest;
use Modules\User\Http\Requests\UpdateAssignmentRequest;
use Modules\User\Http\Requests\BulkAssignRequest;
use Modules\User\Http\Requests\TransferUsersRequest;
use Carbon\Carbon;

class AssignmentWebController extends Controller
{
    public function __construct(
        protected AssignmentService $assignmentService,
        protected UserService $userService,
        protected OrganizationalUnitService $organizationalUnitService
    ) {
        $this->middleware('auth');
    }

    /**
     * Lista de asignaciones
     */
    public function index(Request $request)
    {
        $query = UserOrganizationUnit::with(['user', 'organizationUnit']);

        // Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('organization_unit_id')) {
            $query->where('organization_unit_id', $request->organization_unit_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('is_primary')) {
            $query->where('is_primary', $request->boolean('is_primary'));
        }

        if ($request->boolean('current_only')) {
            $query->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                });
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            })->orWhereHas('organizationUnit', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Para los filtros
        $users = User::select('id', 'first_name', 'last_name', 'dni')
            ->orderBy('first_name')
            ->get();
        
        $organizationalUnits = OrganizationalUnit::select('id', 'name', 'code')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user::assignments.index', compact(
            'assignments',
            'users',
            'organizationalUnits'
        ));
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('query');
        
        $users = User::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('dni', 'like', "%{$query}%");
            })
            ->limit(10) // Limitamos para no saturar
            ->get(['id', 'first_name', 'last_name', 'dni']);

        return response()->json($users);
    }
    /**
     * Formulario de nueva asignación
     */
    public function create()
    {
        $users = User::where('is_active', true)
            ->orderBy('first_name')
            ->get();
        
        $organizationalUnits = $this->organizationalUnitService->getActiveUnits();

        return view('user::assignments.create', compact(
            'users',
            'organizationalUnits'
        ));
    }

    /**
     * Guardar nueva asignación
     */
    public function store(AssignUserRequest $request)
    {
        try {
            $user = User::findOrFail($request->user_id);
            $organizationalUnit = OrganizationalUnit::findOrFail($request->organization_unit_id);

            $this->assignmentService->assignUserToUnit(
                $user,
                $organizationalUnit,
                Carbon::parse($request->start_date),
                $request->end_date ? Carbon::parse($request->end_date) : null,
                $request->boolean('is_primary', false)
            );

            return redirect()
                ->route('assignments.index')
                ->with('success', 'Usuario asignado exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al asignar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de asignación
     */
    public function show(UserOrganizationUnit $assignment)
    {
        $assignment->load(['user.profile', 'organizationUnit']);

        return view('user::assignments.show', compact('assignment'));
    }

    /**
     * Formulario de edición
     */
    public function edit(UserOrganizationUnit $assignment)
    {
        $assignment->load(['user', 'organizationUnit']);
        
        $organizationalUnits = $this->organizationalUnitService->getActiveUnits();

        return view('user::assignments.edit', compact(
            'assignment',
            'organizationalUnits'
        ));
    }

    /**
     * Actualizar asignación
     */
    public function update(UpdateAssignmentRequest $request, UserOrganizationUnit $assignment)
    {
        try {
            $data = $request->validated();

            if (isset($data['start_date'])) {
                $data['start_date'] = Carbon::parse($data['start_date']);
            }
            if (isset($data['end_date'])) {
                $data['end_date'] = Carbon::parse($data['end_date']);
            }

            $this->assignmentService->updateAssignment($assignment, $data);

            return redirect()
                ->route('assignments.show', $assignment)
                ->with('success', 'Asignación actualizada exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar asignación: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar asignación
     */
    public function destroy(Request $request, UserOrganizationUnit $assignment)
    {
        try {
            $this->assignmentService->unassignUserFromUnit(
                $assignment,
                $request->input('reason')
            );

            return redirect()
                ->route('assignments.index')
                ->with('success', 'Usuario desasignado exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al desasignar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de asignación masiva
     */
    public function bulkCreate()
    {
        $users = User::where('is_active', true)
            ->orderBy('first_name')
            ->get();
        
        $organizationalUnits = $this->organizationalUnitService->getActiveUnits();

        return view('user::assignments.bulk-create', compact(
            'users',
            'organizationalUnits'
        ));
    }

    /**
     * Procesar asignación masiva
     */
    public function bulkStore(BulkAssignRequest $request)
    {
        try {
            $unit = OrganizationalUnit::findOrFail($request->organization_unit_id);
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

            $results = $this->assignmentService->bulkAssignUsersToUnit(
                $request->user_ids,
                $unit,
                $startDate,
                $endDate
            );

            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            $message = "Asignación masiva completada: {$successCount} exitosas";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} fallidas";
            }

            return redirect()
                ->route('assignments.index')
                ->with('success', $message)
                ->with('bulk_results', $results);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error en asignación masiva: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de transferencia
     */
    public function transferCreate()
    {
        $organizationalUnits = $this->organizationalUnitService->getActiveUnits();

        return view('user::assignments.transfer', compact('organizationalUnits'));
    }

    /**
     * Procesar transferencia
     */
    public function transferStore(TransferUsersRequest $request)
    {
        try {
            $fromUnit = OrganizationalUnit::findOrFail($request->from_unit_id);
            $toUnit = OrganizationalUnit::findOrFail($request->to_unit_id);
            $transferDate = Carbon::parse($request->transfer_date);

            $results = $this->assignmentService->transferUsers($fromUnit, $toUnit, $transferDate);

            $message = "Transferencia completada: {$results['transferred']} usuarios transferidos";
            if ($results['failed'] > 0) {
                $message .= ", {$results['failed']} fallidos";
            }

            return redirect()
                ->route('assignments.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error en transferencia: ' . $e->getMessage());
        }
    }

    /**
     * Vista de asignaciones de un usuario
     */
    public function userAssignments(User $user)
    {
        $activeAssignments = $this->assignmentService->getUserActiveAssignments($user);
        $assignmentHistory = $this->assignmentService->getUserAssignmentHistory($user);
        $primaryAssignment = $this->assignmentService->getUserPrimaryAssignment($user);

        return view('user::assignments.user-assignments', compact(
            'user',
            'activeAssignments',
            'assignmentHistory',
            'primaryAssignment'
        ));
    }

    /**
     * Cambiar unidad principal
     */
    public function changePrimary(Request $request, User $user)
    {
        $request->validate([
            'organization_unit_id' => 'required|uuid|exists:organizational_units,id',
            'start_date' => 'required|date',
        ]);

        try {
            $newUnit = OrganizationalUnit::findOrFail($request->organization_unit_id);
            $startDate = Carbon::parse($request->start_date);

            $this->assignmentService->changePrimaryUnit($user, $newUnit, $startDate);

            return redirect()
                ->back()
                ->with('success', 'Unidad organizacional principal cambiada exitosamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al cambiar unidad principal: ' . $e->getMessage());
        }
    }

    /**
     * Vista de usuarios de una unidad
     */
    public function unitUsers(OrganizationalUnit $unit)
    {
        $activeUsers = $this->assignmentService->getUnitAssignedUsers($unit, true);
        $statistics = $this->assignmentService->getAssignmentStatistics($unit);

        return view('user::assignments.unit-users', compact(
            'unit',
            'activeUsers',
            'statistics'
        ));
    }
}