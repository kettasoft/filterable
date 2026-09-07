<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Clamps a numeric value between an optional minimum and/or maximum bound.
 * Non-numeric values are returned unchanged.
 *
 * Alias: 'clamp'
 *
 * @example
 *   protected $sanitizers = ['per_page' => ClampSanitizer::class];
 *   // new ClampSanitizer(1, 100): 150 → 100  |  0 → 1
 */
class ClampSanitizer implements Sanitizable
{
  /**
   * Minimum allowed value (inclusive). Null = no lower bound.
   * @var int|float|null
   */
  protected int|float|null $min;

  /**
   * Maximum allowed value (inclusive). Null = no upper bound.
   * @var int|float|null
   */
  protected int|float|null $max;

  public function __construct(int|float|null $min = null, int|float|null $max = null)
  {
    $this->min = $min;
    $this->max = $max;
  }

  public function sanitize($value): mixed
  {
    if (is_array($value)) {
      return array_map(fn($v) => $this->clamp($v), $value);
    }

    return $this->clamp($value);
  }

  protected function clamp(mixed $value): mixed
  {
    if (! is_numeric($value)) {
      return $value;
    }

    // Preserve float type when either bound is a float
    $value = (is_float($this->min) || is_float($this->max))
      ? (float) $value
      : (int) $value;

    if ($this->min !== null) {
      $value = max($this->min, $value);
    }

    if ($this->max !== null) {
      $value = min($this->max, $value);
    }

    return $value;
  }
}
