<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;
use Kettasoft\Filterable\Support\Payload;

/**
 * NOT IN operator.
 */
final class NotInOperator implements OperatorInterface
{
  /**
   * Apply the NOT IN operator to the query builder.
   *
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @param Payload $payload
   * @return Builder
   */
  public function apply(Builder $builder, Payload $payload): Builder
  {
    $values = $payload->explode();

    // Edge case: empty list -> always true (no exclusion)
    if (empty($values)) {
      return $builder->whereRaw('1 = 1');
    }

    return $builder->whereNotIn($payload->field, $values);
  }
}
