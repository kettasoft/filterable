<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Contracts\FilterableContext;
use Kettasoft\Filterable\Exceptions\FilterIsNotDefinedException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface;

class FilterResolver
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
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
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
   * @return QueryBuilderInterface
   */
  public function resolve(): QueryBuilderInterface|Filterable
  {
    if ($this->filter instanceof FilterableContext) {
      return $this->forwardCallTo($this->filter, 'apply', [$this->builder]);
    }

    if (is_string($this->filter) && $filter = config('filterable.aliases')[$this->filter] ?? null) {
      return $this->apply($filter);
    }

    if (is_a($this->filter, FilterableContext::class, true)) {
      return $this->apply($this->filter);
    }

    if ($this->filter === null && $filter = $this->getModel()->getFilterable()) {
      return $this->apply($filter);
    }

    throw new FilterIsNotDefinedException($this->filter);
  }

  /**
   * Apply the filter to the query builder.
   * 
   * @param mixed $filter
   */
  protected function apply($filter)
  {
    $filter = App::make($filter);
    return $this->forwardCallTo($filter, 'apply', [$this->builder]);
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
