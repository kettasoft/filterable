<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Converts string values to uppercase using multibyte-safe function.
 *
 * Alias: 'uppercase'
 *
 * @example
 *   protected $sanitizers = ['code' => 'uppercase'];
 *   // "active" → "ACTIVE"
 */
class UppercaseSanitizer implements Sanitizable
{
  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return mb_strtoupper($value);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? mb_strtoupper($v) : $v,
        $value
      );
    }

    return $value;
  }
}
