<?php

namespace Kettasoft\Filterable\Traits;

trait FieldNormalizer
{
  /**
   * Check if normalize field option is enable in engine.
   * @return bool
   */
  abstract protected function hasNormalizeFieldCondition(): bool;

  /**
   * Normalize incoming field name to lowercase.
   * @param mixed $field
   */
  public function normalizeField($field)
  {
    return $this->hasNormalizeFieldCondition() ? strtolower($field) : $field;
  }
}
