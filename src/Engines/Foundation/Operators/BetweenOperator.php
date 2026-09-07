<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorValueException;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class BetweenOperator implements Operator
{
  public function apply(Builder $builder, Payload $payload): Builder
  {
    $values = $payload->explode();

    if (is_string($payload->value)) {
      $values = array_map('trim', $values);
    }

    if (count($values) !== 2) {
      throw new InvalidOperatorValueException(
        $payload,
        'The between operator requires exactly two values.'
      );
    }

    if (OperatorResolver::normalize($payload->operator) === 'not between') {
      return $builder->whereNotBetween($payload->field, $values);
    }

    return $builder->whereBetween($payload->field, $values);
  }
}
