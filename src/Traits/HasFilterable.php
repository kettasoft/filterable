<?php

namespace Kettasoft\Filterable\Traits;

use Kettasoft\Filterable\Filterable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\FilterRegisterator;

/**
 * Apply filters dynamically to Eloquent Query.
 *
 * This is not a typical Laravel Global Scope.
 */
trait HasFilterable
{
  /**
   * Apply all relevant thread filters.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
   * @param \Kettasoft\Filterable\Filterable|string|null $filter
   * @return \Illuminate\Contracts\Database\Eloquent\Builder
   */
  public function scopeFilter(Builder $query, Filterable|string|array|null $filter = null): Builder
  {
    return (new FilterRegisterator($query, $filter))->register();
  }
}
