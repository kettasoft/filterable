<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedFieldException;
use Kettasoft\Filterable\Engines\Exceptions\NotAllowedEmptyValueException;

/**
 * Validate and resolve payloads before they are applied.
 */
class PayloadFactory
{
  public function __construct(protected Engine $engine) {}

  /**
   * Validate and resolve the given payload.
   */
  public function make(Payload $payload): Payload
  {
    $this->validateField($payload);
    $this->validateOperator($payload);
    $this->validateValue($payload);

    return $payload
      ->setField($this->resolveField($payload))
      ->setOperator($this->resolveOperator($payload));
  }

  protected function validateField(Payload $payload): void
  {
    $field = $payload->field;
    $isWildcardAllowed = ($this->engine->getAllowedFields()[0] ?? false) === '*';

    if (!(in_array($field, $this->engine->getAllowedFields(), true) || $this->isRelational($field) || $isWildcardAllowed)) {
      throw new NotAllowedFieldException($field, $payload);
    }
  }

  protected function validateOperator(Payload $payload): bool
  {
    $operator = $payload->operator;

    if (! array_key_exists($operator, $this->engine->allowedOperators()) && $this->engine->isStrict()) {
      throw new InvalidOperatorException($operator, $payload);
    }

    return (bool) $this->engine->defaultOperator();
  }

  protected function validateValue(Payload $payload): void
  {
    if ($this->engine->isIgnoredEmptyValues() && $payload->isEmpty()) {
      throw new NotAllowedEmptyValueException('Empty values are not allowed.', $payload);
    }
  }

  protected function resolveField(Payload $payload): string
  {
    return $this->engine->getFieldsMap()[$payload->field] ?? $payload->field;
  }

  protected function resolveOperator(Payload $payload): string
  {
    return $this->engine->allowedOperators()[$payload->operator]
      ?? Operators::fromString($this->engine->defaultOperator());
  }

  protected function isRelational(string $field): bool
  {
    return $this->engine->getContext()->hasRelationPath($field);
  }
}
