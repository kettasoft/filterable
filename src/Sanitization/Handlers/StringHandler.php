<?php

namespace Kettasoft\Filterable\Sanitization\Handlers;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class StringHandler implements SanitizeHandler
{
  protected Sanitizable $sanitizer;

  public function __construct($sanitizer)
  {
    if (! is_a($sanitizer, Sanitizable::class, true)) {
      throw new \InvalidArgumentException(sprintf("Sanitizer class %s is invalid", $sanitizer));
    }

    $this->sanitizer = new $sanitizer;
  }

  /**
   * Handle incomming sanitizer.
   * @param mixed $value
   * @return mixed
   */
  public function handle(mixed $value): mixed
  {
    return $this->sanitizer->sanitize($value);
  }
}
