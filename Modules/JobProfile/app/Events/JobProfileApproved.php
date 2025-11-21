<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\JobProfile;

class JobProfileApproved
{
    use Dispatchable, SerializesModels;

    public JobProfile $jobProfile;
    public string $approvedBy;

    public function __construct(JobProfile $jobProfile, string $approvedBy)
    {
        $this->jobProfile = $jobProfile;
        $this->approvedBy = $approvedBy;
    }
}
