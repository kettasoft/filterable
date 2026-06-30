<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * IN operator.
 */
final class InOperator implements OperatorInterface
{
  /**
   * Apply the IN operator to the query builder.
   *
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    $values = $payload->explode();

    // Edge case: empty list -> always false
    if (empty($values)) {
      return $builder->whereRaw('1 = 0');
    }

    return $builder->whereIn($payload->field, $values);
  }
}
