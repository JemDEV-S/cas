<?php

namespace Modules\JobProfile\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\JobProfile\Entities\PositionCode;

interface PositionCodeRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?PositionCode;

    public function findByCode(string $code): ?PositionCode;

    public function findActive(): Collection;

    public function create(array $data): PositionCode;

    public function update(string $id, array $data): PositionCode;

    public function delete(string $id): bool;

    public function activate(string $id): bool;

    public function deactivate(string $id): bool;
}
