<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use InvalidArgumentException;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * BETWEEN operator.
 */
final class BetweenOperator implements OperatorInterface
{
  /**
   * Apply the BETWEEN operator to the query builder.
   *
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   * @throws InvalidArgumentException if the payload does not contain exactly 2 values.
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    $values = $payload->explode();

    if (\count($values) !== 2) {
      throw new InvalidArgumentException('Between operator requires exactly 2 values.');
    }

    return $builder->whereBetween($payload->field, [$values[0], $values[1]]);
  }
}
