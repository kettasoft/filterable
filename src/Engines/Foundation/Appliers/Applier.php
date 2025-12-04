<?php

namespace Kettasoft\Filterable\Engines\Foundation\Appliers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;

abstract class Applier
{
  public static function apply(Appliable $appliable, Builder $builder)
  {
    return $appliable->apply($builder);
  }
}
