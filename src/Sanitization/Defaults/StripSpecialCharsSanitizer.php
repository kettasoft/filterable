<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Removes characters that are not alphanumeric or whitespace.
 * An optional pattern of extra characters to allow can be provided.
 *
 * Alias: 'strip_chars'
 *
 * @example
 *   protected $sanitizers = ['username' => 'strip_chars'];
 *   // "hello@#$world!" → "helloworld"
 *
 *   // Allow underscores and dashes:
 *   protected $sanitizers = ['username' => new StripSpecialCharsSanitizer('_-')];
 *   // "hello_world-2025!" → "hello_world-2025"
 */
class StripSpecialCharsSanitizer implements Sanitizable
{
  /**
   * Extra characters to allow in addition to alphanumerics and spaces.
   * @var string
   */
  protected string $allowed;

  /**
   * Replacement string for each stripped character.
   * @var string
   */
  protected string $replacement;

  public function __construct(string $allowed = '', string $replacement = '')
  {
    $this->allowed     = $allowed;
    $this->replacement = $replacement;
  }

  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return $this->strip($value);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? $this->strip($v) : $v,
        $value
      );
    }

    return $value;
  }

  protected function strip(string $value): string
  {
    $extra = preg_quote($this->allowed, '/');

    return preg_replace("/[^a-zA-Z0-9\s{$extra}]/u", $this->replacement, $value);
  }
}
