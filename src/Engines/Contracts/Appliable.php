<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Appliable
{
  /**
   * Apply filters to the query builder.
   * 
   * @param Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder): Builder;
}
