<?php

namespace SMSkin\IdentityServiceClient\Guard\Jwt\Exceptions;

use Exception;

class JWTException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'An error occurred';
}
