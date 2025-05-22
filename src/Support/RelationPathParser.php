<?php

namespace Kettasoft\Filterable\Support;

class RelationPathParser
{
  /**
   * Splits a field into relation and actual column.
   * @param string $relation
   * @return array<string|null>
   */
  public static function resolve(string $relation): array
  {
    $segments = explode('.', $relation);
    $field = array_pop($segments);
    $path = implode('.', $segments);

    return [$path ?: null, $field];
  }
}
