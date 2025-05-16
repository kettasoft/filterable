<?php

namespace Kettasoft\Filterable\Contracts;

interface FilterableContext
{
  /**
   * Get current data.
   * @return array
   */
  public function getData(): mixed;
}
