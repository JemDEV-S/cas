<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserCreated;
use Modules\User\Entities\UserPreference;

class CreateUserPreferences
{
    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        // Crear preferencias con valores por defecto
        UserPreference::create([
            'user_id' => $event->user->id,
            'language' => 'es',
            'timezone' => 'America/Lima',
            'notifications_email' => true,
            'notifications_system' => true,
            'theme' => 'light',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'preferences' => [],
        ]);
    }
}
