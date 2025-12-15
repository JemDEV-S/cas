<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\JobProfile;

class JobProfileUpdated
{
    use Dispatchable, SerializesModels;

    public JobProfile $jobProfile;
    public string $updatedBy;

    public function __construct(JobProfile $jobProfile, string $updatedBy)
    {
        $this->jobProfile = $jobProfile;
        $this->updatedBy = $updatedBy;
    }
}
