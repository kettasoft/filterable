<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Trims leading and trailing whitespace (or custom characters) from string values.
 *
 * Alias: 'trim'
 *
 * @example
 *   protected $sanitizers = ['name' => 'trim'];
 *   // "  hello  " → "hello"
 */
class TrimSanitizer implements Sanitizable
{
  /**
   * Characters to trim (default: whitespace).
   * @var string
   */
  protected string $characters;

  public function __construct(string $characters = " \t\n\r\0\x0B")
  {
    $this->characters = $characters;
  }

  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return trim($value, $this->characters);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? trim($v, $this->characters) : $v,
        $value
      );
    }

    return $value;
  }
}
