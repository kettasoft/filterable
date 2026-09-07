<?php

namespace Kettasoft\Filterable\Support;

class ConditionNormalizer
{
  /**
   * Normalize condition to [ operator => value ].
   * @param mixed $condition
   * @param string $operator
   * @return array
   */
  public static function normalize(mixed $condition, string|null $operator = null): array
  {
    if (
      is_array($condition)
      && array_key_exists('operator', $condition)
      && array_key_exists('value', $condition)
    ) {
      return [
        'operator' => $condition['operator'],
        'value' => $condition['value'],
      ];
    }

    if (is_array($condition) && !array_is_list($condition) && count($condition) === 1) {
      return [
        'operator' => array_key_first($condition),
        'value' => array_values($condition)[0] ?? null
      ];
    }

    return ['operator' => $operator, 'value' => $condition];
  }
}
