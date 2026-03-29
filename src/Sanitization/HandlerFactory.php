<?php

namespace Kettasoft\Filterable\Sanitization;

use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\SlugSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\FloatSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\BooleanSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\IntegerSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\LowercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\UppercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripTagsSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\EscapeHtmlSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\NullIfEmptySanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripSpecialCharsSanitizer;
use Kettasoft\Filterable\Sanitization\Handlers\ArrayHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ObjectHandler;
use Kettasoft\Filterable\Sanitization\Handlers\StringHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ClosureHandler;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class HandlerFactory
{
  /**
   * Built-in sanitizer aliases mapped to their default class names.
   *
   * These can be used as short string identifiers in the $sanitizers array
   * instead of providing a full class name.
   *
   * @var array<string, class-string>
   */
  protected static array $defaults = [
    'trim'          => TrimSanitizer::class,
    'lowercase'     => LowercaseSanitizer::class,
    'uppercase'     => UppercaseSanitizer::class,
    'strip_tags'    => StripTagsSanitizer::class,
    'escape_html'   => EscapeHtmlSanitizer::class,
    'integer'       => IntegerSanitizer::class,
    'float'         => FloatSanitizer::class,
    'boolean'       => BooleanSanitizer::class,
    'slug'          => SlugSanitizer::class,
    'null_if_empty' => NullIfEmptySanitizer::class,
    'clamp'         => ClampSanitizer::class,
    'strip_chars'   => StripSpecialCharsSanitizer::class,
  ];

  /**
   * Handle sanitize value by sanitizer handlers.
   * @param mixed $value
   * @param mixed $sanitizer
   */
  public static function handle($value, $sanitizer)
  {
    return static::makeHandler($sanitizer)->handle($value);
  }

  /**
   * Resolve an alias to a concrete sanitizer class name, if applicable.
   *
   * @param mixed $sanitizer
   * @return mixed
   */
  protected static function resolveAlias(mixed $sanitizer): mixed
  {
    if (is_string($sanitizer) && isset(static::$defaults[$sanitizer])) {
      return static::$defaults[$sanitizer];
    }

    return $sanitizer;
  }

  /**
   * Register custom aliases for sanitizer classes.
   *
   * @param array<string, class-string> $aliases
   * @return void
   */
  public static function extend(array $aliases): void
  {
    static::$defaults = array_merge(static::$defaults, $aliases);
  }

  /**
   * Get all registered aliases (built-in + custom).
   *
   * @return array<string, class-string>
   */
  public static function aliases(): array
  {
    return static::$defaults;
  }

  /**
   * Create SanitizerHandler instance based on sanitizer type.
   * @param mixed $sanitizer
   * @throws \RuntimeException
   * @return SanitizeHandler
   */
  protected static function makeHandler($sanitizer): SanitizeHandler
  {
    $sanitizer = static::resolveAlias($sanitizer);

    $handler = match (true) {
      is_string($sanitizer)   => new StringHandler($sanitizer),
      is_callable($sanitizer) => new ClosureHandler($sanitizer),
      is_array($sanitizer)    => new ArrayHandler($sanitizer),
      is_object($sanitizer)   => new ObjectHandler($sanitizer),
      default                 => throw new \RuntimeException("Handler is not processable"),
    };

    return $handler;
  }
}
