<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Engines\Foundation\Resolvers\RelationResolver;
use Kettasoft\Filterable\Support\Payload;

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
   * Clause constructor.
   * 
   * @param Payload $payload
   */
  public function __construct(Payload $payload)
  {
    $this->field = $payload->field;
    $this->operator = $payload->operator;
    $this->value = $payload->value;
  }

  /**
   * Create Clause instance.
   * 
   * @param Payload $payload
   * @return Clause
   */
  public static function make(Payload $payload)
  {
    return new self($payload);
  }

  /**
   * @inheritDoc
   */
  public function isRelational(): bool
  {
    return is_string($this->field) && str_contains($this->field, '.');
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

  /**
   * Get the Payload instance.
   * 
   * @return Payload
   */
  public function getPayload(): Payload
  {
    return $this->payload;
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
