<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Filterable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Engines\Contracts\Skippable;
use Kettasoft\Filterable\Engines\Contracts\Executable;
use Kettasoft\Filterable\Engines\Contracts\Strictable;
use Kettasoft\Filterable\Engines\Contracts\HasFieldMap;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;
use Kettasoft\Filterable\Support\Payload;

abstract class Engine implements HasInteractsWithOperators, HasFieldMap, Strictable, Executable, HasAllowedFieldChecker, Skippable
{
  /**
   * Create Engine instance.
   * @param Filterable $context
   */
  public function __construct(protected Filterable $context) {}

  /**
   * Get engine name.
   * @return string
   */
  abstract public function getEngineName(): string;

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  abstract public function execute(Builder $builder);

  /**
   * Attempt to execute the given callback, handling exceptions.
   *
   * @param \Closure $callback
   * @return bool
   */
  final protected function attempt(\Closure $callback): bool
  {
    try {
      return $callback->call($this);
    } catch (\Throwable $e) {
      return $this->context->getExceptionHandler()->handle($e, $this);
    }
  }

  /**
   * Skip the current filter execution with a message and payload.
   *
   * @param \Kettasoft\Filterable\Support\Payload $payload The payload being skipped
   * @param string|null $message The reason for skipping
   * @return never
   * @throws SkipExecution
   */
  public function skip(Payload $payload, ?string $message = null): never
  {
    throw new SkipExecution($message ?? 'Filter execution skipped.', $payload);
  }

  /**
   * Get allowed fields to filtering.
   * @return array
   */
  protected function getAllowedFieldsFromConfig(): array
  {
    return config("filterable.engines.{$this->getEngineName()}.allowed_fields", []);
  }

  /**
   * Check if empty values are ignored from engine config.
   * @return bool
   */
  protected function isIgnoredEmptyValuesFromConfig(): bool
  {
    return config("filterable.engines.{$this->getEngineName()}.ignore_empty_values", false);
  }

  /**
   * Get allowed operators to filtering.
   * @return array
   */
  public function getOperatorsFromConfig(): array
  {
    return config("filterable.engines.{$this->getEngineName()}.allowed_operators", []);
  }

  /**
   * Check if the strict mode is enable in an engine config.
   * @return bool
   */
  protected function isStrictFromConfig(): bool
  {
    return config("filterable.engines.{$this->getEngineName()}.strict", false);
  }

  public function isIgnoredEmptyValues(): bool
  {
    return $this->isIgnoredEmptyValuesFromConfig() || $this->context->hasIgnoredEmptyValues();
  }

  public function getAllowedFields(): array
  {
    return array_merge($this->getAllowedFieldsFromConfig(), $this->context->getAllowedFields());
  }

  /**
   * @inheritDoc
   */
  public function allowedOperators(): array
  {
    if (empty($this->context->getAllowedOperators())) {
      return $this->getOperatorsFromConfig();
    }

    $requested = $this->context->getAllowedOperators();

    return array_filter(
      $this->getOperatorsFromConfig(),
      fn($operator, $alias) => in_array($alias, $requested, true)
        || in_array($operator, $requested, true),
      ARRAY_FILTER_USE_BOTH
    );
  }

  /**
   * @inheritDoc
   */
  public function getFieldsMap(): array
  {
    return $this->context->getFieldsMap();
  }

  /**
   * @inheritDoc
   */
  public function isStrict(): bool
  {
    return is_bool($this->context->isStrict()) ? $this->context->isStrict() : $this->isStrictFromConfig();
  }

  /**
   * Get the context instance.
   * @return Filterable
   */
  public function getContext(): Filterable
  {
    return $this->context;
  }

  /**
   * Clone the engine and bind it to another Filterable instance.
   *
   * @internal
   */
  final public function cloneForContext(Filterable $context): static
  {
    $engine = clone $this;
    $engine->context = $context;

    return $engine;
  }

  public function getResources(): Resources
  {
    return $this->context->getResources();
  }

  /**
   * Sanitize the given value using the sanitizer instance.
   *
   * @param mixed $filed
   * @param mixed $value
   */
  final protected function sanitizeValue($filed, $value)
  {
    $sanitizer = $this->context->getSanitizerInstance();

    return $sanitizer->handle($filed, $value);
  }

  /**
   * Commit an applied payload.
   * @param string $key
   * @param Payload $payload
   * @return bool
   */
  final protected function commit(string $key, Payload $payload): bool
  {
    return $this->context->commit($key, $payload);
  }
}
