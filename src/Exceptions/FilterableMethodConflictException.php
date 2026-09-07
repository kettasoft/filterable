<?php

namespace Kettasoft\Filterable\Exceptions;

class FilterableMethodConflictException extends StrictnessException
{
  /**
   * Create a new exception instance.
   *
   * @param string $method The conflicting method name.
   */
  public function __construct(string $method)
  {
    parent::__construct(sprintf("Filter method [%s] conflicts with core Filterable method.", $method));
  }
}
