<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;

interface Executable
{
  /**
   * Execute using the given query builder instance.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return \Illuminate\Contracts\Database\Eloquent\Builder
   */
  public function execute(Builder $builder);
}
