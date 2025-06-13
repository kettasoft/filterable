<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Executable
{
  /**
   * Execute using the given query builder instance.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function execute(Builder $builder);
}
