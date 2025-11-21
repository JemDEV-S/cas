<?php

namespace Modules\User\Services;

use Modules\Core\Services\BaseService;
use Modules\User\Entities\UserPreference;

class PreferenceService extends BaseService
{
    public function updatePreferences(string $userId, array $data): UserPreference
    {
        $preference = UserPreference::where('user_id', $userId)->firstOrFail();
        $preference->update($data);

        return $preference->fresh();
    }

    public function getPreferences(string $userId): ?UserPreference
    {
        return UserPreference::where('user_id', $userId)->first();
    }

    public function setPreference(string $userId, string $key, $value): void
    {
        $preference = UserPreference::where('user_id', $userId)->firstOrFail();
        $preference->setPreference($key, $value);
    }

    public function getPreference(string $userId, string $key, $default = null)
    {
        $preference = UserPreference::where('user_id', $userId)->first();

        if (!$preference) {
            return $default;
        }

        return $preference->getPreference($key, $default);
    }
}
