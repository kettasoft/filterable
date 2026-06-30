<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\OperatorInterface;

/**
 * Resolves operator strings to operator strategy instances.
 */
final class OperatorResolver
{
  /**
   * Default mapping.
   *
   * @return array<string, class-string<OperatorInterface>>
   */
  private static function defaults(): array
  {
    return [
      '=' => EqualsOperator::class,
      'eq' => EqualsOperator::class,
      '!=' => EqualsOperator::class,
      '<>' => EqualsOperator::class,
      '>' => GreaterThanOperator::class,
      'gt' => GreaterThanOperator::class,
      '>=' => GreaterThanOrEqualOperator::class,
      'gte' => GreaterThanOrEqualOperator::class,
      '<' => LessThanOperator::class,
      'lt' => LessThanOperator::class,
      '<=' => LessThanOrEqualOperator::class,
      'lte' => LessThanOrEqualOperator::class,
      'in' => InOperator::class,
      'nin' => NotInOperator::class,
      'not in' => NotInOperator::class,
      'is null' => NullOperator::class,
      'not null' => NotNullOperator::class,
      'notnull' => NotNullOperator::class,
      'between' => BetweenOperator::class,
      'like' => LikeOperator::class,
      'not like' => NotLikeOperator::class,
      'notlike' => NotLikeOperator::class,
      'rlike' => RlikeOperator::class,
      'regexp' => RlikeOperator::class,
      'not rlike' => NotRlikeOperator::class,
      'notrlike' => NotRlikeOperator::class,
      'not regexp' => NotRlikeOperator::class,
    ];
  }

  /**
   * Resolve operator string to an operator instance.
   *
   * @param string|null $operator
   * @return OperatorInterface
   */
  public static function resolve(string|null $operator): OperatorInterface
  {
    $key = strtolower(trim((string) $operator));

    // allow configuration overrides
    $map = self::defaults();
    $config = config('filterable.operators', []);

    if (is_array($config) && count($config) > 0) {
      // config keys are operator strings, values are class names
      $map = array_merge($map, $config);
    }

    $class = $map[$key] ?? EqualsOperator::class;

    return new $class();
  }
}
