<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;


interface OperatorInterface
{
  /**
   * Apply the operator to the query builder.
   * @param Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder;
}
