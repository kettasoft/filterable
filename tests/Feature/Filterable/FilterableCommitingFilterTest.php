<?php

namespace Kettasoft\Filterable\Tests\Feature\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\MapValue;

class FilterableCommitingFilterTest extends TestCase
{
    public function test_it_saving_applied_filters()
    {
        request()->merge([
            'status' => 'active',
            'category' => 'news',
        ]);

        $filterable = new class extends Filterable {
            protected $filters = ['status', 'category'];
            public function status($payload)
            {
                return $this->builder->where('status', $payload->value);
            }
            public function category($payload)
            {
                return $this->builder->where('category', $payload->value);
            }
        };

        $filterable->apply(Post::query());

        $this->assertCount(2, $filterable->applied());
        $this->assertInstanceOf(Payload::class, $filterable->applied('status'));
        $this->assertEquals('status', $filterable->applied('status')->field);
        $this->assertEquals('active', $filterable->applied('status')->rawValue);
    }

    public function test_it_commits_the_final_transformed_payload_as_a_snapshot()
    {
        request()->merge(['status' => 'active']);

        $filterable = new class extends Filterable {
            protected $filters = ['status'];

            public ?Payload $receivedPayload = null;

            #[MapValue(['active' => 1])]
            public function status(Payload $payload)
            {
                $this->receivedPayload = $payload;

                return $this->builder->where('status', $payload->value);
            }
        };

        $filterable->apply(Post::query());

        $applied = $filterable->applied('status');

        $this->assertInstanceOf(Payload::class, $applied);
        $this->assertNotSame($filterable->receivedPayload, $applied);
        $this->assertSame(1, $applied->value);
        $this->assertSame('active', $applied->rawValue);

        $filterable->receivedPayload->setValue('changed-after-commit');

        $this->assertSame(1, $applied->value);
    }
}
