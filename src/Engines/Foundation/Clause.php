<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Engines\Foundation\Resolvers\RelationResolver;

class Clause implements Arrayable, Jsonable
{

  /**
   * Original field.
   * @var string
   */
  public readonly string $field;

  /**
   * Original operator.
   * @var string
   */
  public readonly string|null $operator;

  /**
   * Original value.
   * @var string
   */
  public readonly string|null $value;

  /**
   * Check if the clause is validated.
   * @var bool
   */
  public bool $validated = false;

  /**
   * Clause constructor.
   * @param \Kettasoft\Filterable\Foundation\Resources $resources
   * @param mixed $field
   * @param mixed $dissector
   */
  public function __construct($field, $operator, $value)
  {
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
  }

  /**
   * Create Dissector instance.
   * @param mixed $field
   * @param mixed $dissector
   * @return Clause
   */
  public static function make($field, $operator, $value)
  {
    return new self($field, $operator, $value);
  }

  /**
   * @inheritDoc
   */
  public function isRelational(): bool
  {
    return is_string($this->field) && str_contains($this->field, '.');
  }

  public function setStatus(bool $status)
  {
    $this->validated = $status;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getOriginalField()
  {
    return $this->field;
  }

  /**
   * @inheritDoc
   */
  public function getValue()
  {
    return $this->value;
  }

  public function relation($bag)
  {
    $instance = new RelationResolver($bag, $this->field);
    return $instance;
  }

  public function apply(Builder $builder)
  {
    return $builder->where(
      $this->field,
      $this->operator,
      $this->value
    );
  }

  /**
   * @inheritDoc
   */
  public function toArray(): array
  {
    return [
      'field' => $this->field,
      'operator' => $this->operator,
      'value' => $this->value
    ];
  }

  /**
   * Convert the object to its JSON representation.
   * @param mixed $options
   * @return bool|string
   */
  public function toJson($options = 0)
  {
    return json_encode($this->toArray(), $options);
  }

  public function toCollection(): Collection
  {
    return new Collection($this->toArray());
  }
}
