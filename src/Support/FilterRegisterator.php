<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Exceptions\FilterIsNotDefinedException;

class FilterRegisterator
{
  use ForwardsCalls;

  /**
   * Query builder instance.
   * @var Builder
   */
  protected Builder $builder;

  /**
   * Filterable instance.
   * @var FilterableContext|string
   */
  protected $filter;

  /**
   * Create FilterRegisterator instance.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param mixed $filter
   */
  public function __construct(Builder $builder, $filter = null)
  {
    $this->builder = $builder;
    $this->filter = $filter;
  }

  /**
   * Bind the filter instance to model.
   * @throws \Kettasoft\Filterable\Exceptions\FilterIsNotDefinedException
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function register(): Builder
  {
    if ($this->filter instanceof FilterableContext) {
      return $this->forwardCallTo($this->filter, 'apply', [$this->builder]);
    }

    if (is_string($this->filter) && $filter = config('filterable.aliases')->get($this->filter)) {
      $filter = App::make($filter);

      return $this->forwardCallTo($filter, 'apply', [$this->builder]);
    }

    if ($this->filter === null && $filter = $this->getModel()->getFilterable()) {
      $filter = App::make($filter);

      return $this->forwardCallTo($filter, 'apply', [$this->builder]);
    }

    throw new FilterIsNotDefinedException($this->filter);
  }

  /**
   * Get the model instance being queried.
   * @return Builder|Model
   */
  public function getModel(): Builder|Model
  {
    return $this->builder->getModel();
  }
}
