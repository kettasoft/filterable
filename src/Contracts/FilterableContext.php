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

  /**
   * Get mentors.
   * @return array
   */
  public function getMentors(): array;

  /**
   * Check if current filterable class has ignored empty values.
   * @return bool
   */
  public function hasIgnoredEmptyValues(): bool;
}
