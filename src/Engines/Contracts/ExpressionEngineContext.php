<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Sanitization\Sanitizer;

interface ExpressionEngineContext
{
  /**
   * Get current data.
   * @return array
   */
  public function getData(): mixed;

  /**
   * Get sanitizer instance.
   * @return Sanitizer
   */
  public function getSanitizerInstance(): Sanitizer;

  /**
   * Get allowed fields to apply filtering.
   * @return array
   */
  public function getAllowedFields(): array;

  /**
   * Check if a given relation is allowed for filtering.
   * @param string $relation
   * @return bool
   */
  public function isRelationAllowed(string $relation, $field): bool;

  /**
   * Get columns wrapper.
   * @return array
   */
  public function getFieldsMap(): array;

  /**
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @return array
   */
  public function getAllowedOperators(): array;
}
