<?php

namespace Modules\JobPosting\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Evento cuando se solicita publicación de convocatoria
        \Modules\JobPosting\Events\JobPostingPublicationRequested::class => [],

        // Cuando la convocatoria se publica finalmente (después de firmas)
        \Modules\JobPosting\Events\JobPostingPublished::class => [
            \Modules\JobProfile\Listeners\ActivateJobProfiles::class,
        ],

        // Cuando un documento se firma completamente
        \Modules\Document\Events\DocumentFullySigned::class => [
            \Modules\JobPosting\Listeners\PublishJobPostingAfterSignatures::class,
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
