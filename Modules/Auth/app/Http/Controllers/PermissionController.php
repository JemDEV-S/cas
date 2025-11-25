<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Entities\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:auth.view.permissions')->only(['index']);
        $this->middleware('permission:auth.view.permission')->only(['show']);
    }

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $query = Permission::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por módulo
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'module');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $permissions = $query->paginate(20)->withQueryString();

        // Obtener módulos únicos para el filtro
        $modules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        return view('auth::permissions.index', compact('permissions', 'modules'));
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        $permission->load('roles');

        return view('auth::permissions.show', compact('permission'));
    }
}
