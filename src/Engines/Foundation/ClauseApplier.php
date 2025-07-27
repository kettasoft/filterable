<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;

class ClauseApplier implements Appliable
{
  /**
   * ClauseApplier constructot
   * @param \Kettasoft\Filterable\Engines\Foundation\Clause $clause
   */
  public function __construct(protected Clause $clause) {}

  /**
   * Apply a Clause to the query builder.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function apply(Builder $builder): Builder
  {
    if (! $this->clause->isAllowedField() && ! $this->clause->isRelational()) {
      return $builder;
    }

    if ($this->clause->isRelational()) {
      return $this->applyRelational($builder);
    }

    return $this->applyDirect($builder);
  }

  /**
   * Apply a direct (non-relational) clause to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  protected function applyDirect(Builder $builder)
  {
    return $builder->where(
      $this->clause->getDatabaseColumnName(),
      $this->clause->getOperator(),
      $this->clause->getValue()
    );
  }

  /**
   * Apply a relational clause to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  protected function applyRelational(Builder $builder)
  {
    return $this->clause->relation($this->clause->resources->relations)->resolve(
      $builder,
      $this->clause
    );
  }
}
