<?php

namespace Kettasoft\Filterable\Support;

use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Filterable;

class TreeBasedRelationsResolver
{
  protected Filterable $context;

  public function __construct(Filterable $context)
  {
    $this->context = $context;
  }

  public function resolve($query, $relation, $field, $operator, $value)
  {
    if ($this->validate($relation, $field)) {

      return $query->whereHas($relation, function ($q) use ($field, $operator, $value) {
        $q->where($field, $operator, $value);
      });
    }
  }

  protected function validate($relation, $field): bool
  {
    $relations = $this->context->getRelations();

    if (array_is_list($relations)) {
      return in_array($relation, $relations);
    }

    if (array_key_exists($relation, $relations)) {

      $fields = $this->context->getRelations()[$relation];

      return in_array($field, $fields);
    }

    if ($this->context->isStrict()) {
      throw new NotAllowedFieldException($field);
    }

    return false;
  }
}
