<?php

namespace Modules\Document\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Eventos de JobProfile
        \Modules\JobProfile\Events\JobProfileApproved::class => [
            \Modules\Document\Listeners\GenerateJobProfileDocument::class,
        ],
        \Modules\JobProfile\Events\JobProfileUpdated::class => [
            \Modules\Document\Listeners\RegenerateJobProfileDocument::class,
        ],

        // Eventos propios del módulo Document
        \Modules\Document\Events\DocumentGenerated::class => [
            // Agregar listeners si es necesario
        ],
        \Modules\Document\Events\DocumentReadyForSignature::class => [
            // Notificar al usuario que debe firmar
        ],
        \Modules\Document\Events\DocumentSigned::class => [
            // Notificar que el documento fue firmado
        ],
        \Modules\Document\Events\SignatureRejected::class => [
            // Notificar rechazo de firma
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
