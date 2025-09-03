<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

/**
 * The context in which attributes are processed.
 * 
 * @package Kettasoft\Filterable\Engines\Foundation\Attributes
 */
class AttributeContext
{
  /**
   * Create a new attribute context instance.
   *
   * @param mixed $query
   * @param mixed $payload
   * @param array $state
   */
  public function __construct(
    public mixed $query = null,
    public mixed $payload = null,
    public array $state = []
  ) {}

  /**
   * Set a value in the context state.
   *
   * @param string $key
   * @param mixed $value
   * @return void
   */
  public function set(string $key, mixed $value): void
  {
    $this->state[$key] = $value;
  }

  /**
   * Get a value from the context state.
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get(string $key, mixed $default = null): mixed
  {
    return $this->state[$key] ?? $default;
  }

  /**
   * Check if a key exists in the context state.
   *
   * @param string $key
   * @return bool
   */
  public function has(string $key): bool
  {
    return array_key_exists($key, $this->state);
  }
}
