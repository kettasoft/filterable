<?php

namespace Kettasoft\Filterable\Sanitization\Handlers;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class ObjectHandler implements SanitizeHandler
{
  protected Sanitizable $sanitizer;

  /**
   * ObjectHandler constructor.
   * @param mixed $sanitizer
   * @throws \InvalidArgumentException
   */
  public function __construct($sanitizer)
  {
    if (! ($sanitizer instanceof Sanitizable)) {
      throw new \InvalidArgumentException(sprintf("Sanitizer class %s is not implemented from %s interface", get_class($sanitizer), Sanitizable::class));
    }

    $this->sanitizer = $sanitizer;
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
