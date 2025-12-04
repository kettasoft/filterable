<?php

namespace Kettasoft\Filterable\Engines\Foundation\Executors;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Executable;

trait Executer
{
  /**
   * Execute the given Executable instance with the provided query builder instance.
   * @param \Kettasoft\Filterable\Engines\Contracts\Executable $executable
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return \Illuminate\Contracts\Database\Eloquent\Builder
   */
  public static function execute(Executable $executable, Builder $builder)
  {
    return $executable->execute($builder);
  }
}
