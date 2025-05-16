<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @template TKey of array-key
 * @template TValue
 */
readonly class Payload implements \Stringable, Arrayable, Jsonable
{
  /**
   * Request field.
   * @var string
   */
  public string $field;

  /**
   * Requested operator.
   * @var string
   */
  public string $operator;

  /**
   * Request value.
   * @var mixed
   */
  public mixed $value;

  /**
   * Value before sanitizing.
   * @var mixed
   */
  public mixed $beforeSanitize;

  /**
   * Create new Payload instance.
   * @param string $field
   * @param string $operator
   * @param mixed $value
   * @param mixed $beforeSanitize
   */
  public function __construct(string $field, string $operator, mixed $value, mixed $beforeSanitize)
  {
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
    $this->beforeSanitize = $beforeSanitize;
  }

  /**
   * Shortcut to create Payload instance.
   * @param mixed $field
   * @param mixed $operator
   * @param mixed $value
   * @param mixed $beforeSanitize
   * @return Payload
   */
  public static function create($field, $operator, $value, $beforeSanitize): static
  {
    return new static($field, $operator, $value, $beforeSanitize);
  }

  /**
   * Get the instance as an array.
   *
   * @return array<TKey, TValue>
   */
  public function toArray()
  {
    return [
      'field' => $this->field,
      'operator' => $this->operator,
      'value' => $this->value,
      'beforeSanitize' => $this->beforeSanitize,
    ];
  }

  /**
   * Convert the object to its JSON representation.
   *
   * @param  int  $options
   * @return string
   */
  public function toJson($options = 0)
  {
    return json_encode($this->toArray(), $options);
  }

  /**
   * Return request value on read class as a string.
   */
  public function __tostring()
  {
    return $this->value;
  }
}
