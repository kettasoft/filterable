<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface HasAllowedFieldChecker extends Strictable
{
  /**
   * Get all allowed fields.
   * @return array
   */
  public function getAllowedFields(): array;
}
