<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class NullOperator implements Operator
{
  public function apply(Builder $builder, Payload $payload): Builder
  {
    if (OperatorResolver::normalize($payload->operator) === 'is not null') {
      return $builder->whereNotNull($payload->field);
    }

    return $builder->whereNull($payload->field);
  }
}
