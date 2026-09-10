<?php

namespace Kettasoft\Filterable\Exceptions;

use UnexpectedValueException;
use Kettasoft\Filterable\Exceptions\Contracts\DriverException;

class InvalidDriverResultException extends UnexpectedValueException implements DriverException
{
  /**
   * Create an exception for a driver result incompatible with the active lifecycle.
   *
   * @param string $driver Driver implementation that returned the result.
   * @param object $result Backend query object returned by the driver.
   * @param string $expected Expected result contract or class name.
   */
  public function __construct(string $driver, object $result, string $expected)
  {
    $resultClass = $result::class;

    parent::__construct(
      "Filterable driver [{$driver}] returned [{$resultClass}]; expected [{$expected}]."
    );
  }
}
