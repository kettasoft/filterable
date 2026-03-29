<?php

namespace Kettasoft\Filterable\Sanitization\Defaults;

use Illuminate\Support\Str;
use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

/**
 * Converts a string to a URL-friendly slug using Laravel's Str::slug().
 *
 * Alias: 'slug'
 *
 * @example
 *   protected $sanitizers = ['category' => 'slug'];
 *   // "Hello World!" → "hello-world"
 */
class SlugSanitizer implements Sanitizable
{
  /**
   * Word separator character.
   * @var string
   */
  protected string $separator;

  /**
   * Language for ASCII transliteration.
   * @var string
   */
  protected string $language;

  public function __construct(string $separator = '-', string $language = 'en')
  {
    $this->separator = $separator;
    $this->language  = $language;
  }

  public function sanitize($value): mixed
  {
    if (is_string($value)) {
      return Str::slug($value, $this->separator, $this->language);
    }

    if (is_array($value)) {
      return array_map(
        fn($v) => is_string($v) ? Str::slug($v, $this->separator, $this->language) : $v,
        $value
      );
    }

    return $value;
  }
}
