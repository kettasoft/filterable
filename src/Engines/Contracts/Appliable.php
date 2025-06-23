<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Appliable
{
  public function apply(Builder $builder): Builder;
}
