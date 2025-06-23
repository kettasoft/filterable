<?php

namespace Kettasoft\Filterable\Engines\Foundation\Parsers;

class Dissector
{
  /**
   * Parsed value.
   * @var mixed
   */
  public readonly mixed $value;

  /**
   * Parsed operator.
   * @var string
   */
  public readonly string|null $operator;

  /**
   * Parse the given raw.
   * @param string|array $raw
   * @param mixed $defaultOperator
   * @return Dissector
   */
  public static function parse(string|array $raw, $defaultOperator): self
  {
    $instance = new self();


    if (is_array($raw) && self::isValidHaystack($raw)) {
      [$operator, $value] = [$raw['operator'], $raw['value']];
    }

    if (is_string($raw) && str_contains($raw, ':')) {
      [$operator, $value] = explode(':', $raw, 2);
    } elseif (is_string($raw)) {
      [$operator, $value] = [$defaultOperator, $raw];
    }

    $instance->operator = $operator;
    $instance->value = $value;

    return $instance;
  }

  protected static function isValidHaystack(array $haystack)
  {
    return array_key_exists('operator', $haystack) && array_key_exists('value', $haystack);
  }
}
