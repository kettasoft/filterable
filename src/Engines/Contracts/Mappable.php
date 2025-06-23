<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface Mappable
{
  /**
   * Map the given key to the corresponding value.
   * @param string|null $key
   * @return mixed
   */
  public function map(string|null $key = null);
}
