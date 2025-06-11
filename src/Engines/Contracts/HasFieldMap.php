<?php

namespace Kettasoft\Filterable\Engines\Contracts;

interface HasFieldMap
{
  /**
   * Return an array of field mapping.
   * 
   * @return array<string, string>
   */
  public function getFieldsMap(): array;
}
