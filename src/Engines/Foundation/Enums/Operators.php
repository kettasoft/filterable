<?php

namespace Kettasoft\Filterable\Engines\Foundation\Enums;

use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorException;

enum Operators: string
{
  case EQUALS = '=';
  case NOT_EQUALS = '!=';
  case GREATER_THAN = '>';
  case LESS_THAN = '<';
  case GREATER_THAN_OR_EQUAL = '>=';
  case LESS_THAN_OR_EQUAL = '<=';
  case LIKE = 'LIKE';
  case NOT_LIKE = 'NOT LIKE';
  case IN = 'IN';
  case NOT_IN = 'NOT IN';
  case IS_NULL = 'IS NULL';
  case IS_NOT_NULL = 'IS NOT NULL';
  case BETWEEN = 'BETWEEN';
  case NOT_BETWEEN = 'NOT BETWEEN';

  public function toString(): string
  {
    return $this->value;
  }

  public static function fromString(string $operator): string
  {
    return match ($operator) {
      'eq' => self::EQUALS->value,
      'ne', 'neq' => self::NOT_EQUALS->value,
      'gt' => self::GREATER_THAN->value,
      'lt' => self::LESS_THAN->value,
      'gte' => self::GREATER_THAN_OR_EQUAL->value,
      'lte' => self::LESS_THAN_OR_EQUAL->value,
      'like' => self::LIKE->value,
      'nlike', 'not_like' => self::NOT_LIKE->value,
      'in' => self::IN->value,
      'nin', 'not_in' => self::NOT_IN->value,
      'null', 'is_null' => self::IS_NULL->value,
      'notnull', 'is_not_null' => self::IS_NOT_NULL->value,
      'between' => self::BETWEEN->value,
      'nbetween', 'not_between' => self::NOT_BETWEEN->value,
      default => throw new InvalidOperatorException($operator),
    };
  }
}
