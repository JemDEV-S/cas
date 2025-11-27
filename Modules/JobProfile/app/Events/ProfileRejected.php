<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\JobProfile;

class ProfileRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public JobProfile $jobProfile,
        public string $reviewedBy,
        public ?string $reason = null
    ) {}
}
