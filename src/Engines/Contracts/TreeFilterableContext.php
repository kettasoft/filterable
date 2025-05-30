<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Sanitization\Sanitizer;

interface TreeFilterableContext
{
  /**
   * Get current data.
   * @return array
   */
  public function getData(): mixed;

  /**
   * Check if current filterable class has ignored empty values.
   * @return bool
   */
  public function hasIgnoredEmptyValues(): bool;

  /**
   * Get columns wrapper.
   * @return array
   */
  public function getFieldsMap(): array;

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
   * List of supported SQL operators you want to allow when parsing the expressions.
   * @return array
   */
  public function getAllowedOperators(): array;

  /**
   * Check if filter has strict mode.
   * @return mixed
   */
  public function isStrict();
}
