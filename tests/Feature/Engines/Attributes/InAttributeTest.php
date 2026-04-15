<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\In;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class InAttributeTest extends TestCase
{
    public function test_in_attribute_allows_value_in_allowed_set()
    {
        request()->merge([
            'status' => 'allowedValue',
        ]);
        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[In('allowedValue', 'anotherAllowedValue')]
            public function status(Payload $payload)
            {
                $this->builder->where('name', '=', $payload);
            }
        };

        $sql = 'select * from "posts" where "name" = \'allowedValue\'';

        $this->assertStringContainsString($sql, Post::filter($class)->toRawSql());
    }

    public function test_in_attribute_throws_exception_for_value_not_in_allowed_set()
    {
        request()->merge([
            'status' => 'stopped',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[In('pending', 'approved', 'rejected')]
            public function status(Payload $payload)
            {
                $this->builder->where('name', '=', $payload);
            }
        };

        $sql = 'select * from "posts"';

        $this->assertStringContainsString($sql, Post::filter($class)->toRawSql());
    }
}
