<?php

namespace Kettasoft\Filterable\Support;

class RelationFieldParser
{
  /**
   * Parse request data and convert nested arrays to dot notation for relations.
   * 
   * Converts: ['user' => ['name' => 'value']] => ['user.name' => 'value']
   * Converts: ['user' => ['profile' => ['city' => 'value']]] => ['user.profile.city' => 'value']
   * 
   * @param array $data
   * @return array
   */
  public static function parse(array $data): array
  {
    return static::flatten($data);
  }

  /**
   * Flatten a nested array using dot notation.
   * 
   * @param array $array
   * @param string $prepend
   * @return array
   */
  protected static function flatten(array $array, string $prepend = ''): array
  {
    $results = [];

    foreach ($array as $key => $value) {
      if (is_array($value) && !empty($value)) {
        // Recursively flatten nested arrays
        $results = array_merge($results, static::flatten($value, $prepend . $key . '.'));
      } else {
        // Add the flattened key-value pair
        $results[$prepend . $key] = $value;
      }
    }

    return $results;
  }
}
