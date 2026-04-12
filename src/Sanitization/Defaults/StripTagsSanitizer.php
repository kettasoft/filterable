<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Strips HTML and PHP tags from string values.
 *
 * Alias: 'strip_tags'
 *
 * @example
 *   protected $sanitizers = ['bio' => 'strip_tags'];
 *   // "<b>Hello</b> <script>alert(1)</script>" → "Hello "
 */
class StripTagsSanitizer implements Sanitizable
{
  /**
   * Allowed HTML tags (e.g. '<b><i>').
   * @var string|null
   */
  protected ?string $allowedTags;

  public function __construct(?string $allowedTags = null)
  {
    $this->allowedTags = $allowedTags;
  }

  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return strip_tags($value, $this->allowedTags);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? strip_tags($v, $this->allowedTags) : $v,
        $value
      );
    }

    return $value;
  }
}
