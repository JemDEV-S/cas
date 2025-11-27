<?php

namespace Modules\JobProfile\Policies;

use Modules\User\Entities\User;
use Modules\JobProfile\Entities\PositionCode;

class PositionCodePolicy
{
    /**
     * Determine if the user can view any position codes.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('positioncode.view.any');
    }

    /**
     * Determine if the user can view the position code.
     */
    public function view(User $user, PositionCode $positionCode): bool
    {
        return $user->can('positioncode.view');
    }

    /**
     * Determine if the user can create position codes.
     */
    public function create(User $user): bool
    {
        return $user->can('positioncode.create');
    }

    /**
     * Determine if the user can update the position code.
     */
    public function update(User $user, PositionCode $positionCode): bool
    {
        return $user->can('positioncode.update');
    }

    /**
     * Determine if the user can delete the position code.
     */
    public function delete(User $user, PositionCode $positionCode): bool
    {
        return $user->can('positioncode.delete');
    }

    /**
     * Determine if the user can activate/deactivate position codes.
     */
    public function toggleStatus(User $user, PositionCode $positionCode): bool
    {
        return $user->can('positioncode.toggle-status');
    }
}
