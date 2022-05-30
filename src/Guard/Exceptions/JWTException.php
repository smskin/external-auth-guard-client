<?php

namespace SMSkin\IdentityServiceClient\Guard\Exceptions;

use Exception;

class JWTException extends Exception
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'An error occurred';
}
