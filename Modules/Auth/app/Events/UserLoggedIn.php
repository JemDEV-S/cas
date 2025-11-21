<?php

namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public $user;
    public $ipAddress;

    public function __construct($user, string $ipAddress)
    {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
    }
}
