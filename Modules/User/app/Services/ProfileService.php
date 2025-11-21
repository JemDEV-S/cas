<?php

namespace Modules\User\Services;

use Modules\Core\Services\BaseService;
use Modules\User\Entities\UserProfile;

class ProfileService extends BaseService
{
    public function updateProfile(string $userId, array $data): UserProfile
    {
        $profile = UserProfile::where('user_id', $userId)->firstOrFail();
        $profile->update($data);

        return $profile->fresh();
    }

    public function getProfile(string $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }
}
