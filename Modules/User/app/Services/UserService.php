<?php

namespace Modules\User\Services;

use Modules\Core\Services\BaseService;
use Modules\User\Entities\User;
use Modules\User\Repositories\UserRepository;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\Core\Exceptions\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UserService extends BaseService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): User
    {
        $this->validateUserData($data);

        if ($this->repository->existsByEmail($data['email'])) {
            throw new BusinessRuleException('El email ya está registrado.');
        }

        if ($this->repository->existsByDNI($data['dni'])) {
            throw new BusinessRuleException('El DNI ya está registrado.');
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->transaction(function () use ($data) {
            $user = $this->repository->create($data);

            // Crear perfil y preferencias por defecto
            $user->profile()->create([]);
            $user->preference()->create([]);

            return $user->fresh(['profile', 'preference']);
        });
    }

    public function update(string $id, array $data): User
    {
        $user = $this->repository->findOrFail($id);

        if (isset($data['email']) && $this->repository->existsByEmail($data['email'], $id)) {
            throw new BusinessRuleException('El email ya está registrado.');
        }

        if (isset($data['dni']) && $this->repository->existsByDNI($data['dni'], $id)) {
            throw new BusinessRuleException('El DNI ya está registrado.');
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $this->repository->update($id, $data);

        return $this->repository->findOrFail($id);
    }

    public function delete(string $id): void
    {
        $user = $this->repository->findOrFail($id);
        $this->repository->delete($id);
    }

    public function activate(string $id): User
    {
        $user = $this->repository->findOrFail($id);
        $user->is_active = true;
        $user->save();

        return $user;
    }

    public function deactivate(string $id): User
    {
        $user = $this->repository->findOrFail($id);
        $user->is_active = false;
        $user->save();

        return $user;
    }

    public function assignRoles(string $userId, array $roleIds): void
    {
        $user = $this->repository->findOrFail($userId);
        $user->syncRoles($roleIds);
    }

    private function validateUserData(array $data): void
    {
        $errors = [];

        if (isset($data['dni']) && !preg_match('/^\d{8}$/', $data['dni'])) {
            $errors['dni'] = ['El DNI debe tener 8 dígitos.'];
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['El email no es válido.'];
        }

        if (!empty($errors)) {
            throw new ValidationException('Datos de usuario inválidos', $errors);
        }
    }

    public function juryUsersQuery():Builder
    {
        return User::whereHas('roles', function ($query){
            $query->where('slug', 'jury');
        });
    }
}
