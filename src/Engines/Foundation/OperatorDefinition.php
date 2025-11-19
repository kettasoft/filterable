<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Support\Arr;
use Kettasoft\Filterable\Foundation\Bags\OperatorBag;
use Kettasoft\Filterable\Engines\Foundation\Enums\Operators;
use Kettasoft\Filterable\Engines\Exceptions\InvalidOperatorException;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;
use Kettasoft\Filterable\Engines\Contracts\OperatorDefinitionContract;

class OperatorDefinition implements OperatorDefinitionContract
{
  /**
   * @inheritDoc
   */
  public function __construct(protected OperatorBag $bag, protected bool $strict) {}

  /**
   * @inheritDoc
   */
  public function isAllowed(string $operator): bool
  {
    return $this->bag->has($operator);
  }

  /**
   * @inheritDoc
   */
  public function resolve(string|null $operator = null): string|null
  {
    $operator = strtolower($operator);

    if ($this->isAllowed($operator)) {
      return $this->bag->get($operator);
    }

    if ($this->strict) {
      $this->throw($operator);
    }

    return Operators::fromString($this->bag->default);
  }

  /**
   * Get all defined operators
   * @return array
   */
  public function all($key = null): array|string|null
  {
    if ($key) {
      return $this->bag->get($key);
    }

    return $this->bag->all();
  }

  /**
   * Throw InvalidOperatorException instance.
   * @param mixed $operator
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   * @return never
   */
  protected function throw($operator)
  {
    throw new InvalidOperatorException($operator);
  }
}
