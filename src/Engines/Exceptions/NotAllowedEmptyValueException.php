<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class NotAllowedEmptyValueException extends SkipExecution
{
    /**
     * NotAllowedEmptyValueException constructor.
     * @param mixed $message
     */
    public function __construct($message = "", ?Payload $payload = null)
    {
        parent::__construct($message, $payload);
    }
}
