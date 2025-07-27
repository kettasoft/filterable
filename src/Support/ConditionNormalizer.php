<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Support\Arr;

class ConditionNormalizer
{
  /**
   * Normalize condition to [ operator => value ].
   * @param string|array|null $condition
   * @param string $operator
   * @return array
   */
  public static function normalize(string|array|null $condition, string|null $operator = null): array
  {
    if (is_string($condition)) {
      // If the condition is a string, we assume it's a value and use the operator.
      return ['operator' => $operator, 'value' => $condition];
    }

    if (is_array($condition) && !array_is_list($condition)) {
      // If the condition is an associative array, we assume it already has the operator as a key.
      return [
        'operator' => array_key_first($condition),
        'value' => array_values($condition)[0] ?? null
      ];
    }
  }
}
