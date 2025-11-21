<?php

namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Entities\Role;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    public $user;
    public Role $role;

    public function __construct($user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}
