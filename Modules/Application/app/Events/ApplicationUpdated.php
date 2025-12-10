<?php

namespace Modules\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Application\Entities\Application;

class ApplicationUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Application $application
    ) {}
}
