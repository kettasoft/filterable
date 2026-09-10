<?php

namespace Kettasoft\Filterable\Operations;

use Kettasoft\Filterable\Operations\Contracts\Operation;

/**
 * A backend-independent field comparison.
 */
class Comparison implements Operation
{
    /**
     * Create a new Comparison instance.
     * 
     * @param string $field
     * @param string $operator
     * @param mixed $value
     */
    public function __construct(
        private string $field,
        private string $operator,
        private mixed $value,
    ) {}

    public function field(): string
    {
        return $this->field;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
