<?php

namespace Modules\JobProfile\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\JobProfile\Entities\PositionCode;
use Modules\JobProfile\Repositories\Contracts\PositionCodeRepositoryInterface;

class PositionCodeRepository implements PositionCodeRepositoryInterface
{
    public function __construct(
        protected PositionCode $model
    ) {}

    public function all(): Collection
    {
        return $this->model->orderBy('code')->get();
    }

    public function findById(string $id): ?PositionCode
    {
        return $this->model->find($id);
    }

    public function findByCode(string $code): ?PositionCode
    {
        return $this->model->where('code', $code)->first();
    }

    public function findActive(): Collection
    {
        return $this->model->active()->orderBy('code')->get();
    }

    public function create(array $data): PositionCode
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): PositionCode
    {
        $positionCode = $this->model->findOrFail($id);
        $positionCode->update($data);
        return $positionCode->fresh();
    }

    public function delete(string $id): bool
    {
        $positionCode = $this->model->findOrFail($id);
        return $positionCode->delete();
    }

    public function activate(string $id): bool
    {
        $positionCode = $this->model->findOrFail($id);
        return $positionCode->activate();
    }

    public function deactivate(string $id): bool
    {
        $positionCode = $this->model->findOrFail($id);
        return $positionCode->deactivate();
    }
}
