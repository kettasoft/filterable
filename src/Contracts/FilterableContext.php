<?php

namespace Kettasoft\Filterable\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Foundation\Invoker;

/**
 * Contract for filter objects that can be resolved by model integration.
 */
interface FilterableContext
{
  /**
   * Apply the filter to an Eloquent query.
   *
   * @param Builder|null $builder
   * @return Invoker|Builder
   */
  public function apply(Builder|null $builder = null): Invoker|Builder;
}
