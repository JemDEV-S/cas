<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Entities\Role;
use Modules\Auth\Entities\Permission;
use Modules\Auth\Services\RoleService;
use Modules\Auth\Http\Requests\StoreRoleRequest;
use Modules\Auth\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:auth.view.roles')->only(['index']);
        $this->middleware('permission:auth.view.role')->only(['show']);
        $this->middleware('permission:auth.create.role')->only(['create', 'store']);
        $this->middleware('permission:auth.update.role')->only(['edit', 'update']);
        $this->middleware('permission:auth.delete.role')->only(['destroy']);
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::with('permissions');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $roles = $query->paginate(15)->withQueryString();

        return view('auth::roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::where('is_active', true)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('auth::roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request)
    {
        $this->authorize('create', Role::class);

        try {
            $role = $this->roleService->create($request->validated());

            // Asignar permisos
            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return redirect()
                ->route('roles.show', $role)
                ->with('success', 'Rol creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear rol: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);

        $role->load('permissions', 'users');

        return view('auth::roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $role->load('permissions');

        $permissions = Permission::where('is_active', true)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('auth::roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        try {
            $this->roleService->update($role->id, $request->validated());

            // Actualizar permisos
            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]);
            }

            return redirect()
                ->route('roles.show', $role)
                ->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar rol: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        try {
            // Verificar si el rol tiene usuarios asignados
            if ($role->users()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
            }

            $this->roleService->delete($role->id);

            return redirect()
                ->route('roles.index')
                ->with('success', 'Rol eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar rol: ' . $e->getMessage());
        }
    }

    /**
     * Toggle role status (activate/deactivate).
     */
    public function toggleStatus(Role $role)
    {
        $this->authorize('toggleStatus', $role);

        try {
            $role->update([
                'is_active' => !$role->is_active
            ]);

            $status = $role->is_active ? 'activado' : 'desactivado';

            return redirect()
                ->back()
                ->with('success', "Rol {$status} exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }
}
