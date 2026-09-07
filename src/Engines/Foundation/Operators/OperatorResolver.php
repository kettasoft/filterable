<?php

namespace Kettasoft\Filterable\Engines\Foundation\Operators;

use Kettasoft\Filterable\Exceptions\InvalidOperatorDefinitionException;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;

final class OperatorResolver
{
  /**
   * @var array<string, class-string<Operator>>
   */
  private array $operators;

  /**
   * @param array<string, class-string<Operator>> $operators
   */
  public function __construct(array $operators = [])
  {
    $this->operators = array_replace($this->defaults(), $this->normalizeKeys($operators));
  }

  public static function fromConfig(): self
  {
    $operators = config('filterable.operator_strategies', []);

    return new self(is_array($operators) ? $operators : []);
  }

  public function resolve(string $operator): Operator
  {
    $definition = $this->operators[self::normalize($operator)] ?? ComparisonOperator::class;

    if (! is_string($definition) || ! is_a($definition, Operator::class, true)) {
      throw new InvalidOperatorDefinitionException($operator, $definition);
    }

    try {
      $instance = app($definition);
    } catch (\Throwable) {
      throw new InvalidOperatorDefinitionException($operator, $definition);
    }

    if (! $instance instanceof Operator) {
      throw new InvalidOperatorDefinitionException($operator, $instance);
    }

    return $instance;
  }

  public static function normalize(string $operator): string
  {
    $operator = strtolower(trim(str_replace('_', ' ', $operator)));

    return preg_replace('/\s+/', ' ', $operator) ?? $operator;
  }

  /**
   * @return array<string, class-string<Operator>>
   */
  private function defaults(): array
  {
    return [
      'in' => InOperator::class,
      'not in' => InOperator::class,
      'between' => BetweenOperator::class,
      'not between' => BetweenOperator::class,
      'is null' => NullOperator::class,
      'is not null' => NullOperator::class,
    ];
  }

  /**
   * @param array<string, class-string<Operator>> $operators
   * @return array<string, class-string<Operator>>
   */
  private function normalizeKeys(array $operators): array
  {
    $normalized = [];

    foreach ($operators as $operator => $definition) {
      $normalized[self::normalize((string) $operator)] = $definition;
    }

    return $normalized;
  }
}
