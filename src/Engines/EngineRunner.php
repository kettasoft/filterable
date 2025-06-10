<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Engine;

trait EngineRunner
{
  /**
   * Execute the given filter engine using the provided query builder.
   * @param \Kettasoft\Filterable\Engines\Foundation\Engine $engine
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public static function run(Engine $engine, Builder $builder)
  {
    return $engine->apply($builder);
  }
}
