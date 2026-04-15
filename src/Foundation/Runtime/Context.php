<?php

namespace Kettasoft\Filterable\Foundation\Runtime;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;

/**
 * Context - Manages transient runtime state for Filterable instances.
 * 
 * This class encapsulates all runtime state that is specific to a single filter execution.
 * Unlike configuration properties (filters, allowed fields, etc.), runtime state is:
 * - Transient: Only valid during a single filter operation
 * - Mutable: Changes as filters are applied
 * - Non-shareable: Should not be shared between cloned instances
 * 
 * The state includes:
 * - Applied filter payloads (tracking what filters were executed)
 * - Skipped payloads (tracking what filters were ignored and why)
 * - Parsed request data (the actual filter parameters from the request)
 * - Query builder instance (the Eloquent builder being filtered)
 * - Cache key generator (custom cache key generation logic)
 * 
 * By isolating runtime state in a dedicated class:
 * 1. Clone operations become simpler (just create new state instance)
 * 2. Testing becomes easier (can mock or inspect state independently)
 * 3. State management is centralized and explicit
 * 4. Following Single Responsibility Principle
 * 
 * @package Kettasoft\Filterable\Foundation
 * @since 3.x
 */
class Context
{
  /**
   * Applied filter payloads.
   * 
   * Stores all successfully applied filter payloads keyed by their field names.
   * This allows tracking which filters were actually applied to the query.
   * 
   * @var array<string, Payload>
   */
  protected array $applied = [];

  /**
   * Skipped payloads with metadata.
   * 
   * Stores information about filters that were skipped during execution,
   * including the reason for skipping, field name, value, and timestamp.
   * Useful for debugging and understanding why certain filters weren't applied.
   * 
   * @var array<array{payload: Payload, reason: string|null, field: string, value: mixed, timestamp: \Carbon\Carbon}>
   */
  protected array $skipped = [];

  /**
   * Parsed request data.
   * 
   * Contains the filter parameters extracted from the HTTP request.
   * This is the sanitized and validated data that will be used for filtering.
   * 
   * @var array
   */
  protected array $data = [];

  /**
   * The query builder instance.
   * 
   * Holds the Eloquent query builder or relation that filters are being applied to.
   * Initially null, set when apply() is called.
   * Can be a Builder, Relation, or any object implementing BuilderContract.
   * 
   * @var \Illuminate\Database\Eloquent\Builder|null
   */
  protected ?Builder $builder = null;

  /**
   * Cache key generator callback or instance.
   * 
   * Optional custom function or CacheKeyGenerator instance for generating cache keys.
   * If not set, the default cache key generation logic is used.
   * 
   * @var callable|\Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator|null
   */
  protected $cacheKeyGenerator = null;

  /**
   * Commit a payload as applied.
   * 
   * Records a filter payload that has been successfully applied to the query.
   * This is called by the engine after a filter is executed.
   * 
   * @param string $key The field name or unique identifier for the payload
   * @param Payload $payload The payload object representing the applied filter
   * @return void
   */
  public function commitPayload(string $key, Payload $payload): void
  {
    $this->applied[$key] = $payload;
  }

  /**
   * Commit a payload as applied (legacy alias).
   * 
   * @deprecated Use commitPayload() instead
   * @param string $key The field name or unique identifier
   * @param Payload $payload The payload object
   * @return void
   */
  public function commitClause(string $key, Payload $payload): void
  {
    $this->commitPayload($key, $payload);
  }

  /**
   * Register a skipped payload.
   * 
   * Records information about a filter that was skipped during execution.
   * Captures the payload, reason for skipping, and a timestamp for debugging.
   * 
   * @param Payload $payload The payload that was skipped
   * @param string|null $reason Optional explanation for why it was skipped
   * @return void
   */
  public function skipPayload(Payload $payload, ?string $reason = null): void
  {
    $this->skipped[] = [
      'payload' => $payload,
      'reason' => $reason,
      'field' => $payload->field,
      'value' => $payload->value,
      'timestamp' => now(),
    ];
  }

  /**
   * Get applied payloads.
   * 
   * Retrieves all applied payloads or a specific payload by key.
   * 
   * @param string|null $key Optional field name to get a specific payload
   * @return mixed All payloads if key is null, specific payload otherwise, or null if not found
   */
  public function getApplied(?string $key = null): mixed
  {
    if ($key === null) {
      return $this->applied;
    }

    return $this->applied[$key] ?? null;
  }

  /**
   * Get skipped payloads.
   * 
   * Retrieves all skipped payloads or filters by field name.
   * 
   * @param string|null $field Optional field name to filter skipped payloads
   * @return array All skipped payloads or filtered by field
   */
  public function getSkipped(?string $field = null): array
  {
    if ($field === null) {
      return $this->skipped;
    }

    return array_filter($this->skipped, fn($item) => $item['field'] === $field);
  }

  /**
   * Check if a field was skipped.
   * 
   * Determines whether any filters for the given field were skipped.
   * 
   * @param string $field The field name to check
   * @return bool True if the field has skipped filters, false otherwise
   */
  public function hasSkipped(string $field): bool
  {
    return !empty($this->getSkipped($field));
  }

  /**
   * Set parsed request data.
   * 
   * Updates the filter parameters from the request.
   * 
   * @param array $data The parsed filter data
   * @return void
   */
  public function setData(array $data): void
  {
    $this->data = $data;
  }

  /**
   * Get parsed request data.
   * 
   * Returns the filter parameters that were extracted from the request.
   * 
   * @return array The filter data
   */
  public function getData(): array
  {
    return $this->data;
  }

  /**
   * Set the query builder instance.
   * 
   * Attaches an Eloquent query builder or relation to this state.
   * 
   * @param \Illuminate\Database\Eloquent\Builder $builder The query builder or relation to set
   * @return void
   */
  public function setBuilder(Builder $builder): void
  {
    $this->builder = $builder;
  }

  /**
   * Get the query builder instance.
   * 
   * Returns the currently attached query builder or relation
   * 
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function getBuilder(): Builder
  {
    return $this->builder;
  }

  /**
   * Check if builder is set.
   * 
   * Determines whether a query builder has been attached to this state.
   * 
   * @return bool True if builder exists, false otherwise
   */
  public function hasBuilder(): bool
  {
    return $this->builder !== null;
  }

  /**
   * Set cache key generator callback or instance.
   * 
   * Assigns a custom function or CacheKeyGenerator for generating cache keys.
   * 
   * @param callable|\Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator|null $generator The generator function/instance or null to unset
   * @return void
   */
  public function setCacheKeyGenerator($generator): void
  {
    $this->cacheKeyGenerator = $generator;
  }

  /**
   * Get cache key generator callback or instance.
   * 
   * Returns the custom cache key generator, if set.
   * 
   * @return callable|\Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator|null The generator function/instance or null
   */
  public function getCacheKeyGenerator()
  {
    return $this->cacheKeyGenerator;
  }

  /**
   * Check if cache key generator is set.
   * 
   * Determines whether a custom cache key generator has been configured.
   * 
   * @return bool True if generator exists, false otherwise
   */
  public function hasCacheKeyGenerator(): bool
  {
    return $this->cacheKeyGenerator !== null;
  }

  /**
   * Reset all runtime state.
   * 
   * Clears all transient data, returning the state to its initial condition.
   * Useful for reusing a Filterable instance or after cloning.
   * 
   * @return void
   */
  public function reset(): void
  {
    $this->applied = [];
    $this->skipped = [];
    $this->data = [];
    $this->builder = null;
    $this->cacheKeyGenerator = null;
  }

  /**
   * Create a snapshot of current state.
   * 
   * Captures the current runtime state for later inspection or restoration.
   * Useful for debugging or implementing state rollback mechanisms.
   * 
   * @return array Associative array containing all state data
   */
  public function snapshot(): array
  {
    return [
      'applied' => $this->applied,
      'skipped' => $this->skipped,
      'data' => $this->data,
      'builder' => $this->builder,
      'cacheKeyGenerator' => $this->cacheKeyGenerator,
    ];
  }

  /**
   * Restore state from a snapshot.
   * 
   * Replaces current state with data from a previous snapshot.
   * Useful for implementing undo/rollback functionality.
   * 
   * @param array $snapshot Previously captured state snapshot
   * @return void
   */
  public function restore(array $snapshot): void
  {
    $this->applied = $snapshot['applied'] ?? [];
    $this->skipped = $snapshot['skipped'] ?? [];
    $this->data = $snapshot['data'] ?? [];
    $this->builder = $snapshot['builder'] ?? null;
    $this->cacheKeyGenerator = $snapshot['cacheKeyGenerator'] ?? null;
  }
}
