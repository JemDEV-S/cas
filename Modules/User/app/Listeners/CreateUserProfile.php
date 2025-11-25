<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserCreated;
use Modules\User\Entities\UserProfile;

class CreateUserProfile
{
    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        // Crear perfil automáticamente cuando se crea un usuario
        UserProfile::create([
            'user_id' => $event->user->id,
            'metadata' => [],
        ]);
    }
}
