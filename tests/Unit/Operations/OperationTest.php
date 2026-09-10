<?php

namespace Kettasoft\Filterable\Tests\Unit\Operations;

use InvalidArgumentException;
use Kettasoft\Filterable\Operations\Comparison;
use Kettasoft\Filterable\Operations\Group;
use PHPUnit\Framework\TestCase;

class OperationTest extends TestCase
{
    public function test_comparison_exposes_backend_independent_parts(): void
    {
        $operation = new Comparison('views', '>=', 100);

        $this->assertSame('views', $operation->field());
        $this->assertSame('>=', $operation->operator());
        $this->assertSame(100, $operation->value());
    }

    public function test_group_normalizes_its_boolean_and_preserves_operations(): void
    {
        $comparison = new Comparison('status', '=', 'active');
        $group = new Group(' OR ', [$comparison]);

        $this->assertSame('or', $group->boolean());
        $this->assertSame([$comparison], $group->operations());
    }

    public function test_group_rejects_an_unknown_boolean(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Group('xor', []);
    }

    public function test_group_rejects_non_operation_members(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Group('and', ['not-an-operation']);
    }
}
