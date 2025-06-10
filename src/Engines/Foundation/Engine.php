<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Contracts\FilterableContext;
use Illuminate\Contracts\Database\Eloquent\Builder;

abstract class Engine
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
}
