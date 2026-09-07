<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class InOperator implements Operator
{
  public function apply(Builder $builder, Payload $payload): Builder
  {
    $values = $payload->explode();

    if (is_string($payload->value)) {
      $values = array_map('trim', $values);
    }

    if (OperatorResolver::normalize($payload->operator) === 'not in') {
      return $builder->whereNotIn($payload->field, $values);
    }

    return $builder->whereIn($payload->field, $values);
  }
}
