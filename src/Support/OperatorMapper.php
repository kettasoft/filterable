<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Support\Collection;
use Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators;
use Kettasoft\Filterable\Exceptions\InvalidOperatorException;

class OperatorMapper
{
  /**
   * Engine context
   * @var HasInteractsWithOperators
   */
  protected HasInteractsWithOperators $context;

  /**
   * OperatorMapper constructor.
   * @param \Kettasoft\Filterable\Engines\Contracts\HasInteractsWithOperators $context
   */
  public function __construct(HasInteractsWithOperators $context)
  {
    $this->context = $context;
  }

  /**
   * Get valid operator.
   * @param string|null $operator
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   */
  public function map(string|null $operator)
  {
    if ($operator === null) {
      return $this->context->isStrict() ? $this->throw($operator) : $this->default($operator);
    }

    $allowedOperators = $this->context->allowedOperators();
    $operators = $this->context->operators();

    $selectedOperators = empty($allowedOperators) ? $operators : array_intersect_key(
      $operators,
      array_flip($this->context->allowedOperators())
    );

    if (array_key_exists($operator, $selectedOperators)) {
      return $selectedOperators[$operator];
    }

    if ($this->context->isStrict()) {
      $this->throw($operator);
    }

    return $this->default($operator);
  }

  /**
   * Get default engine operator.
   * @param mixed $operator
   * @return string
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   */
  protected function default($operator)
  {
    return $this->context->defaultOperator() ?? $this->throw($operator);
  }

  /**
   * Throw invalid operator.
   * @param string $operator
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   * @return never
   */
  protected function throw(string $operator)
  {
    throw new InvalidOperatorException($operator);
  }
}
