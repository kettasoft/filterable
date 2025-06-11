<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Engines\Contracts\HasFieldMap;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;
use Kettasoft\Filterable\Engines\Contracts\Strictable;
use Kettasoft\Filterable\Filterable;

abstract class Engine implements HasInteractsWithOperators, HasFieldMap, Strictable
{
  /**
   * Create Engine instance.
   * @param \Kettasoft\Filterable\Contracts\FilterableContext $context
   */
  public function __construct(protected FilterableContext $context) {}

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  abstract public function apply(Builder $builder);

  /**
   * Check if the strict mode is enable in an engine config.
   * @return bool
   */
  abstract protected function isStrictFromConfig(): bool;

  /**
   * @inheritDoc
   */
  public function allowedOperators(): array
  {
    return $this->context->getAllowedOperators();
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
}
