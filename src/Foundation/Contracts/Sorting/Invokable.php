<?php

namespace Kettasoft\Filterable\Foundation\Contracts\Sorting;

use Kettasoft\Filterable\Foundation\Contracts\Sortable;

/**
 * Interface Invokable
 *
 * Defines a contract for classes that can be invoked to configure sorting rules.
 * @package Kettasoft\Filterable\Foundation\Contracts\Sorting
 * @link https://kettasoft.github.io/filterable/sorting#using-invokable-sort-classes
 */
interface Invokable
{
  /**
   * Invoke the sorting logic.
   * @param \Kettasoft\Filterable\Foundation\Contracts\Sortable $sort
   * @return Sortable
   */
  public function __invoke(Sortable $sort): Sortable;
}
