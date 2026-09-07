<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class InvalidOperatorException extends SkipExecution
{
  /**
   * InvalidOperatorException constructor.
   * @param string $operator
   */
  public function __construct(string $operator, ?Payload $payload = null)
  {
    parent::__construct(sprintf("Operator [$operator] is invalid"), $payload);
  }
}
