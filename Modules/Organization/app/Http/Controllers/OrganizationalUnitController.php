<?php

namespace Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Http\Requests\StoreOrganizationalUnitRequest;
use Modules\Organization\Http\Requests\UpdateOrganizationalUnitRequest;
use Modules\Organization\Services\HierarchyService;

class OrganizationalUnitController extends Controller
{
    public function __construct(
        protected HierarchyService $hierarchyService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:organization.view.units')->only(['index']);
        $this->middleware('permission:organization.view.unit')->only(['show']);
        $this->middleware('permission:organization.create.unit')->only(['create', 'store']);
        $this->middleware('permission:organization.update.unit')->only(['edit', 'update']);
        $this->middleware('permission:organization.delete.unit')->only(['destroy']);
    }

    /**
     * Display a listing of organizational units.
     */
    public function index(Request $request)
    {
        $query = OrganizationalUnit::with(['parent']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $units = $query->paginate(15)->withQueryString();

        // Obtener tipos únicos para el filtro
        $types = OrganizationalUnit::select('type')
            ->distinct()
            ->pluck('type');

        return view('organization::index', compact('units', 'types'));
    }

    /**
     * Show the form for creating a new organizational unit.
     */
    public function create()
    {
        $parentUnits = OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization::create', compact('parentUnits'));
    }

    /**
     * Store a newly created organizational unit.
     */
    public function store(StoreOrganizationalUnitRequest $request)
    {
        try {
            $data = $request->validated();

            // Calcular nivel y path
            if ($request->filled('parent_id')) {
                $parent = OrganizationalUnit::find($request->parent_id);
                $data['level'] = $parent->level + 1;
                $data['path'] = $parent->path . '/' . $data['code'];
            } else {
                $data['level'] = 0;
                $data['path'] = $data['code'];
            }

            $unit = OrganizationalUnit::create($data);

            // Actualizar closure table
            $this->hierarchyService->rebuildClosureTable();

            return redirect()
                ->route('organizational-units.show', $unit)
                ->with('success', 'Unidad organizacional creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear unidad organizacional: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified organizational unit.
     */
    public function show(OrganizationalUnit $organizationalUnit)
    {
        $organizationalUnit->load(['parent', 'children', 'ancestors', 'descendants']);

        return view('organization::show', compact('organizationalUnit'));
    }

    /**
     * Show the form for editing the specified organizational unit.
     */
    public function edit(OrganizationalUnit $organizationalUnit)
    {
        $parentUnits = OrganizationalUnit::where('is_active', true)
            ->where('id', '!=', $organizationalUnit->id)
            ->orderBy('name')
            ->get();

        return view('organization::edit', compact('organizationalUnit', 'parentUnits'));
    }

    /**
     * Update the specified organizational unit.
     */
    public function update(UpdateOrganizationalUnitRequest $request, OrganizationalUnit $organizationalUnit)
    {
        try {
            $data = $request->validated();

            // Verificar que no se establezca como su propio padre
            if ($request->filled('parent_id') && $request->parent_id === $organizationalUnit->id) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Una unidad no puede ser su propio padre.');
            }

            // Recalcular nivel y path si cambió el padre
            if ($request->filled('parent_id')) {
                $parent = OrganizationalUnit::find($request->parent_id);
                $data['level'] = $parent->level + 1;
                $data['path'] = $parent->path . '/' . $data['code'];
            } else {
                $data['level'] = 0;
                $data['path'] = $data['code'];
            }

            $organizationalUnit->update($data);

            // Actualizar closure table
            $this->hierarchyService->rebuildClosureTable();

            return redirect()
                ->route('organizational-units.show', $organizationalUnit)
                ->with('success', 'Unidad organizacional actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar unidad organizacional: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified organizational unit.
     */
    public function destroy(OrganizationalUnit $organizationalUnit)
    {
        try {
            if ($organizationalUnit->hasChildren()) {
                return redirect()
                    ->back()
                    ->with('error', 'No se puede eliminar una unidad organizacional con unidades hijas.');
            }

            $organizationalUnit->delete();

            // Actualizar closure table
            $this->hierarchyService->rebuildClosureTable();

            return redirect()
                ->route('organizational-units.index')
                ->with('success', 'Unidad organizacional eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar unidad organizacional: ' . $e->getMessage());
        }
    }

    /**
     * Display organizational tree view.
     */
    public function tree()
    {
        $rootUnits = OrganizationalUnit::root()
            ->with(['children'])
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return view('organization::tree', compact('rootUnits'));
    }
}
