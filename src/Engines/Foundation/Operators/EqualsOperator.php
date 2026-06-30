<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * Default equals operator.
 */
final class EqualsOperator implements OperatorInterface
{
  /**
   * Apply the equals operator to the query builder.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    // Use payload operator as provided (e.g. =, !=, <, >) and the raw value
    return $builder->where($payload->field, $payload->operator, $payload->value);
  }
}
