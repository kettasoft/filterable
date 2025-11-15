<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedEmptyValueException;

/**
 * Class ClauseFactory
 *
 * Responsible for building {@see Clause} objects from {@see Payload},
 * including field/operator validation and resolution.
 *
 * @package Kettasoft\Filterable\Engines\Foundation
 */
class ClauseFactory
{
  /**
   * Create a new ClauseFactory instance.
   * @param \Kettasoft\Filterable\Engines\Foundation\Engine $engine
   */
  public function __construct(protected Engine $engine) {}

  /**
   * Build a Clause from the given Payload.
   *
   * @param  Payload  $payload
   * @return Clause
   *
   * @throws NotAllowedFieldException   If the field is not allowed and strict mode is enabled.
   * @throws InvalidOperatorException   If the operator is not allowed and strict mode is enabled.
   * @throws \InvalidArgumentException  If the value is empty and strict mode is enabled.
   */
  public function make(Payload $payload): Clause
  {
    $this->validateField($payload);
    $this->validateOperator($payload);
    $this->validateValue($payload);

    $resolvedField    = $this->resolveField($payload);
    $resolvedOperator = $this->resolveOperator($payload);
    $payload->setField($resolvedField)->setOperator($resolvedOperator);

    return (new Clause($payload));
  }

  /**
   * Validate the payload field against allowed fields and relations.
   *
   * @param  Payload  $payload
   * @return void
   *
   * @throws NotAllowedFieldException
   */
  protected function validateField(Payload $payload): void
  {
    $field = $payload->field;
    // allow wildcard * as "all fields allowed"
    $isWildcardAllowed = ($this->engine->getAllowedFields()[0] ?? false) === '*';

    if (!(in_array($field, $this->engine->getAllowedFields(), true) || $this->isRelational($field) || $isWildcardAllowed)) {
      throw new NotAllowedFieldException($field);
    }

    return;
  }

  /**
   * Validate the payload operator against allowed operators.
   *
   * @param  Payload  $payload
   * @return bool
   *
   * @throws InvalidOperatorException
   */
  protected function validateOperator(Payload $payload): bool
  {
    $operator = $payload->operator;
    if (! array_key_exists($operator, $this->engine->allowedOperators()) && $this->engine->isStrict()) {

      throw new InvalidOperatorException($operator);
    }

    return (bool) $this->engine->defaultOperator();
  }

  /**
   * Validate that the payload value is not empty when ignoredEmptyValues is enabled.
   *
   * @param  Payload  $payload
   * @return void
   *
   * @throws NotAllowedEmptyValueException
   */
  protected function validateValue(Payload $payload): void
  {
    if ($this->engine->isIgnoredEmptyValues() && $payload->isEmpty()) {
      throw new NotAllowedEmptyValueException("Empty values are not allowed.");
    }

    return;
  }

  /* -----------------------------------------------------------------
     |  Resolution Methods
     | -----------------------------------------------------------------
     */

  /**
   * Resolve the final field name using fields map.
   *
   * @param  Payload  $payload
   * @return string
   */
  protected function resolveField(Payload $payload): string
  {
    return $this->engine->getFieldsMap()[$payload->field] ?? $payload->field;
  }

  /**
   * Resolve the final operator using allowed operators or default operator.
   *
   * @param  Payload  $payload
   * @return string
   */
  protected function resolveOperator(Payload $payload): string
  {
    return $this->engine->allowedOperators()[$payload->operator]
      ?? Operators::fromString($this->engine->defaultOperator());
  }

  /**
   * Determine if the given field is part of a relation path.
   *
   * @param  string  $field
   * @return bool
   */
  protected function isRelational(string $field): bool
  {
    return $this->engine->getContext()->hasRelationPath($field);
  }
}
