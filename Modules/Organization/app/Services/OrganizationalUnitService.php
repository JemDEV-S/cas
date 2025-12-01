<?php

namespace Modules\Organization\Services;

use Modules\Core\Services\BaseService;
use Modules\Organization\Entities\OrganizationalUnit;
use Modules\Organization\Repositories\OrganizationalUnitRepository;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;

class OrganizationalUnitService extends BaseService
{
    public function __construct(OrganizationalUnitRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): OrganizationalUnit
    {
        if ($this->repository->existsByCode($data['code'])) {
            throw new BusinessRuleException('El código ya está en uso.');
        }

        return $this->transaction(function () use ($data) {
            // Calcular nivel y path
            if (isset($data['parent_id'])) {
                $parent = $this->repository->findOrFail($data['parent_id']);
                $data['level'] = $parent->level + 1;
                $data['path'] = $parent->path . '/' . $parent->id;
            } else {
                $data['level'] = 1;
                $data['path'] = '';
            }

            $unit = $this->repository->create($data);

            // Actualizar Closure Table
            $this->updateClosureTable($unit);

            return $unit->fresh();
        });
    }

    public function update(string $id, array $data): OrganizationalUnit
    {
        if (isset($data['code']) && $this->repository->existsByCode($data['code'], $id)) {
            throw new BusinessRuleException('El código ya está en uso.');
        }

        $unit = $this->repository->findOrFail($id);

        // No permitir cambiar parent_id de unidades con hijos
        if (isset($data['parent_id']) && $data['parent_id'] !== $unit->parent_id) {
            if ($unit->hasChildren()) {
                throw new BusinessRuleException('No se puede mover una unidad que tiene sub-unidades.');
            }
        }

        return $this->transaction(function () use ($id, $data, $unit) {
            // Si cambia el padre, recalcular nivel y path
            if (isset($data['parent_id']) && $data['parent_id'] !== $unit->parent_id) {
                if ($data['parent_id']) {
                    $parent = $this->repository->findOrFail($data['parent_id']);
                    $data['level'] = $parent->level + 1;
                    $data['path'] = $parent->path . '/' . $parent->id;
                } else {
                    $data['level'] = 1;
                    $data['path'] = '';
                }

                // Limpiar y reconstruir closure table
                $this->clearClosureTable($unit);
            }

            $this->repository->update($id, $data);
            $unit = $this->repository->findOrFail($id);

            // Actualizar Closure Table si cambió el padre
            if (isset($data['parent_id']) && $data['parent_id'] !== $unit->parent_id) {
                $this->updateClosureTable($unit);
            }

            return $unit;
        });
    }

    public function delete(string $id): void
    {
        $unit = $this->repository->findOrFail($id);

        if ($unit->hasChildren()) {
            throw new BusinessRuleException('No se puede eliminar una unidad que tiene sub-unidades.');
        }

        $this->transaction(function () use ($unit) {
            $this->clearClosureTable($unit);
            $this->repository->delete($unit->id);
        });
    }

    public function getTree()
    {
        return $this->repository->getTree();
    }

    public function getDescendants(string $id)
    {
        $unit = $this->repository->findOrFail($id);
        return $unit->descendants()->get();
    }

    public function getAncestors(string $id)
    {
        $unit = $this->repository->findOrFail($id);
        return $unit->getAllAncestors();
    }

    /**
     * Actualiza la Closure Table para una unidad
     */
    protected function updateClosureTable(OrganizationalUnit $unit): void
    {
        // Insertar relación consigo misma (depth = 0)
        DB::table('organizational_unit_closure')->insert([
            'ancestor_id' => $unit->id,
            'descendant_id' => $unit->id,
            'depth' => 0,
        ]);

        // Insertar relaciones con ancestros
        if ($unit->parent_id) {
            $ancestorRelations = DB::table('organizational_unit_closure')
                ->where('descendant_id', $unit->parent_id)
                ->get();

            foreach ($ancestorRelations as $relation) {
                DB::table('organizational_unit_closure')->insert([
                    'ancestor_id' => $relation->ancestor_id,
                    'descendant_id' => $unit->id,
                    'depth' => $relation->depth + 1,
                ]);
            }
        }
    }

    /**
     * Limpia la Closure Table para una unidad
     */
    protected function clearClosureTable(OrganizationalUnit $unit): void
    {
        DB::table('organizational_unit_closure')
            ->where('descendant_id', $unit->id)
            ->where('ancestor_id', '!=', $unit->id)
            ->delete();
    }

    public function getActiveUnits()
    {
        return $this->repository->getActive();
    }

}
