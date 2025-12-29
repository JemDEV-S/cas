<?php

declare(strict_types=1);

namespace Modules\JobPosting\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobPosting\Entities\JobPosting;

class JobPostingPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly JobPosting $jobPosting
    ) {}
}
