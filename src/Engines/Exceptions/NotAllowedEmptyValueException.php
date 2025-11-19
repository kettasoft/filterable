<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

class NotAllowedEmptyValueException extends SkipExecution
{
    /**
     * NotAllowedEmptyValueException constructor.
     * @param mixed $message
     */
    public function __construct($message = "")
    {
        parent::__construct($message);
    }
}
