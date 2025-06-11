<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface HasInteractsWithOperators extends Strictable
{
  /**
   * Get operators from engine config. 
   * @return array
   */
  public function getOperatorsFromConfig(): array;

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
