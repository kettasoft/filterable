<?php

namespace Kettasoft\Filterable\Engines\Foundation\Parsers;

/**
 * Dissector class for parsing raw filter values and operators.
 * 
 * This class is responsible for extracting and normalizing operator and value
 * pairs from various input formats (array, string, or scalar values).
 * 
 * @package Kettasoft\Filterable\Engines\Foundation\Parsers
 */
class Dissector
{
  /**
   * Parsed value.
   * @var mixed
   */
  public readonly mixed $value;

  /**
   * Parsed operator.
   * @var string|null
   */
  public readonly string|null $operator;

  /**
   * Parse the given raw input into operator and value components.
   * 
   * Supports multiple input formats:
   * - Array: ['operator' => 'eq', 'value' => 'some_value']
   * - String with delimiter: 'eq:some_value'
   * - String without delimiter: uses defaultOperator with the string as value
   * - Null: uses defaultOperator with null as value
   * - Other scalar values: uses defaultOperator with the value as-is
   *
   * @param mixed $raw The raw input to parse (array, string, or scalar)
   * @param mixed $defaultOperator The default operator to use when not specified
   * @return self A new Dissector instance with parsed operator and value
   */
  public static function parse(mixed $raw, mixed $defaultOperator): self
  {
    $instance = new self();
    [$operator, $value] = self::extractOperatorAndValue($raw, $defaultOperator);

    $instance->operator = $operator;
    $instance->value = $value;

    return $instance;
  }

  /**
   * Extract operator and value from various input formats.
   *
   * @param mixed $raw The raw input to extract from
   * @param mixed $defaultOperator The default operator to use as fallback
   * @return array A tuple of [operator, value]
   */
  protected static function extractOperatorAndValue(mixed $raw, mixed $defaultOperator): array
  {
    if (is_array($raw) && self::isValidHaystack($raw)) {
      return [$raw['operator'], $raw['value']];
    }

    if (is_string($raw) && str_contains($raw, ':')) {
      return explode(':', $raw, 2);
    }

    return [$defaultOperator, $raw];
  }

  /**
   * Validate if an array contains the required 'operator' and 'value' keys.
   *
   * @param array $haystack The array to validate
   * @return bool True if the array has both 'operator' and 'value' keys, false otherwise
   */
  protected static function isValidHaystack(array $haystack): bool
  {
    return array_key_exists('operator', $haystack) && array_key_exists('value', $haystack);
  }
}
