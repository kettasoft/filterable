<?php

namespace Kettasoft\Filterable\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Interface HasBuilder
 *
 * This interface indicates that a class has a query builder instance that can be accessed and manipulated.
 * It is used to ensure that classes implementing this interface provide a method to retrieve or set the builder.
 */
interface HasBuilder
{
  /**
   * Get the query builder instance.
   * @return \Illuminate\Database\Eloquent\Builder The query builder instance.
   */
  public function getBuilder(): Builder;

  /**
   * Set the query builder instance.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return static
   */
  public function setBuilder(Builder $builder): static;
}
