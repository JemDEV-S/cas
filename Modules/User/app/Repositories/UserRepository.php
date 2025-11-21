<?php

namespace Modules\User\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\User\Entities\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByDNI(string $dni): ?User
    {
        return $this->model->where('dni', $dni)->first();
    }

    public function existsByEmail(string $email, ?string $exceptId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function existsByDNI(string $dni, ?string $exceptId = null): bool
    {
        $query = $this->model->where('dni', $dni);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function getActive()
    {
        return $this->model->where('is_active', true)->get();
    }

    public function searchUsers(string $search, int $perPage = 15)
    {
        return $this->model
            ->where(function ($q) use ($search) {
                $q->where('dni', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('first_name', 'ILIKE', "%{$search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$search}%");
            })
            ->paginate($perPage);
    }

    public function getUsersWithRoles()
    {
        return $this->model->with('roles')->get();
    }
}
