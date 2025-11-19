<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Support\Arr;
use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Engines\Contracts\Skippable;
use Kettasoft\Filterable\Engines\Contracts\Executable;
use Kettasoft\Filterable\Engines\Contracts\Strictable;
use Kettasoft\Filterable\Engines\Contracts\HasFieldMap;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;

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
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  abstract public function execute(Builder $builder);

  /**
   * @inheritDoc
   */
  public function skip(string $message, mixed $clause = null): never
  {
    throw new SkipExecution($message, $clause);
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

    return Arr::only($this->getOperatorsFromConfig(), $this->context->getAllowedOperators());
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
   * Commit applied clauses.
   * @param string $key
   * @param Clause $clause
   * @return bool
   */
  final protected function commit(string $key, Clause $clause): bool
  {
    return $this->context->commit($key, $clause);
  }
}
