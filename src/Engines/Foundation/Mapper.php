<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Engines\Contracts\Mappable;

trait Mapper
{
  public static function run(Mappable $mappable, $args = null)
  {
    return $mappable->map($args);
  }
}
