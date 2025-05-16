<?php

namespace Kettasoft\Filterable\Engines;

use Kettasoft\Filterable\Engines\Contracts\Engine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;

class Invokeable implements Engine
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
  public function apply(Builder $builder)
  {
    //
  }
}
