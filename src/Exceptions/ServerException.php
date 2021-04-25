<?php

namespace App\Exceptions;

use Throwable;

class ServerException extends \Exception
{
    public function __construct($message = "", $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}