<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * Less than operator.
 */
final class LessThanOperator implements OperatorInterface
{
  /**
   * Apply the less than operator to the query builder.
   *
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    return $builder->where($payload->field, '<', $payload->value);
  }
}
