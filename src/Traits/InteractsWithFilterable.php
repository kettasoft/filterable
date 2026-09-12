<?php

namespace Kettasoft\Filterable\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Support\FilterResolver;
use Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException;
use Kettasoft\Filterable\Foundation\Invoker;

/**
 * Apply filters dynamically to Eloquent Query.
 *
 * This is not a typical Laravel Global Scope.
 *
 * @method static \Kettasoft\Filterable\Foundation\Invoker|\Illuminate\Contracts\Database\Eloquent\Builder filter(\Kettasoft\Filterable\Contracts\FilterableContext|string|array|null $filter = null)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithFilterable
{
  /**
   * Apply all relevant thread filters.
   * @param $query
   * @param FilterableContext|string|array|null $filter
   * @return Invoker|Builder
   */
  public function scopeFilter(Builder $query, FilterableContext|string|array|null $filter = null): Invoker|Builder
  {
    return (new FilterResolver($query, $filter))->resolve();
  }

  /**
   * Get defined filterable class from model.
   * @throws FilterClassNotResolvedException
   */
  public function getFilterable()
  {
    if (! property_exists($this, 'filterable')) {
      throw new FilterClassNotResolvedException(get_class($this));
    }

    return $this->filterable;
  }
}
