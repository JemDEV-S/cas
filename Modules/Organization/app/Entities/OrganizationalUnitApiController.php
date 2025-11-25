<?php

namespace Modules\Organization\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Http\Requests\StoreOrganizationalUnitRequest;
use Modules\Organization\Http\Requests\UpdateOrganizationalUnitRequest;
use Modules\Organization\Services\HierarchyService;
use Modules\Organization\Services\TreeService;
use Modules\Organization\Services\OrganizationalUnitService;

class OrganizationalUnitApiController extends Controller
{
    public function __construct(
        protected OrganizationalUnitService $service,
        protected HierarchyService $hierarchyService,
        protected TreeService $treeService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Listar todas las unidades organizacionales
     */
    public function index(Request $request): JsonResponse
    {
        $query = OrganizationalUnit::with(['parent']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $units = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    /**
     * Crear nueva unidad organizacional
     */
    public function store(StoreOrganizationalUnitRequest $request): JsonResponse
    {
        try {
            $unit = $this->service->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unidad organizacional creada exitosamente',
                'data' => $unit->load(['parent', 'children']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear unidad: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mostrar unidad específica
     */
    public function show(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        $organizationalUnit->load(['parent', 'children', 'ancestors', 'descendants']);

        return response()->json([
            'success' => true,
            'data' => $organizationalUnit,
        ]);
    }

    /**
     * Actualizar unidad organizacional
     */
    public function update(
        UpdateOrganizationalUnitRequest $request,
        OrganizationalUnit $organizationalUnit
    ): JsonResponse {
        try {
            $unit = $this->service->update($organizationalUnit->id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Unidad organizacional actualizada exitosamente',
                'data' => $unit->load(['parent', 'children']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar unidad: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Eliminar unidad organizacional
     */
    public function destroy(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        try {
            if ($organizationalUnit->hasChildren()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una unidad con sub-unidades',
                ], 422);
            }

            $this->service->delete($organizationalUnit->id);

            return response()->json([
                'success' => true,
                'message' => 'Unidad organizacional eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar unidad: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener árbol jerárquico completo
     */
    public function tree(Request $request): JsonResponse
    {
        $rootId = $request->get('root_id');
        $maxDepth = $request->get('max_depth');

        $tree = $this->treeService->getTree($rootId, $maxDepth);

        return response()->json([
            'success' => true,
            'data' => $tree,
        ]);
    }

    /**
     * Obtener árbol aplanado
     */
    public function flatTree(Request $request): JsonResponse
    {
        $rootId = $request->get('root_id');
        $tree = $this->treeService->getFlatTree($rootId);

        return response()->json([
            'success' => true,
            'data' => $tree,
        ]);
    }

    /**
     * Obtener ancestros de una unidad
     */
    public function ancestors(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        $ancestors = $this->hierarchyService->getAncestors(
            $organizationalUnit->id,
            includeSelf: true
        );

        return response()->json([
            'success' => true,
            'data' => $ancestors,
        ]);
    }

    /**
     * Obtener descendientes de una unidad
     */
    public function descendants(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        $descendants = $this->hierarchyService->getDescendants(
            $organizationalUnit->id,
            includeSelf: false
        );

        return response()->json([
            'success' => true,
            'data' => $descendants,
        ]);
    }

    /**
     * Obtener hijos directos de una unidad
     */
    public function children(OrganizationalUnit $organizationalUnit): JsonResponse
    {
        $children = $this->hierarchyService->getChildren($organizationalUnit->id);

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    /**
     * Obtener hermanos de una unidad
     */
    public function siblings(OrganizationalUnit $organizationalUnit, Request $request): JsonResponse
    {
        $includeSelf = $request->boolean('include_self', false);
        $siblings = $this->hierarchyService->getSiblings($organizationalUnit->id, $includeSelf);

        return response()->json([
            'success' => true,
            'data' => $siblings,
        ]);
    }

    /**
     * Mover unidad a nuevo padre
     */
    public function move(Request $request, OrganizationalUnit $organizationalUnit): JsonResponse
    {
        $request->validate([
            'new_parent_id' => 'nullable|uuid|exists:organizational_units,id',
        ]);

        try {
            $this->hierarchyService->moveUnit(
                $organizationalUnit->id,
                $request->new_parent_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Unidad movida exitosamente',
                'data' => $organizationalUnit->fresh()->load(['parent', 'children']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener opciones para select
     */
    public function selectOptions(Request $request): JsonResponse
    {
        $excludeId = $request->get('exclude_id');
        $onlyActive = $request->boolean('only_active', true);

        $options = $this->treeService->getSelectOptions($excludeId, $onlyActive);

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Buscar en unidades organizacionales
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'fields' => 'array',
        ]);

        $query = $request->get('query');
        $fields = $request->get('fields', ['name', 'code', 'description']);

        $results = $this->treeService->search($query, $fields);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Obtener estadísticas de la jerarquía
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->hierarchyService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Validar estructura del árbol
     */
    public function validate(): JsonResponse
    {
        $errors = $this->treeService->validateTree();

        return response()->json([
            'success' => empty($errors),
            'data' => [
                'is_valid' => empty($errors),
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Exportar árbol en diferentes formatos
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:json,xml,yaml',
            'root_id' => 'nullable|uuid|exists:organizational_units,id',
        ]);

        $format = $request->get('format', 'json');
        $rootId = $request->get('root_id');

        $export = $this->treeService->export($format, $rootId);

        $contentType = match($format) {
            'json' => 'application/json',
            'xml' => 'application/xml',
            'yaml' => 'application/x-yaml',
        };

        return response($export, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=organizational_units.{$format}");
    }

    /**
     * Reconstruir closure table
     */
    public function rebuildClosure(): JsonResponse
    {
        try {
            $this->hierarchyService->rebuildClosureTable();

            return response()->json([
                'success' => true,
                'message' => 'Closure table reconstruida exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reconstruir: ' . $e->getMessage(),
            ], 500);
        }
    }
}
