<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class ComparisonOperator implements Operator
{
  public function apply(Builder $builder, Payload $payload): Builder
  {
    return $builder->where($payload->field, $payload->operator, $payload->value);
  }
}
