<?php

namespace Kettasoft\Filterable\Foundation\Runtime;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator;
use Kettasoft\Filterable\Support\Payload;

/**
 * Holds transient state for a single Filterable instance.
 */
final class Context
{
  /**
   * @var array<string, Payload>
   */
  protected array $applied = [];

  /**
   * @var array<int, array{payload: Payload, reason: string|null, field: string, value: mixed, timestamp: \Carbon\Carbon}>
   */
  protected array $skipped = [];

  protected array $data = [];

  protected ?Builder $builder = null;

  protected ?CacheKeyGenerator $cacheKeyGenerator = null;

  /**
   * Store a snapshot of an applied payload.
   */
  public function commitPayload(string $key, Payload $payload): void
  {
    $this->applied[$key] = clone $payload;
  }

  /**
   * Store a skipped payload and its diagnostic metadata.
   */
  public function skipPayload(Payload $payload, ?string $reason = null): void
  {
    $payload = clone $payload;

    $this->skipped[] = [
      'payload' => $payload,
      'reason' => $reason,
      'field' => $payload->field,
      'value' => $payload->value,
      'timestamp' => now(),
    ];
  }

  /**
   * Get all applied payloads or one payload by key.
   *
   * @return array<string, Payload>|Payload|null
   */
  public function getApplied(?string $key = null): array|Payload|null
  {
    if ($key === null) {
      return $this->applied;
    }

    return $this->applied[$key] ?? null;
  }

  /**
   * Get all skipped payloads or entries for one field.
   */
  public function getSkipped(?string $field = null): array
  {
    if ($field === null) {
      return $this->skipped;
    }

    return array_values(array_filter(
      $this->skipped,
      fn($item) => $item['field'] === $field
    ));
  }

  public function hasSkipped(string $field): bool
  {
    return $this->getSkipped($field) !== [];
  }

  public function setData(array $data): void
  {
    $this->data = $data;
  }

  public function getData(): array
  {
    return $this->data;
  }

  public function setBuilder(Builder $builder): void
  {
    $this->builder = $builder;
  }

  public function getBuilder(): ?Builder
  {
    return $this->builder;
  }

  public function hasBuilder(): bool
  {
    return $this->builder !== null;
  }

  public function setCacheKeyGenerator(CacheKeyGenerator $generator): void
  {
    $this->cacheKeyGenerator = $generator;
  }

  public function getCacheKeyGenerator(): ?CacheKeyGenerator
  {
    return $this->cacheKeyGenerator;
  }
}
