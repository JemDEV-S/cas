<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\JobProfile;

class ProfileInReview
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public JobProfile $jobProfile,
        public ?string $submittedBy = null
    ) {}
}
