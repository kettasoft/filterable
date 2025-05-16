<?php

namespace Kettasoft\Filterable\Traits;

trait InteractsWithFilterKey
{
  /**
   * Filter key to extract data from query string.
   * @var string|null
   */
  protected $filterKey = 'filter';

  /**
   * Get a filter key.
   * @return string
   */
  public function getFilterKey(): string
  {
    return $this->filterKey ?? config('filterable.filter_key', 'filter');
  }

  /**
   * Set a filter key.
   * @param string $key
   * @return static
   */
  public function setFilterKey(string $key): static
  {
    $this->filterKey = $key;
    return $this;
  }
}
