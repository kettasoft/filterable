<?php

namespace Kettasoft\Filterable\Contracts;

interface FilterableContext
{
  /**
   * Get current data.
   * @return array
   */
  public function getData(): mixed;

  /**
   * Fetch all relevant filters from the filter API class.
   *
   * @return array
   */
  public function getFilterAttributes(): array;
}
