<?php

namespace Kettasoft\Filterable\Traits;

use Kettasoft\Filterable\Filterable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\FilterResolver;
use Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException;
use Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface;

/**
 * Provides filterable functionality to Eloquent models.
 *
 * This trait allows models to interact with the Filterable system,
 * enabling dynamic query filtering based on request parameters.
 *
 * @method static \Kettasoft\Filterable\Foundation\Invoker|\Illuminate\Contracts\Database\Eloquent\Builder filter(\Kettasoft\Filterable\Filterable|string|array|null $filter = null)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InteractsWithFilterable
{
  /**
   * Apply filterable to the query.
   *
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $query
   * @param \Kettasoft\Filterable\Filterable|string|array|null $filter
   * @return \Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface
   */
  public function scopeFilter(Builder $query, Filterable|string|array|null $filter = null): QueryBuilderInterface
  {
    return (new FilterResolver($query, $filter))->resolve();
  }

  /**
   * Get the filterable class associated with this model.
   *
   * @return string|array
   * @throws \Kettasoft\Filterable\Exceptions\FilterClassNotResolvedException
   */
  public function getFilterable(): string|array
  {
    if (!property_exists($this, 'filterable')) {
      throw new FilterClassNotResolvedException(get_class($this));
    }

    return $this->filterable;
  }

  /**
   * Check if model has a filterable class defined.
   *
   * @return bool
   */
  public function hasFilterable(): bool
  {
    return property_exists($this, 'filterable') && !empty($this->filterable);
  }

  /**
   * Get the number of models to return per page.
   *
   * @return int
   */
  public function getPerPage(): int
  {
    return config('filterable.paginate_limit')
      ?? request('perPage', parent::getPerPage());
  }

  /**
   * Get the default filter class for this model.
   *
   * @return string|null
   */
  public function getDefaultFilterClass(): ?string
  {
    if (!$this->hasFilterable()) {
      return null;
    }

    if (is_array($this->filterable)) {
      return $this->filterable[0] ?? null;
    }

    return $this->filterable;
  }

  /**
   * Check if model supports multiple filters.
   *
   * @return bool
   */
  public function supportsMultipleFilters(): bool
  {
    return $this->hasFilterable() && is_array($this->filterable);
  }
}
