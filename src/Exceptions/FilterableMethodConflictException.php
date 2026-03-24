<?php

namespace Kettasoft\Filterable\Exceptions;

class FilterableMethodConflictException extends \RuntimeException
{
  /**
   * Create a new exception instance.
   *
   * @param string $method The conflicting method name.
   */
  public function __construct(string $method)
  {
    $message = sprintf("Filter method [%s] conflicts with core Filterable method.", $method);
    parent::__construct($message);
  }
}
