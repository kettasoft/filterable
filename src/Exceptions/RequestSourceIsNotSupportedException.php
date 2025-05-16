<?php

namespace Kettasoft\Filterable\Exceptions;

class RequestSourceIsNotSupportedException extends \InvalidArgumentException
{
  /**
   * RequestSourceIsNotDefineException constructor.
   * @param string $message
   */
  public function __construct(string $source)
  {
    parent::__construct(sprintf(
      "The request source (%s) is not supported, Allowed: query, input, json",
      $source
    ));
  }
}
