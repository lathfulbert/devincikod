<?php

namespace Modules\Auth\Events;

use App\Core\Events\Event;

/**
 * User Registered Event
 * 
 * Dispatched when a new user successfully registers.
 */
class UserRegistered extends Event
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;

        parent::__construct([
            'user' => $user,
            'timestamp' => time(),
        ]);
    }

    /**
     * Get the user that was registered
     */
    public function getUser()
    {
        return $this->user;
    }
}
