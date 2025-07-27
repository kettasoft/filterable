<?php

namespace Kettasoft\Filterable\Engines\Foundation\Executors;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Executable;

trait Executer
{
  /**
   * Execute the given Executable instance with the provided query builder instance.
   * @param \Kettasoft\Filterable\Engines\Contracts\Executable $executable
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public static function execute(Executable $executable, Builder $builder)
  {
    return $executable->execute($builder);
  }
}
