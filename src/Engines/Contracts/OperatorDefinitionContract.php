<?php

namespace Kettasoft\Filterable\Engines\Contracts;

use Kettasoft\Filterable\Foundation\Bags\OperatorBag;

interface OperatorDefinitionContract
{
  /**
   * OperatorDefinition constructor.
   * @param \Kettasoft\Filterable\Foundation\Bags\OperatorBag $bag
   */
  public function __construct(OperatorBag $bag, bool $isStrict);

  /**
   * Check if the operator is allowed.
   * @param string $operator
   * @return bool
   */
  public function isAllowed(string $operator): bool;

  /**
   * Resolve operator its SQL equivalent.
   * @param string|null $operator
   * @throws \Kettasoft\Filterable\Exceptions\InvalidOperatorException
   * @return string
   */
  public function resolve(string|null $operator = null): string|null;

  /**
   * Get all defined operators.
   * @return array
   */
  public function all(): array|string|null;
}
