<?php

namespace Modules\User\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Entities\User;
use Modules\User\Entities\UserOrganizationUnit;
use Modules\Organization\Entities\OrganizationalUnit;

/**
 * Evento: Cambio de unidad organizacional principal
 */
class UserOrganizationChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public ?OrganizationalUnit $oldUnit,
        public OrganizationalUnit $newUnit
    ) {}
}