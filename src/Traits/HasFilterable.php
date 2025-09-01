<?php

namespace Kettasoft\Filterable\Traits;

use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\FilterResolver;
use Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException;
use Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface;

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
  public function scopeFilter(Builder $query, Filterable|string|array|null $filter = null): QueryBuilderInterface
  {
    return (new FilterResolver($query, $filter))->resolve();
  }

  /**
   * Get defined filterable class from model.
   * @throws \Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException
   */
  public function getFilterable()
  {
    if (! property_exists($this, 'filterable')) {
      throw new FilterClassNotResolvedException(get_class($this));
    }

    return $this->filterable;
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
