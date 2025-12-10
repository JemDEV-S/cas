<?php

namespace Modules\Application\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Application\Events\ApplicationSubmitted::class => [
            \Modules\Application\Listeners\LogApplicationSubmitted::class,
            \Modules\Application\Listeners\SendApplicationSubmittedNotification::class,
        ],
        \Modules\Application\Events\ApplicationUpdated::class => [
            \Modules\Application\Listeners\LogApplicationUpdated::class,
        ],
        \Modules\Application\Events\ApplicationEvaluated::class => [
            \Modules\Application\Listeners\LogApplicationEvaluated::class,
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
    protected function configureEmailVerification(): void {}
}
