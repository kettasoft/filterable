<?php

namespace Kettasoft\Filterable\Tests\Feature\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

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
        $this->assertEquals('status', $filterable->applied('status')->field);
    }
}
