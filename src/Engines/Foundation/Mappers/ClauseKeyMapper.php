<?php

namespace Kettasoft\Filterable\Engines\Foundation\Mappers;

/**
 * Responsible for mapping and validating clause keys (field, operator, value).
 */
class ClauseKeyMapper
{
  protected readonly string $fieldKey;
  protected readonly string $operatorKey;
  protected readonly string $valueKey;
  protected readonly array $keys;
  protected array $defaults = [
    'field' => 'field',
    'operator' => 'operator',
    'value' => 'value',
  ];

  /**
   * ClauseKeyMapper constructor
   * @param array $keys
   */
  public function __construct(array $keys = [])
  {
    $keys = array_merge(config('filterable.clause_keys', $this->defaults), $keys);

    $this->validateKeys($keys);

    $this->fieldKey = $keys['field'];
    $this->operatorKey = $keys['operator'];
    $this->valueKey = $keys['value'];
    $this->keys = $keys;
  }

  /**
   * Get the key for the field name.
   * @return string
   */
  public function field(): string
  {
    return $this->fieldKey;
  }

  /**
   * Get the key for the operator.
   * @return string
   */
  public function operator(): string
  {
    return $this->operatorKey;
  }

  /**
   * Get the key for the value.
   * @return string
   */
  public function value(): string
  {
    return $this->valueKey;
  }

  /**
   * Get the full keys.
   * @return array
   */
  public function all(): array
  {
    return $this->keys;
  }

  /**
   * Validate that custom keys are unique.
   * @param array $keys
   * @throws \InvalidArgumentException
   */
  protected function validateKeys(array $keys)
  {
    $values = array_values($keys);
    $unique = array_unique($values);

    if (count($values) !== count($unique)) {
      $dublicates = array_diff_key($values, $unique);

      throw new \InvalidArgumentException(sprintf("Custom clause keys must be unique. Conflict in: %s", implode(', ', array_keys($dublicates))));
    }
  }
}
