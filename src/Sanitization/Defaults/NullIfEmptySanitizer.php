<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Returns null when the value is an empty string or matches a list of "empty" representations.
 * Useful for normalising optional request inputs that arrive as empty strings.
 *
 * Alias: 'null_if_empty'
 *
 * @example
 *   protected $sanitizers = ['search' => 'null_if_empty'];
 *   // ""  → null  |  "0" → null  |  "hello" → "hello"
 */
class NullIfEmptySanitizer implements Sanitizable
{
  /**
   * String representations that should be treated as empty.
   * @var array<string>
   */
  protected array $emptyValues;

  public function __construct(array $emptyValues = ['', '0', 'null', 'undefined', 'none'])
  {
    $this->emptyValues = $emptyValues;
  }

  public function sanitize($value): mixed
  {
    if (is_array($value)) {
      return array_map(fn($v) => $this->nullify($v), $value);
    }

    return $this->nullify($value);
  }

  protected function nullify(mixed $value): mixed
  {
    if (is_null($value)) {
      return null;
    }

    if (is_string($value) && in_array(trim($value), $this->emptyValues, true)) {
      return null;
    }

    return $value;
  }
}
