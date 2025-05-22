<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface HasInteractsWithOperators extends Strictable
{
  /**
   * Get defined engine operators. 
   * @return array
   */
  public function operators(): array;

  /**
   * Get allowed operators only.
   * @return array
   */
  public function allowedOperators(): array;

  /**
   * Default engine operator.
   * @return string
   */
  public function defaultOperator();
}
