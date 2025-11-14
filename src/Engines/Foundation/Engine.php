<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Engines\Contracts\Executable;
use Kettasoft\Filterable\Engines\Contracts\HasAllowedFieldChecker;
use Kettasoft\Filterable\Engines\Contracts\HasFieldMap;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;
use Kettasoft\Filterable\Engines\Contracts\Strictable;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Resources;

abstract class Engine implements HasInteractsWithOperators, HasFieldMap, Strictable, Executable, HasAllowedFieldChecker
{
  /**
   * Create Engine instance.
   * @param \Kettasoft\Filterable\Contracts\FilterableContext $context
   */
  public function __construct(protected FilterableContext $context)
  {
    $resources = $this->context->getResources()
      ->setOperators($this->allowedOperators());

    $resources->operators->setDefault($this->defaultOperator());
  }

  /**
   * Apply filters to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  abstract public function execute(Builder $builder);

  /**
   * Check if the strict mode is enable in an engine config.
   * @return bool
   */
  abstract protected function isStrictFromConfig(): bool;

  abstract protected function getAllowedFieldsFromConfig(): array;

  abstract protected function isIgnoredEmptyValuesFromConfig(): bool;

  abstract public function getEngineName(): string;

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
