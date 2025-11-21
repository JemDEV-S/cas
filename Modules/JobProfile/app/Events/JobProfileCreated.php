<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\JobProfile;

class JobProfileCreated
{
    use Dispatchable, SerializesModels;

    public JobProfile $jobProfile;

    public function __construct(JobProfile $jobProfile)
    {
        $this->jobProfile = $jobProfile;
    }
}
