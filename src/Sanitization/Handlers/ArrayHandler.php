<?php

namespace Kettasoft\Filterable\Sanitization\Handlers;

use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;
use Kettasoft\Filterable\Sanitization\HandlerFactory;

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
      $value = HandlerFactory::handle($value, $sanitizer);
    }

    return $value;
  }
}
