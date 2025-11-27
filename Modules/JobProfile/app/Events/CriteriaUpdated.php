<?php

namespace Modules\JobProfile\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\JobProfile\Entities\PositionCode;

class CriteriaUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PositionCode $positionCode,
        public string $phaseId,
        public array $criteria
    ) {}
}
