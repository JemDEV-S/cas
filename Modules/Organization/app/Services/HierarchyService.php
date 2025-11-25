<?php

namespace Modules\Organization\Services;

use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Entities\OrganizationalUnitClosure;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HierarchyService
{
    /**
     * Obtener todos los ancestros de una unidad
     */
    public function getAncestors(string $unitId, bool $includeSelf = false): Collection
    {
        $query = OrganizationalUnitClosure::where('descendant_id', $unitId);

        if (!$includeSelf) {
            $query->where('depth', '>', 0);
        }

        return $query->with('ancestor')
            ->orderBy('depth', 'desc')
            ->get()
            ->pluck('ancestor');
    }

    /**
     * Obtener todos los descendientes de una unidad
     */
    public function getDescendants(string $unitId, bool $includeSelf = false): Collection
    {
        $query = OrganizationalUnitClosure::where('ancestor_id', $unitId);

        if (!$includeSelf) {
            $query->where('depth', '>', 0);
        }

        return $query->with('descendant')
            ->orderBy('depth', 'asc')
            ->get()
            ->pluck('descendant');
    }

    /**
     * Obtener hijos directos de una unidad
     */
    public function getChildren(string $unitId): Collection
    {
        return OrganizationalUnit::where('parent_id', $unitId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtener el padre de una unidad
     */
    public function getParent(string $unitId): ?OrganizationalUnit
    {
        $unit = OrganizationalUnit::find($unitId);
        return $unit?->parent;
    }

    /**
     * Obtener hermanos de una unidad (mismo nivel)
     */
    public function getSiblings(string $unitId, bool $includeSelf = false): Collection
    {
        $unit = OrganizationalUnit::find($unitId);

        if (!$unit) {
            return collect([]);
        }

        $query = OrganizationalUnit::where('parent_id', $unit->parent_id);

        if (!$includeSelf) {
            $query->where('id', '!=', $unitId);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Obtener la ruta completa de una unidad
     */
    public function getPath(string $unitId): Collection
    {
        return $this->getAncestors($unitId, true);
    }

    /**
     * Obtener el nivel de profundidad de una unidad
     */
    public function getLevel(string $unitId): int
    {
        return OrganizationalUnitClosure::where('descendant_id', $unitId)
            ->where('ancestor_id', '!=', $unitId)
            ->count();
    }

    /**
     * Mover una unidad a un nuevo padre
     */
    public function moveUnit(string $unitId, ?string $newParentId): bool
    {
        $unit = OrganizationalUnit::find($unitId);

        if (!$unit) {
            throw new BusinessRuleException('Unidad organizacional no encontrada');
        }

        // Validar que no se esté moviendo a sí misma
        if ($unitId === $newParentId) {
            throw new BusinessRuleException('Una unidad no puede ser padre de sí misma');
        }

        // Validar que no se esté moviendo a uno de sus descendientes
        if ($newParentId && $this->isDescendant($newParentId, $unitId)) {
            throw new BusinessRuleException('No se puede mover una unidad a uno de sus descendientes');
        }

        DB::beginTransaction();

        try {
            // Eliminar relaciones de closure existentes
            $this->deleteClosureRelations($unitId);

            // Actualizar el parent_id
            $unit->parent_id = $newParentId;
            $unit->level = $newParentId ? $this->getLevel($newParentId) + 1 : 0;
            $unit->save();

            // Reconstruir relaciones de closure
            $this->rebuildClosureForSubtree($unitId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verificar si una unidad es descendiente de otra
     */
    public function isDescendant(string $unitId, string $ancestorId): bool
    {
        return OrganizationalUnitClosure::where('ancestor_id', $ancestorId)
            ->where('descendant_id', $unitId)
            ->where('depth', '>', 0)
            ->exists();
    }

    /**
     * Verificar si una unidad es ancestro de otra
     */
    public function isAncestor(string $unitId, string $descendantId): bool
    {
        return $this->isDescendant($descendantId, $unitId);
    }

    /**
     * Obtener la raíz del árbol
     */
    public function getRoot(): ?OrganizationalUnit
    {
        return OrganizationalUnit::whereNull('parent_id')
            ->first();
    }

    /**
     * Obtener todas las raíces (si hay múltiples árboles)
     */
    public function getRoots(): Collection
    {
        return OrganizationalUnit::whereNull('parent_id')
            ->orderBy('order')
            ->get();
    }

    /**
     * Obtener la profundidad máxima del árbol
     */
    public function getMaxDepth(): int
    {
        return OrganizationalUnitClosure::max('depth') ?? 0;
    }

    /**
     * Contar descendientes de una unidad
     */
    public function countDescendants(string $unitId): int
    {
        return OrganizationalUnitClosure::where('ancestor_id', $unitId)
            ->where('depth', '>', 0)
            ->count();
    }

    /**
     * Eliminar relaciones de closure para una unidad y sus descendientes
     */
    protected function deleteClosureRelations(string $unitId): void
    {
        // Obtener todos los descendientes incluyendo la unidad misma
        $descendants = OrganizationalUnitClosure::where('ancestor_id', $unitId)
            ->pluck('descendant_id')
            ->toArray();

        // Eliminar todas las relaciones que involucran estos nodos
        OrganizationalUnitClosure::whereIn('descendant_id', $descendants)
            ->delete();
    }

    /**
     * Reconstruir las relaciones de closure para un subárbol
     */
    protected function rebuildClosureForSubtree(string $rootId): void
    {
        // Insertar autoreferencia
        OrganizationalUnitClosure::create([
            'ancestor_id' => $rootId,
            'descendant_id' => $rootId,
            'depth' => 0,
        ]);

        // Si tiene padre, copiar todas las relaciones del padre
        $unit = OrganizationalUnit::find($rootId);
        if ($unit->parent_id) {
            // Insertar relaciones con todos los ancestros del padre
            DB::statement("
                INSERT INTO organizational_unit_closure (ancestor_id, descendant_id, depth)
                SELECT ancestor_id, ?, depth + 1
                FROM organizational_unit_closure
                WHERE descendant_id = ?
            ", [$rootId, $unit->parent_id]);
        }

        // Procesar recursivamente todos los hijos
        $children = $this->getChildren($rootId);
        foreach ($children as $child) {
            $this->rebuildClosureForSubtree($child->id);
        }
    }

    /**
     * Reconstruir toda la tabla de closure (alias para rebuildAllClosures)
     */
    public function rebuildClosureTable(): void
    {
        $this->rebuildAllClosures();
    }

    /**
     * Reconstruir toda la tabla de closure
     */
    public function rebuildAllClosures(): void
    {
        DB::beginTransaction();

        try {
            // Limpiar tabla de closure
            OrganizationalUnitClosure::truncate();

            // Reconstruir desde las raíces
            $roots = $this->getRoots();
            foreach ($roots as $root) {
                $this->rebuildClosureForSubtree($root->id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de la jerarquía
     */
    public function getStatistics(): array
    {
        return [
            'total_units' => OrganizationalUnit::count(),
            'active_units' => OrganizationalUnit::where('is_active', true)->count(),
            'max_depth' => $this->getMaxDepth(),
            'roots_count' => $this->getRoots()->count(),
            'by_type' => OrganizationalUnit::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }
}
