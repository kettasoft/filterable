<?php

namespace Kettasoft\Filterable\Exceptions;

class InvalidOperatorException extends \InvalidArgumentException
{
  /**
   * InvalidOperatorException constructor.
   * @param string $operator
   */
  public function __construct(string $operator)
  {
    parent::__construct(sprintf("Operator [$operator] is invalid"));
  }
}
