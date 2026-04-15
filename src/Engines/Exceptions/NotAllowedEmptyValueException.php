<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class NotAllowedEmptyValueException extends SkipExecution
{
    /**
     * NotAllowedEmptyValueException constructor.
     * @param string $message
     * @param Payload|null $payload
     */
    public function __construct(string $message = "", ?Payload $payload = null)
    {
        parent::__construct($message, $payload);
    }
}
