<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class InvalidOperatorValueException extends SkipExecution
{
  public function __construct(Payload $payload, string $message)
  {
    parent::__construct($message, $payload);
  }
}
