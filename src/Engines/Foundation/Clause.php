<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Engines\Foundation\Mapper;
use Kettasoft\Filterable\Support\ValidateTableColumns;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Engines\Foundation\Mappers\OperatorMapper;
use Kettasoft\Filterable\Engines\Foundation\Resolvers\RelationResolver;

class Clause implements Arrayable, Jsonable
{
  /**
   * Current registered engine.
   * @var Resources
   */
  public Resources $resources;

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
   * Dissector instance.
   * @var Dissector
   */
  public readonly Dissector $dissector;

  /**
   * Strict mode.
   * @var bool
   */
  protected bool $strict = false;

  /**
   * Clause constructor.
   * @param \Kettasoft\Filterable\Foundation\Resources $resources
   * @param mixed $field
   * @param mixed $dissector
   */
  public function __construct(Resources $resources, $field, $dissector)
  {
    $this->resources = $resources;
    $this->field = $field;
    $this->dissector = Dissector::parse($dissector, 'eq');

    $this->operator = $this->dissector->operator;
    $this->value = $this->dissector->value;
  }

  /**
   * Create Dissector instance.
   * @param \Kettasoft\Filterable\Foundation\Resources $resources
   * @param mixed $field
   * @param mixed $dissector
   * @return Clause
   */
  public static function make(Resources $resources, $field, $dissector)
  {
    return new self($resources, $field, $dissector);
  }

  /**
   * @inheritDoc
   */
  public function isRelational(): bool
  {
    return is_string($this->field) && str_contains($this->field, '.');
  }

  public function isEmptyValue(): bool
  {
    return empty($this->value) || is_null($this->value);
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
    $sanitizers = $this->resources->sanitizers;

    $value = $this->getOriginalValue();

    if (count($sanitizers)) {
      foreach ($sanitizers as $sanitizer) {
        $value = $sanitizer->handle($this->getOriginalField(), $value);
      }
    }

    return $value;
  }

  /**
   * @inheritDoc
   */
  public function getOriginalValue()
  {
    return $this->dissector->value;
  }

  /**
   * @inheritDoc
   */
  public function getOperator()
  {
    $instance = $this->resources->operators;

    return Mapper::run(
      OperatorMapper::init($instance, $this->strict),
      $this->operator
    );
    // return $instance->get($this->operator, $instance->get($instance->default));
  }

  /**
   * @inheritDoc
   */
  public function getDatabaseColumnName()
  {
    return $this->resources->fieldMap->get($this->field, $this->field);
  }

  /**
   * Check if the field is allowed.
   * @return bool
   */
  public function isAllowedField(): bool
  {
    return $this->resources->fields->has($this->field);
  }

  public function validateTableColumn()
  {
    // return ValidateTableColumns::validate($this->engine->getContext()->getBuilder(), $this->getDatabaseColumnName());
  }

  public function strict($enable = true): self
  {
    $this->strict = $enable;
    return $this;
  }

  public function relation($bag)
  {
    $instance = new RelationResolver($bag, $this->field);
    return $instance;
  }

  /**
   * @inheritDoc
   */
  public function toArray(): array
  {
    return [
      'field' => $this->getDatabaseColumnName(),
      'operator' => $this->getOperator(),
      'value' => $this->getValue()
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
