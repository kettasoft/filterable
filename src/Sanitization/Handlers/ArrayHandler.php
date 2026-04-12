<?php

namespace Kettasoft\Filterable\Sanitization\Handlers;

use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class ArrayHandler implements SanitizeHandler
{
  protected array $sanitizers;

  /**
   * ArrayHandler constructor
   * @param mixed $sanitizers
   */
  public function __construct($sanitizers)
  {
    $this->sanitizers = $sanitizers;
  }

  /**
   * Handle incomming sanitizer.
   * @param mixed $value
   * @return mixed
   */
  public function handle(mixed $value): mixed
  {
    foreach ($this->sanitizers as $sanitizer) {
      $value = Sanitizer::apply($value, $sanitizer);
    }

    return $value;
  }
}
