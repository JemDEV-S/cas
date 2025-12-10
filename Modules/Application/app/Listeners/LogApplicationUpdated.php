<?php

namespace Modules\Application\Listeners;

use Modules\Application\Events\ApplicationUpdated;
use Modules\Application\Entities\ApplicationHistory;

class LogApplicationUpdated
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationUpdated $event): void
    {
        ApplicationHistory::log(
            applicationId: $event->application->id,
            eventType: 'UPDATED',
            description: 'Postulación actualizada'
        );
    }
}
