<?php

namespace Modules\Organization\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganizationalUnitDeleted
{
    use Dispatchable, SerializesModels;

    public string $unitId;

    public function __construct(string $unitId)
    {
        $this->unitId = $unitId;
    }
}
