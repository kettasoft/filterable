<?php

namespace Kettasoft\Filterable\Support;

use Kettasoft\Filterable\Engines\Contracts\QueryResolverContract;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Filterable;

class TreeBasedSignelConditionResolver
{
  /**
   * Resolve query.
   * @param mixed $query
   * @param mixed $field
   * @param mixed $operator
   * @param mixed $value
   * @return void
   */
  public static function resolve($query, $field, $operator, $value)
  {
    if (in_array($operator, ['in', 'not in']) && is_array($value)) {
      $method = $operator === 'in' ? 'whereIn' : 'whereNotIn';
      $query->{$method}($field, $value);
    } else {
      $query->where($field, $operator, $value);
    }
  }
}
