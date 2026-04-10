<?php

namespace Kettasoft\Filterable\Sanitization;

use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Sanitization\Handlers\ArrayHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ObjectHandler;
use Kettasoft\Filterable\Sanitization\Handlers\StringHandler;
use Kettasoft\Filterable\Sanitization\Handlers\ClosureHandler;
use Kettasoft\Filterable\Sanitization\Contracts\SanitizeHandler;

class Sanitizer implements \Countable
{
  use ForwardsCalls;

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
   *
   * @param mixed $value
   * @param mixed $sanitizer  string class-name, Closure, array, or Sanitizable object
   * @throws \RuntimeException
   * @return mixed
   */
  public static function apply(mixed $value, mixed $sanitizer): mixed
  {
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
      is_string($sanitizer)   => new StringHandler($sanitizer),
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
