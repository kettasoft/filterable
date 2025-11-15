<?php

namespace Kettasoft\Filterable\Exceptions;

class StrictnessException extends \RuntimeException
{
    /**
     * StrictnessException Constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
