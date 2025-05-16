<?php

namespace Kettasoft\Filterable\Support;

class ConditionNormalizer
{
  /**
   * Normalize condition to [ operator => value ].
   * @param string|array|null $condition
   * @param string $operator
   * @return array
   */
  public static function normalize(string|array|null $condition, string $operator = null): array
  {
    return is_array($condition) ? $condition : [$operator ?? 'eq' => $condition];
  }
}
