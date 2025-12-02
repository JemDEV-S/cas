<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\User\Entities\User;
use Modules\User\Services\UserService;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\Auth\Entities\Role;
use Modules\Organization\Entities\OrganizationalUnit;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:user.view.users')->only(['index']);
        $this->middleware('permission:user.view.user')->only(['show']);
        $this->middleware('permission:user.create.user')->only(['create', 'store']);
        $this->middleware('permission:user.update.user')->only(['edit', 'update']);
        $this->middleware('permission:user.delete.user')->only(['destroy']);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'profile']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('slug', $request->role);
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::where('is_active', true)->get();

        return view('user::index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::where('is_active', true)->get();
        $organizationalUnits = OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user::create', compact('roles', 'organizationalUnits'));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        try {
            $user = $this->userService->create($request->validated());

            // Asignar roles
            if ($request->filled('roles')) {
                $user->syncRoles($request->roles);
            }

            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'profile', 'preference', 'organizationUnits.userOrganizationUnits']);

        return view('user::show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load('roles');
        $roles = Role::where('is_active', true)->get();
        $organizationalUnits = OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user::edit', compact('user', 'roles', 'organizationalUnits'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        try {
            // Preparar los datos para actualizar
            $updateData = $request->validated();

            // Solo actualizar la contraseña si se proporciona una nueva
            if (empty($updateData['password'])) {
                // Eliminar la contraseña de los datos a actualizar
                unset($updateData['password']);
                unset($updateData['current_password']);
                unset($updateData['password_confirmation']);
            } else {
                // Verificar que la contraseña actual es correcta
                if (!Hash::check($request->current_password, $user->password)) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'La contraseña actual es incorrecta.');
                }

                // Hashear la nueva contraseña
                $updateData['password'] = Hash::make($updateData['password']);
            }

            $this->userService->update($user->id, $updateData);

            // Actualizar roles
            if ($request->filled('roles')) {
                $user->syncRoles($request->roles);
            }

            return redirect()
                ->route('users.show', $user)
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->delete($user->id);

            return redirect()
                ->route('users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (activate/deactivate).
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('toggleStatus', $user);

        try {
            $user->update([
                'is_active' => !$user->is_active
            ]);

            $status = $user->is_active ? 'activado' : 'desactivado';

            return redirect()
                ->back()
                ->with('success', "Usuario {$status} exitosamente.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    /**
     * Export users to Excel.
     */
    public function export(Request $request)
    {
        $this->authorize('export', User::class);

        try {
            // TODO: Implementar exportación
            return redirect()
                ->back()
                ->with('info', 'Función de exportación en desarrollo.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }
}
