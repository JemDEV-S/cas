<?php

namespace Modules\Application\Listeners;

use Modules\Application\Events\ApplicationSubmitted;
use Modules\Application\Entities\ApplicationHistory;

class LogApplicationSubmitted
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationSubmitted $event): void
    {
        ApplicationHistory::log(
            applicationId: $event->application->id,
            eventType: 'CREATED',
            description: 'Postulación creada con código: ' . $event->application->code
        );
    }
}
