<?php

namespace Kettasoft\Filterable\Operations;

use Kettasoft\Filterable\Operations\Contracts\Operation;

/**
 * A backend-independent field comparison.
 */
class Comparison implements Operation
{
    /**
     * Create a backend-independent field comparison.
     *
     * @param string $field Logical field or dotted relationship path.
     * @param string $operator Canonical operator understood by a driver.
     * @param mixed $value Final value to compare after input processing.
     */
    public function __construct(
        private string $field,
        private string $operator,
        private mixed $value,
    ) {}

    /**
     * Get the logical field or relationship path being compared.
     *
     * @return string
     */
    public function field(): string
    {
        return $this->field;
    }

    /**
     * Get the canonical comparison operator.
     *
     * @return string
     */
    public function operator(): string
    {
        return $this->operator;
    }

    /**
     * Get the final comparison value.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->value;
    }
}
