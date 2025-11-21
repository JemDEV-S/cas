<?php

namespace Modules\Organization\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Organization\Entities\OrganizationalUnit;

class OrganizationalUnitRepository extends BaseRepository
{
    public function __construct(OrganizationalUnit $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?OrganizationalUnit
    {
        return $this->model->where('code', $code)->first();
    }

    public function existsByCode(string $code, ?string $exceptId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function getRootUnits()
    {
        return $this->model->root()->orderBy('order')->get();
    }

    public function getByType(string $type)
    {
        return $this->model->byType($type)->orderBy('order')->get();
    }

    public function getByLevel(int $level)
    {
        return $this->model->byLevel($level)->orderBy('order')->get();
    }

    public function getTree()
    {
        return $this->model->root()
            ->with(['children' => function ($query) {
                $query->orderBy('order')->with('children');
            }])
            ->orderBy('order')
            ->get();
    }

    public function getActive()
    {
        return $this->model->where('is_active', true)->orderBy('order')->get();
    }
}
