<?php

namespace Kettasoft\Filterable\Sanitization;

use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Sanitization\HandlerFactory;

class Sanitizer implements \Countable, \ArrayAccess, \IteratorAggregate
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
   * Handle sanitizers.
   * @param string $field
   * @param mixed $value
   */
  public function handle(string $field, mixed $value)
  {
    if (empty($field) || !array_key_exists($field, $this->sanitizers)) {
      return $value;
    }

    foreach ($this->sanitizers as $key => $resolver) {
      if ($key === $field) {
        $value = HandlerFactory::handle($value, $resolver);
      }
    }

    return $value;
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
   * Set sanitizer classes
   * @param array $sanitizers
   * @param bool $override Override current sanitizers when (true)
   * @return static
   */
  public function setSanitizers(array $sanitizers, bool $override = true): static
  {
    $this->sanitizers = $override ? $sanitizers : array_merge($this->sanitizers, $sanitizers);
    return $this;
  }

  /**
   * Determine if a sanitizer exists for the given field.
   * @param string $field
   * @return bool
   */
  public function has(string $field): bool
  {
    return array_key_exists($field, $this->sanitizers);
  }

  /**
   * Get the sanitizer resolver for the given field.
   * @param string $field
   * @return mixed|null
   */
  public function get(string $field): mixed
  {
    return $this->sanitizers[$field] ?? null;
  }

  /**
   * Remove the sanitizer for the given field.
   * @param string $field
   * @return void
   */
  public function remove(string $field): void
  {
    unset($this->sanitizers[$field]);
  }

  /**
   * Clear all registered sanitizers.
   * @return void
   */
  public function clear(): void
  {
    $this->sanitizers = [];
  }

  /**
   * ArrayAccess: Check if a sanitizer exists for the given field.
   * @param string $field
   * @return bool
   */
  public function offsetExists($field): bool
  {
    return $this->has($field);
  }

  /**
   * ArrayAccess: Get the sanitizer resolver for the given field.
   * @param string $field
   * @return mixed|null
   */
  public function offsetGet($field): mixed
  {
    return $this->get($field);
  }

  /**
   * ArrayAccess: Set the sanitizer resolver for the given field.
   * @param string $field
   * @param mixed $value
   * @return void
   */
  public function offsetSet($field, $value): void
  {
    $this->sanitizers[$field] = $value;
  }

  /**
   * ArrayAccess: Unset the sanitizer for the given field.
   * @param string $field
   * @return void
   */
  public function offsetUnset($field): void
  {
    $this->remove($field);
  }

  /**
   * IteratorAggregate: Get an iterator for the registered sanitizers.
   * @return \ArrayIterator
   */
  public function getIterator(): \ArrayIterator
  {
    return new \ArrayIterator($this->sanitizers);
  }
}
