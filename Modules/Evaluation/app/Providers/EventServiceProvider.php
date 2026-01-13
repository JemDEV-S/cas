<?php

namespace Modules\Evaluation\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        'Modules\Evaluation\Events\EvaluationAssigned' => [
            'Modules\Evaluation\Listeners\NotifyEvaluatorAssigned',
        ],
        // 'Modules\Evaluation\Events\EvaluationSubmitted' => [
        //     'Modules\Evaluation\Listeners\NotifyEvaluationSubmitted',
        // ],
        'Modules\Evaluation\Events\EvaluationModified' => [
            'Modules\Evaluation\Listeners\LogEvaluationModified',
        ],
        'Modules\Evaluation\Events\EvaluationDeadlineApproaching' => [
            'Modules\Evaluation\Listeners\NotifyDeadlineApproaching',
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}