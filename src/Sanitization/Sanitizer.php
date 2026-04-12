<?php

namespace Kettasoft\Filterable\Sanitization;

use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\FloatSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\SlugSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\IntegerSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\BooleanSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\LowercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\UppercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\EscapeHtmlSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripTagsSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\NullIfEmptySanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripSpecialCharsSanitizer;
use Kettasoft\Filterable\Sanitization\Handlers\ArrayHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ObjectHandler;
use Kettasoft\Filterable\Sanitization\Handlers\StringHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ClosureHandler;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class Sanitizer implements \Countable
{
  use ForwardsCalls;

  /**
   * Built-in alias → FQCN map.
   * Can be extended at runtime via {@see Sanitizer::extend()}.
   *
   * @var array<string, class-string>
   */
  protected static array $aliases = [
    'trim'         => TrimSanitizer::class,
    'lowercase'    => LowercaseSanitizer::class,
    'uppercase'    => UppercaseSanitizer::class,
    'integer'      => IntegerSanitizer::class,
    'float'        => FloatSanitizer::class,
    'boolean'      => BooleanSanitizer::class,
    'slug'         => SlugSanitizer::class,
    'strip_tags'   => StripTagsSanitizer::class,
    'strip_chars'  => StripSpecialCharsSanitizer::class,
    'escape_html'  => EscapeHtmlSanitizer::class,
    'null_if_empty' => NullIfEmptySanitizer::class,
    'clamp'        => ClampSanitizer::class,
  ];

  /**
   * Registered sanitizers to operate upon.
   * @var array
   */
  protected array $sanitizers = [];

  /**
   * Create new Sanitizer instance.
   * @param array $sanitizers
   */
  public function __construct(array $sanitizers)
  {
    $this->sanitizers = $sanitizers;
  }

  /**
   * Register a custom alias that maps a short name to a Sanitizable class.
   *
   * @param string       $alias  Short name, e.g. 'my_trim'
   * @param class-string $class  FQCN implementing Sanitizable
   * @return void
   */
  public static function extend(string $alias, string $class): void
  {
    static::$aliases[$alias] = $class;
  }

  /**
   * Resolve an alias string to its registered FQCN, or return the input as-is
   * if it is already a fully-qualified class name (or unregistered string).
   *
   * @param string $alias
   * @return string  FQCN
   */
  public static function resolveAlias(string $alias): string
  {
    return static::$aliases[$alias] ?? $alias;
  }

  /**
   * Return a copy of the full alias → class map.
   *
   * @return array<string, class-string>
   */
  public static function getAliases(): array
  {
    return static::$aliases;
  }

  /**
   * Handle sanitizers for a given field value.
   *
   * @param string $field
   * @param mixed  $value
   * @return mixed
   */
  public function handle(string $field, mixed $value): mixed
  {
    if (empty($field) || ! array_key_exists($field, $this->sanitizers)) {
      return $value;
    }

    foreach ($this->sanitizers as $key => $resolver) {
      if ($key === $field) {
        $value = static::apply($value, $resolver);
      }
    }

    return $value;
  }

  /**
   * Apply a single sanitizer resolver to a value.
   * The resolver can be a string alias, a fully-qualified class name, a closure,
   * an array of resolvers, or a Sanitizable object.
   *
   * @param mixed $value
   * @param mixed $sanitizer
   * @throws \RuntimeException
   * @return mixed
   */
  public static function apply(mixed $value, mixed $sanitizer): mixed
  {
    if (is_string($sanitizer) && str_contains($sanitizer, '|')) {
      $sanitizer = explode('|', $sanitizer);
    }

    return static::makeHandler($sanitizer)->handle($value);
  }

  /**
   * Build the appropriate SanitizeHandler for the given sanitizer definition.
   *
   * @param mixed $sanitizer
   * @throws \RuntimeException
   * @return SanitizeHandler
   */
  protected static function makeHandler(mixed $sanitizer): SanitizeHandler
  {
    return match (true) {
      is_string($sanitizer)   => new StringHandler(static::resolveAlias($sanitizer)),
      is_callable($sanitizer) => new ClosureHandler($sanitizer),
      is_array($sanitizer)    => new ArrayHandler($sanitizer),
      is_object($sanitizer)   => new ObjectHandler($sanitizer),
      default                 => throw new \RuntimeException("Handler is not processable"),
    };
  }

  /**
   * Get the number of registered sanitizers.
   * @return int
   */
  public function count(): int
  {
    return count($this->sanitizers);
  }

  /**
   * Get registered sanitizers.
   * @return array
   */
  public function getSanitizers(): array
  {
    return $this->sanitizers;
  }

  /**
   * Set sanitizer classes.
   *
   * @param array $sanitizers
   * @param bool  $override Override current sanitizers when true
   * @return static
   */
  public function setSanitizers(array $sanitizers, bool $override = true): static
  {
    $this->sanitizers = $override ? $sanitizers : array_merge($this->sanitizers, $sanitizers);
    return $this;
  }
}
