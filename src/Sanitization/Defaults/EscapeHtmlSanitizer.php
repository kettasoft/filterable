<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Converts HTML special characters to their entity equivalents,
 * preventing XSS while preserving the string for safe output.
 *
 * Alias: 'escape_html'
 *
 * @example
 *   protected $sanitizers = ['title' => 'escape_html'];
 *   // "<script>alert(1)</script>" → "&lt;script&gt;alert(1)&lt;/script&gt;"
 */
class EscapeHtmlSanitizer implements Sanitizable
{
  /**
   * Character encoding.
   * @var string
   */
  protected string $encoding;

  public function __construct(string $encoding = 'UTF-8')
  {
    $this->encoding = $encoding;
  }

  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $this->encoding);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v)
          ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, $this->encoding)
          : $v,
        $value
      );
    }

    return $value;
  }
}
