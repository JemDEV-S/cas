<?php

namespace Modules\Configuration\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Configuration\Entities\SystemConfig;

class CriticalConfigChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SystemConfig $config,
        public ?string $changedBy = null
    ) {
    }
}
