<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Exceptions\NotAllowedFieldException;

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
    $valid = true;

    $valid = $this->validateField($payload) && $valid;
    $valid = $this->validateOperator($payload) && $valid;
    $valid = $this->validateValue($payload) && $valid;

    $resolvedField    = $this->resolveField($payload);
    $resolvedOperator = $this->resolveOperator($payload);
    $payload->setField($resolvedField)->setOperator($resolvedOperator);

    return (new Clause($payload))
      ->setStatus($valid);
  }

  /**
   * Validate the payload field against allowed fields and relations.
   *
   * @param  Payload  $payload
   * @return bool
   *
   * @throws NotAllowedFieldException
   */
  protected function validateField(Payload $payload): bool
  {
    $field = $payload->field;

    if (in_array($field, $this->engine->getAllowedFields(), true) || $this->isRelational($field)) {
      return true;
    }

    // allow wildcard * as "all fields allowed"
    $isWildcardAllowed = ($this->engine->getAllowedFields()[0] ?? false) === '*';

    if ($this->engine->isStrict() && !$isWildcardAllowed) {
      throw new NotAllowedFieldException($field);
    }

    return $isWildcardAllowed;
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

    if (array_key_exists($operator, $this->engine->allowedOperators())) {
      return true;
    }

    if ($this->engine->isStrict()) {
      throw new InvalidOperatorException($operator);
    }

    return (bool) $this->engine->defaultOperator();
  }

  /**
   * Validate that the payload value is not empty when ignoredEmptyValues is enabled.
   *
   * @param  Payload  $payload
   * @return bool
   *
   * @throws \InvalidArgumentException
   */
  protected function validateValue(Payload $payload): bool
  {
    if ($this->engine->isIgnoredEmptyValues() && $payload->isEmpty()) {
      if ($this->engine->isStrict()) {
        throw new \InvalidArgumentException("Empty values are not allowed.");
      }
      return false;
    }
    return true;
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
