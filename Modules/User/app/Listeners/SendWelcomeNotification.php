<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserCreated;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification
{
    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        // TODO: Implementar envío de email de bienvenida cuando esté el módulo Notification
        // Por ahora solo loguear
        Log::info('Usuario creado - Email de bienvenida pendiente', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->first_name . ' ' . $event->user->last_name,
        ]);

        // Cuando se implemente el módulo Notification:
        // Notification::send($event->user, new WelcomeNotification());
    }
}
