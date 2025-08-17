<?php

namespace Kettasoft\Filterable\Foundation\Bags;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;

/**
 * Class Bag
 * 
 * Abstract base class for all Bags (FieldBag, RelationBag, etc).
 * Providers shared array-like behaviors and utility methods.
 * 
 * @template TKey of array-key
 * @template TValue
 */
abstract class Bag implements Countable, Arrayable, ArrayAccess, IteratorAggregate
{
  /**
   * Internal storage of the bag items.
   * @var array
   */
  protected array $items = [];

  /**
   * Internal aliases for the items.
   * @var array
   */
  protected array $aliases = [];

  /**
   * Create a new Bag instance.
   * @param array<TKey, TValue> $items
   */
  public function __construct(array $items = [])
  {
    $this->items = $items;
  }

  /**
   * Get all items as array
   * @return array<TKey, TValue>
   */
  public function all(): array
  {
    return $this->items;
  }

  /**
   * Determine if the given key exists.
   * @param TKey $key
   * @return bool
   */
  public function has($key): bool
  {
    return array_key_exists($key, $this->items) || in_array($key, $this->items);
  }

  /**
   * Get the item by key.
   * 
   * @param TKey $key
   * @param mixed|null $default
   * @return TValue|null
   */
  public function get($key, $default = null)
  {
    return $this->items[$key] ?? $default;
  }

  /**
   * Set an item by key.
   * @param TKey $key
   * @param TValue $value
   * @return void
   */
  public function set($key, $value = null)
  {
    if (count(func_get_args()) === 1) {
      dd($key);
      $this->items[] = $key;
    } else {
      $this->items[$key] = $value;
    }
  }

  /**
   * Fill the items
   * @param array<TKey, TValue> $items
   * @return void
   */
  public function fill(array $items)
  {
    $this->items = $items;
  }

  /**
   * Merge the items.
   * @param array $items
   * @return void
   */
  public function merge(array $items)
  {
    $this->items = array_merge($this->items, $items);
  }

  /**
   * Remove an item by key
   * @param TKey $key
   * @return void
   */
  public function forget($key)
  {
    unset($this->items[$key]);
  }

  public function aliases(array $aliases = [])
  {
    $this->aliases = $aliases;
  }

  /**
   * Return number of items.
   * @return int
   */
  public function count(): int
  {
    return count($this->items);
  }

  /**
   * Convert to array.
   * @return array<TKey, TValue>
   */
  public function toArray()
  {
    return $this->items;
  }

  public function getIterator(): \Traversable
  {
    return new \ArrayIterator($this->items);
  }

  /**
   * Determine if the given offset exists.
   * @param mixed $offset
   * @return bool
   */
  public function offsetExists(mixed $offset): bool
  {
    return isset($this->items[$offset]);
  }

  /**
   * Get item at offset.
   * @param TKey $offset
   * @return TValue|null
   */
  public function offsetGet(mixed $offset): mixed
  {
    return $this->items[$offset] ?? null;
  }

  /**
   * Set item at offset
   * @param TKey $offset
   * @param TValue $value
   * @return void
   */
  public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->items[$offset] = $value;
  }

  /**
   * Unset item at offset.
   * @param TKey $offset
   * @return void
   */
  public function offsetUnset(mixed $offset): void
  {
    unset($this->items[$offset]);
  }

  /**
   * Reset the Bag.
   * @return void
   */
  public function clear()
  {
    $this->items = [];
  }
}
