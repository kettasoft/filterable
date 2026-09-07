<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

use Kettasoft\Filterable\Support\Payload;

class NotAllowedFieldException extends SkipExecution
{
  /**
   * NotAllowedFieldException constructor.
   * @param string $field
   */
  public function __construct(string $field, ?Payload $payload = null)
  {
    parent::__construct(sprintf("Field [$field] is not allowed."), $payload);
  }
}
