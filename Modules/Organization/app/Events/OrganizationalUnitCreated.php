<?php

namespace Modules\Organization\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Organization\Entities\OrganizationalUnit;

class OrganizationalUnitCreated
{
    use Dispatchable, SerializesModels;

    public OrganizationalUnit $unit;

    public function __construct(OrganizationalUnit $unit)
    {
        $this->unit = $unit;
    }
}
