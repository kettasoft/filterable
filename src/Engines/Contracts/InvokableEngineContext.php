<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Sanitization\Sanitizer;

interface InvokableEngineContext
{
  /**
   * Fetch all relevant filters from the filter API class.
   *
   * @return array
   */
  public function getFilterAttributes(): array;

  /**
   * Get the current request instance.
   * @return Request
   */
  public function getRequest(): Request;

  /**
   * Check if current filterable class has ignored empty values.
   * @return bool
   */
  public function hasIgnoredEmptyValues(): bool;

  /**
   * Get sanitizer instance.
   * @return Sanitizer
   */
  public function getSanitizerInstance(): Sanitizer;

  /**
   * Get mentors.
   * @return array
   */
  public function getMentors(): array;
}
