<?php

namespace Modules\User\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDeleted
{
    use Dispatchable, SerializesModels;

    public string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }
}
