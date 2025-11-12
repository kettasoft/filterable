<?php

namespace Kettasoft\Filterable\Tests\Feature\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Tests\Models\Post;

class FilterableLifecycleHooksTest extends TestCase
{
    public function test_it_can_trigger_before_filtering_hook()
    {
        $filter = new class extends Filterable {
            protected function initially(Builder $builder): Builder
            {
                return $builder->where('id', '>', 10);
            }
        };

        $invoker = Post::filter($filter);

        $this->assertStringContainsString('where "id" > ?', $invoker->toSql());
    }

    public function test_it_can_trigger_after_filtering_hook()
    {
        $filter = new class extends Filterable {
            protected function finally(Builder $builder): Builder
            {
                return $builder->where('id', '>', 10);
            }
        };

        $invoker = Post::filter($filter);

        $this->assertStringContainsString('where "id" > ?', $invoker->toSql());
    }

    public function test_it_can_trigger_initially_with_finally_hook()
    {
        $filter = new class extends Filterable {
            protected function initially(Builder $builder): Builder
            {
                return $builder->where('id', '>', 10);
            }

            protected function finally(Builder $builder): Builder
            {
                return $builder->where('status', '=', 'published');
            }
        };

        $invoker = Post::filter($filter);

        $this->assertStringContainsString('where "id" > ? and "status" = ?', $invoker->toSql());
    }

    public function test_it_can_trigger_initially_with_request_filters_and_finally_hook()
    {
        $filter = new class extends Filterable {
            protected $filters = ['title'];
            protected function initially(Builder $builder): Builder
            {
                return $builder->where('id', '>', 10);
            }

            protected function finally(Builder $builder): Builder
            {
                return $builder->where('status', '=', 'published');
            }

            public function title($payload)
            {
                return $this->builder->where('title', '=', $payload->value);
            }
        };

        // Simulate request filters
        $this->app['request']->query->set('title', 'Test Post');

        $invoker = Post::filter($filter);

        $this->assertStringContainsString('where "id" > ? and "title" = ? and "status" = ?', $invoker->toSql());
    }
}
