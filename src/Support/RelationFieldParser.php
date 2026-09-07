<?php

namespace Kettasoft\Filterable\Support;

/**
 * Converts nested relation input into dot-notated field paths.
 */
class RelationFieldParser
{
  /**
   * @param array $data
   * @param array $relations Allowed relation definitions.
   * @param array<string> $operators Recognized operator aliases.
   */
  public static function parse(array $data, array $relations, array $operators = []): array
  {
    return static::flatten($data, $relations, $operators);
  }

  /**
   * Flattens a nested array into dot-notated paths.
   *
   * @param array $data The data to flatten.
   * @param array $relations Allowed relation definitions.
   * @param array<string> $operators Recognized operator aliases.
   * @param string $prefix The prefix for the current level of recursion.
   * @return array The flattened array with dot-notated paths as keys.
   */
  protected static function flatten(
    array $data,
    array $relations,
    array $operators,
    string $prefix = ''
  ): array {
    $result = [];

    foreach ($data as $key => $value) {
      $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

      if (
        !is_array($value)
        || $value === []
        || static::isCondition($value, $operators)
        || !static::isRelationPrefix($path, $relations)
      ) {
        $result[$path] = $value;
        continue;
      }

      $result = array_merge(
        $result,
        static::flatten($value, $relations, $operators, $path)
      );
    }

    return $result;
  }

  /**
   * Determines if the given value is a condition.
   *
   * @param array $value The value to check.
   * @param array<string> $operators Recognized operator aliases.
   */
  protected static function isCondition(array $value, array $operators): bool
  {
    if (array_is_list($value)) {
      return true;
    }

    if (array_key_exists('operator', $value) && array_key_exists('value', $value)) {
      return true;
    }

    return count($value) === 1
      && in_array((string) array_key_first($value), $operators, true);
  }

  /**
   * Determines if the given path is a prefix of any relation.
   *
   * @param string $path The path to check.
   * @param array $relations Allowed relation definitions.
   */
  protected static function isRelationPrefix(string $path, array $relations): bool
  {
    $root = explode('.', $path, 2)[0];

    foreach ($relations as $relation => $fields) {
      if (is_int($relation)) {
        if ($fields === $root) {
          return true;
        }

        continue;
      }

      if ($relation === $path || str_starts_with($relation, "{$path}.")) {
        return true;
      }
    }

    return false;
  }
}
