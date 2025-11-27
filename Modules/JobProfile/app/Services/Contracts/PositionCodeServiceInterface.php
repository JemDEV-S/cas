<?php

namespace Modules\JobProfile\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\JobProfile\Entities\PositionCode;

interface PositionCodeServiceInterface
{
    public function getAll(): Collection;

    public function getActive(): Collection;

    public function findById(string $id): ?PositionCode;

    public function findByCode(string $code): ?PositionCode;

    public function create(array $data): PositionCode;

    public function update(string $id, array $data): PositionCode;

    public function delete(string $id): bool;

    public function activate(string $id): PositionCode;

    public function deactivate(string $id): PositionCode;

    public function validateCalculations(PositionCode $positionCode): bool;
}
