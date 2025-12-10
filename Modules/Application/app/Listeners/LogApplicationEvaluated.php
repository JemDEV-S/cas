<?php

namespace Modules\Application\Listeners;

use Modules\Application\Events\ApplicationEvaluated;
use Modules\Application\Entities\ApplicationHistory;

class LogApplicationEvaluated
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationEvaluated $event): void
    {
        $application = $event->application;

        ApplicationHistory::log(
            applicationId: $application->id,
            eventType: 'ELIGIBILITY_CHECKED',
            data: [
                'old_status' => $application->getOriginal('status'),
                'new_status' => $application->status,
            ],
            description: $application->is_eligible
                ? 'Evaluado como APTO'
                : 'Evaluado como NO APTO: ' . $application->ineligibility_reason
        );
    }
}
