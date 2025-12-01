<?php

namespace Modules\User\Repositories\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface para UserOrganizationUnit Repository
 */
interface UserOrganizationUnitRepositoryInterface
{
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id): bool;
    public function find(string $id);
    public function getUserActiveAssignments(string $userId): Collection;
    public function getUserAssignments(string $userId): Collection;
    public function getPrimaryAssignment(string $userId);
    public function getUserPrimaryAssignments(string $userId): Collection;
    public function getUnitUsers(string $unitId, bool $onlyActive = true): Collection;
    public function isUserAssignedToUnit(string $userId, string $unitId): bool;
    public function getExpiredAssignments(): Collection;
}

// ==========================================

