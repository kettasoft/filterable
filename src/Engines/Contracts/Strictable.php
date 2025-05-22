<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface Strictable
{
  /**
   * Check if engine enable strict mode.
   * @return bool
   */
  public function isStrict(): bool;
}
