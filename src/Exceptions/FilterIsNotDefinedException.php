<?php

namespace Kettasoft\Filterable\Exceptions;

class FilterIsNotDefinedException extends \Exception
{
  /**
   * Create FilterIsNotDefinedException instance.
   * @param mixed $filter
   */
  public function __construct($filter)
  {
    parent::__construct(sprintf(
      "Filter (%s) is not defined.",
      $filter
    ));
  }
}
