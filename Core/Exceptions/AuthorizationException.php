<?php

namespace App\Core\Exceptions;

/**
 * AuthorizationException
 * 
 * Thrown when a user is not authorized to perform an action
 */
class AuthorizationException extends \Exception
{
    protected $code = 403;

    public function __construct(string $message = "This action is unauthorized.", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
