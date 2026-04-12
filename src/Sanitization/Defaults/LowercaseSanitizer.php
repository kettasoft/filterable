<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Converts string values to lowercase using multibyte-safe function.
 *
 * Alias: 'lowercase'
 *
 * @example
 *   protected $sanitizers = ['email' => 'lowercase'];
 *   // "Hello@Example.COM" → "hello@example.com"
 */
class LowercaseSanitizer implements Sanitizable
{
  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return mb_strtolower($value);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? mb_strtolower($v) : $v,
        $value
      );
    }

    return $value;
  }
}
