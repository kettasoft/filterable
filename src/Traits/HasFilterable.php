<?php

namespace Kettasoft\Filterable\Traits;

use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;
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
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @param \Kettasoft\Filterable\Filterable|string|null $filter
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeFilter(Builder $query, Filterable|string|array|null $filter = null): Builder
  {
    return (new FilterRegisterator($query, $filter))->register();
  }

  /**
   * Get the number of models to return per page.
   *
   * @return int
   */
  public function getPerPage()
  {
    return config('filterable.paginate_limit') ?? request('perPage', parent::getPerPage());
  }
}
