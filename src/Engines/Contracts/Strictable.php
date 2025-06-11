<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface Strictable
{
  /**
   * Check if the strict mode is enable.
   * @return bool
   */
  public function isStrict(): bool;
}
