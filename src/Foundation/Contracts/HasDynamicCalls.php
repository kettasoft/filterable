<?php

namespace Kettasoft\Filterable\Foundation\Contracts;

/**
 * Interface for classes that support dynamic method calls.
 *
 * @package Kettasoft\Filterable\Foundation\Contracts
 */
interface HasDynamicCalls
{
  /**
   * Handle dynamic method calls.
   *
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function __call($method, $args);
}
