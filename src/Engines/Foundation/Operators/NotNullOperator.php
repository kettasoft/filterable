<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * IS NOT NULL operator.
 */
final class NotNullOperator implements OperatorInterface
{
  /**
   * Apply the IS NOT NULL operator to the query builder.
   *
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    return $builder->whereNotNull($payload->field);
  }
}
