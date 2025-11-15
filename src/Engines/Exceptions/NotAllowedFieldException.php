<?php

namespace Kettasoft\Filterable\Engines\Exceptions;

class NotAllowedFieldException extends SkipExecution
{
  /**
   * NotAllowedFieldException constructor.
   * @param string $field
   */
  public function __construct(string $field)
  {
    parent::__construct(sprintf("Field [$field] is not allowed."));
  }
}
