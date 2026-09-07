<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedEmptyValueException;
use Kettasoft\Filterable\Engines\Foundation\Operators\OperatorResolver;

/**
 * Validate and resolve payloads before they are applied.
 */
class PayloadFactory
{
  /**
   * Create a new PayloadFactory instance.
   *
   * @param Engine $engine The engine instance to use for validation and resolution.
   */
  public function __construct(protected Engine $engine) {}

  /**
   * Validate and resolve the given payload.
   * 
   * @param Payload $payload The payload to validate and resolve.
   * @return Payload The validated and resolved payload.
   * @throws NotAllowedFieldException If the field is not allowed.
   * @throws InvalidOperatorException If the operator is not allowed and strict mode is enabled.
   * @throws NotAllowedEmptyValueException If empty values are not allowed and the payload is
   */
  public function make(Payload $payload): Payload
  {
    $this->validateField($payload);
    $this->validateOperator($payload);

    $payload
      ->setField($this->resolveField($payload))
      ->setOperator($this->resolveOperator($payload));

    $this->validateValue($payload);

    return $payload;
  }

  /**
   * Validate the field of the payload.
   *
   * @param Payload $payload The payload containing the field to validate.
   * @throws NotAllowedFieldException If the field is not allowed.
   */
  protected function validateField(Payload $payload): void
  {
    $field = $payload->field;
    $allowedFields = $this->engine->getAllowedFields();

    if (in_array('*', $allowedFields, true)) {
      return;
    }

    if (!(in_array($field, $allowedFields, true) || $this->isRelational($field))) {
      throw new NotAllowedFieldException($field, $payload);
    }
  }

  /**
   * Validate the operator of the payload.
   *
   * @param Payload $payload The payload containing the operator to validate.
   * @return bool True if the operator is valid, false otherwise.
   * @throws InvalidOperatorException If the operator is not allowed and strict mode is enabled.
   */
  protected function validateOperator(Payload $payload): bool
  {
    $operator = $payload->operator;
    $allowedOperators = $this->engine->allowedOperators();
    $isAllowed = array_key_exists($operator, $allowedOperators)
      || in_array(OperatorResolver::normalize($operator), array_map(
        [OperatorResolver::class, 'normalize'],
        array_values($allowedOperators)
      ), true);

    if (! $isAllowed && $this->engine->isStrict()) {
      throw new InvalidOperatorException($operator, $payload);
    }

    return (bool) $this->engine->defaultOperator();
  }

  /**
   * Validate the value of the payload.
   *
   * @throws NotAllowedEmptyValueException if empty values are not allowed and the payload is empty.
   */
  protected function validateValue(Payload $payload): void
  {
    if (in_array(OperatorResolver::normalize($payload->operator), ['is null', 'is not null'], true)) {
      return;
    }

    if ($this->engine->isIgnoredEmptyValues() && $payload->isEmpty()) {
      throw new NotAllowedEmptyValueException('Empty values are not allowed.', $payload);
    }
  }

  /**
   * Resolve the field name based on the engine's field mapping.
   * 
   * @param Payload $payload The payload containing the field to resolve.
   * @return string The resolved field name.
   */
  protected function resolveField(Payload $payload): string
  {
    return $this->engine->getFieldsMap()[$payload->field] ?? $payload->field;
  }

  /**
   * Resolve the operator based on the engine's allowed operators.
   * 
   * @param Payload $payload The payload containing the operator to resolve.
   * @return string The resolved operator.
   */
  protected function resolveOperator(Payload $payload): string
  {
    $allowedOperators = $this->engine->allowedOperators();

    if (array_key_exists($payload->operator, $allowedOperators)) {
      return $allowedOperators[$payload->operator];
    }

    foreach ($allowedOperators as $operator) {
      if (OperatorResolver::normalize($operator) === OperatorResolver::normalize($payload->operator)) {
        return $operator;
      }
    }

    return Operators::fromString($this->engine->defaultOperator());
  }

  /**
   * Determines if the given field is a relational field.
   *
   * @param string $field The field to check.
   * @return bool True if the field is relational, false otherwise.
   */
  protected function isRelational(string $field): bool
  {
    return $this->engine->getContext()->hasRelationPath($field);
  }
}
