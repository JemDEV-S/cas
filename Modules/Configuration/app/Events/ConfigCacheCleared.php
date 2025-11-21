<?php

namespace Modules\Configuration\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigCacheCleared
{
    use Dispatchable, SerializesModels;

    public function __construct()
    {
    }
}
