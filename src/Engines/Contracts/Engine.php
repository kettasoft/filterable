<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Contracts\FilterableContext;
use Illuminate\Contracts\Database\Eloquent\Builder;

interface Engine
{
  /**
   * Create Engine instance.
   * @param \Kettasoft\Filterable\Contracts\FilterableContext $context
   */
  public function __construct(FilterableContext $context);

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder);
}
