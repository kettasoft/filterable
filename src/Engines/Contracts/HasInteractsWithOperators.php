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
   * Get the globally allowed operators restricted by a field policy.
   *
   * @param string $field Public filter field before field mapping.
   * @return array<string, string>
   */
  public function allowedOperatorsFor(string $field): array;

  /**
   * Determine whether a field has an exact or fallback operator policy.
   *
   * @param string $field Public filter field before field mapping.
   * @return bool
   */
  public function hasOperatorPolicyFor(string $field): bool;

  /**
   * Default engine operator.
   * @return string
   */
  public function defaultOperator();
}
