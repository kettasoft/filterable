<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Casts the value to a float.
 * Optionally rounds to a given number of decimal places.
 *
 * Alias: 'float'
 *
 * @example
 *   protected $sanitizers = ['price' => FloatSanitizer::class];
 *   // "19.999abc" → 19.999  |  new FloatSanitizer(2) → 20.0
 */
class FloatSanitizer implements Sanitizable
{
  /**
   * Number of decimal places to round to (null = no rounding).
   * @var int|null
   */
  protected ?int $decimals;

  public function __construct(?int $decimals = null)
  {
    $this->decimals = $decimals;
  }

  public function sanitize($value): mixed
  {
    if (is_array($value)) {
      return array_map(fn($v) => $this->cast($v), $value);
    }

    return $this->cast($value);
  }

  protected function cast(mixed $value): float
  {
    $float = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    return $this->decimals !== null ? round($float, $this->decimals) : $float;
  }
}
