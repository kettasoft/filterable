<?php

namespace Kettasoft\Filterable\Sanitization\Contracts;

interface Sanitizable
{
  /**
   * Sanitize incoming value.
   * @param mixed $value
   * @return mixed
   */
  public function sanitize($value): mixed;
}
