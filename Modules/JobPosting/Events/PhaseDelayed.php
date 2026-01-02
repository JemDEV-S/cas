<?php

namespace Modules\JobPosting\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobPosting\Entities\JobPostingSchedule;

class PhaseDelayed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JobPostingSchedule $schedule
    ) {}
}
