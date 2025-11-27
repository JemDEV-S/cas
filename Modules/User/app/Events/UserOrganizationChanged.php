<?php

namespace Modules\User\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Entities\User;
use Modules\User\Entities\UserOrganizationUnit;
use Modules\Organization\Entities\OrganizationalUnit;

/**
 * Evento: Usuario desasignado de unidad organizacional
 */
class UserOrganizationUnassigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public OrganizationalUnit $organizationalUnit,
        public UserOrganizationUnit $assignment,
        public ?string $reason = null
    ) {}
}
