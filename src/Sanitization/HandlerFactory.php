<?php

namespace Kettasoft\Filterable\Sanitization;

use Kettasoft\Filterable\Sanitization\Handlers\ArrayHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ObjectHandler;
use Kettasoft\Filterable\Sanitization\Handlers\StringHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ClosureHandler;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class HandlerFactory
{
  /**
   * Handle sanitize value by sanitizer handlers.
   * @param mixed $value
   * @param mixed $sanitizer
   */
  public static function handle($value, $sanitizer)
  {
    return static::makeHandler($sanitizer)->handle($value);
  }

  /**
   * Create SanitizerHandler instance based on sanitizer type.
   * @param mixed $sanitizer
   * @throws \RuntimeException
   * @return SanitizeHandler
   */
  protected static function makeHandler($sanitizer): SanitizeHandler
  {
    $handler = match (true) {
      is_string($sanitizer) => new StringHandler($sanitizer),
      is_callable($sanitizer) => new ClosureHandler($sanitizer),
      is_array($sanitizer) => new ArrayHandler($sanitizer),
      is_object($sanitizer) => new ObjectHandler($sanitizer),
      default => throw new \RuntimeException("Handler is not processable"),
    };

    return $handler;
  }
}
