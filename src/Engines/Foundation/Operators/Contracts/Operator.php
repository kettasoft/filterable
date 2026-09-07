<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;

interface Operator
{
  /**
   * Apply the operator represented by the payload.
   */
  public function apply(Builder $builder, Payload $payload): Builder;
}
