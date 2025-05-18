<?php

namespace Kettasoft\Filterable\Exceptions;

class NotAllowedFieldException extends \InvalidArgumentException
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
