<?php

namespace SMSkin\IdentityServiceClient\Guards\Jwt\Exceptions;

use Exception;

class JWTException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'An error occurred';
}
