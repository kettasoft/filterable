<?php

namespace Kettasoft\Filterable\Sanitization\Handlers;

use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class ClosureHandler implements SanitizeHandler
{
  protected \Closure $sanitizer;

  /**
   * ClosureHandler constructor.
   * @param mixed $sanitizer
   */
  public function __construct($sanitizer)
  {
    $this->sanitizer = $sanitizer;
  }

  /**
   * Handle incomming sanitizer.
   * @param mixed $value
   * @return mixed
   */
  public function handle(mixed $value): mixed
  {
    return $this->sanitizer->__invoke($value);
  }
}
