<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Converts truthy/falsy string and integer representations to native PHP booleans.
 *
 * Truthy:  "true", "1", "yes", "on"  → true
 * Falsy:   "false", "0", "no", "off", ""  → false
 *
 * Alias: 'boolean'
 *
 * @example
 *   protected $sanitizers = ['is_active' => 'boolean'];
 *   // "yes" → true  |  "off" → false
 */
class BooleanSanitizer implements Sanitizable
{
  /**
   * String values treated as true.
   * @var array<string>
   */
  protected array $truthy = ['true', '1', 'yes', 'on'];

  /**
   * String values treated as false.
   * @var array<string>
   */
  protected array $falsy = ['false', '0', 'no', 'off', ''];

  public function sanitize($value): mixed
  {
    if (is_bool($value)) {
      return $value;
    }

    if (is_string($value)) {
      $lower = mb_strtolower(trim($value));

      if (in_array($lower, $this->truthy, true)) return true;
      if (in_array($lower, $this->falsy, true))  return false;
    }

    if (is_int($value)) {
      return $value !== 0;
    }

    return (bool) $value;
  }
}
