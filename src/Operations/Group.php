<?php

namespace Kettasoft\Filterable\Operations;

use InvalidArgumentException;
use Kettasoft\Filterable\Operations\Contracts\Operation;

/**
 * A logical group of filtering operations.
 */
class Group implements Operation
{
    /**
     * The boolean operator for the group.
     * 
     * @var list<Operation>
     */
    private array $operations;

    /**
     * Create a new operation group.
     * 
     * @param iterable<Operation> $operations
     */
    public function __construct(private string $boolean, iterable $operations)
    {
        $this->boolean = strtolower(trim($this->boolean));

        if (! in_array($this->boolean, ['and', 'or'], true)) {
            throw new InvalidArgumentException('An operation group boolean must be [and] or [or].');
        }

        $this->operations = [];

        foreach ($operations as $operation) {
            if (! $operation instanceof Operation) {
                throw new InvalidArgumentException('Every group member must implement the Operation contract.');
            }

            $this->operations[] = $operation;
        }
    }

    public function boolean(): string
    {
        return $this->boolean;
    }

    /**
     * @return list<Operation>
     */
    public function operations(): array
    {
        return $this->operations;
    }
}
