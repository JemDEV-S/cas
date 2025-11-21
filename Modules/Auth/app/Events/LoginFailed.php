<?php

namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginFailed
{
    use Dispatchable, SerializesModels;

    public $email;
    public $ipAddress;

    public function __construct(string $email, string $ipAddress)
    {
        $this->email = $email;
        $this->ipAddress = $ipAddress;
    }
}
