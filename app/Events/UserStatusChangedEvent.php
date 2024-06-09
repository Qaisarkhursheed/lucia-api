<?php

namespace App\Events;

use App\ModelsExtended\User;

class UserStatusChangedEvent extends Event
{
    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
