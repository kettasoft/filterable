<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Casts the value to an integer.
 * Non-numeric values return 0, or null when $nullOnFail is true.
 *
 * Alias: 'integer'
 *
 * @example
 *   protected $sanitizers = ['page' => 'integer'];
 *   // "42abc" → 42  |  "xyz" → 0
 */
class IntegerSanitizer implements Sanitizable
{
  /**
   * Return null instead of 0 for non-numeric values.
   * @var bool
   */
  protected bool $nullOnFail;

  public function __construct(bool $nullOnFail = false)
  {
    $this->nullOnFail = $nullOnFail;
  }

  public function sanitize($value): mixed
  {
    if (is_array($value)) {
      return array_map(fn($v) => $this->cast($v), $value);
    }

    return $this->cast($value);
  }

  protected function cast(mixed $value): ?int
  {
    if (is_numeric($value)) {
      return (int) $value;
    }

    return $this->nullOnFail ? null : (int) $value;
  }
}
