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
     * Operations contained by the group.
     *
     * @var list<Operation>
     */
    private array $operations;

    /**
     * Create a logical group of backend-independent operations.
     *
     * @param string $boolean Boolean used to join members; accepts `and` or `or`.
     * @param iterable<Operation> $operations Operations contained by the group.
     *
     * @throws InvalidArgumentException When the boolean or a group member is invalid.
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

    /**
     * Get the normalized boolean used to join the group members.
     *
     * @return string Either `and` or `or`.
     */
    public function boolean(): string
    {
        return $this->boolean;
    }

    /**
     * Get the operations contained by this group in their original order.
     *
     * @return list<Operation> Ordered group members.
     */
    public function operations(): array
    {
        return $this->operations;
    }
}
