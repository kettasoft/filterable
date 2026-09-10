<?php

namespace Kettasoft\Filterable\Tests\Unit\Drivers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Drivers\DatabaseDriver;
use Kettasoft\Filterable\Engines\Foundation\Operators\Contracts\Operator;
use Kettasoft\Filterable\Engines\Foundation\Operators\OperatorResolver;
use Kettasoft\Filterable\Exceptions\InvalidDriverTargetException;
use Kettasoft\Filterable\Exceptions\UnsupportedOperationException;
use Kettasoft\Filterable\Operations\Comparison;
use Kettasoft\Filterable\Operations\Contracts\Operation;
use Kettasoft\Filterable\Operations\Group;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\TestCase;

class DatabaseDriverTest extends TestCase
{

    private function driver(): DatabaseDriver
    {
        return new DatabaseDriver();
    }

    public function test_it_applies_a_comparison_operation(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Comparison('views', '>=', 20), Post::query());

        $this->assertSame([20, 30], $query->pluck('views')->sort()->values()->all());
    }

    public function test_it_applies_special_operator_strategies(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Comparison('views', 'in', [10, 30]), Post::query());

        $this->assertSame([10, 30], $query->pluck('views')->sort()->values()->all());
    }

    public function test_it_applies_relational_comparisons(): void
    {
        [$first, $second] = $this->seedPosts();
        Tag::factory()->create(['post_id' => $first->id, 'name' => 'featured']);
        Tag::factory()->create(['post_id' => $second->id, 'name' => 'archived']);

        $query = $this->driver()->apply(
            new Comparison('tags.name', '=', 'featured'),
            Post::query()
        );

        $this->assertSame([$first->id], $query->pluck('id')->all());
    }

    public function test_it_applies_and_groups(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Group('and', [
            new Comparison('status', '=', 'pending'),
            new Comparison('views', '>=', 20),
        ]), Post::query());

        $this->assertSame([20], $query->pluck('views')->all());
    }

    public function test_it_applies_or_groups(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Group('or', [
            new Comparison('status', '=', 'active'),
            new Comparison('views', '>=', 30),
        ]), Post::query());

        $this->assertSame([10, 30], $query->pluck('views')->sort()->values()->all());
    }

    public function test_it_applies_nested_groups(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Group('and', [
            new Group('or', [
                new Comparison('status', '=', 'active'),
                new Comparison('status', '=', 'pending'),
            ]),
            new Comparison('views', '>=', 20),
        ]), Post::query());

        $this->assertSame([20], $query->pluck('views')->all());
    }

    public function test_an_empty_group_does_not_change_the_query(): void
    {
        $this->seedPosts();

        $query = $this->driver()->apply(new Group('and', []), Post::query());

        $this->assertCount(3, $query->get());
    }

    public function test_it_uses_injected_operator_strategies(): void
    {
        $this->seedPosts();
        $driver = new DatabaseDriver(new OperatorResolver([
            'starts with' => StartsWithOperator::class,
        ]));

        $query = $driver->apply(new Comparison('title', 'starts with', 'Sec'), Post::query());

        $this->assertSame(['Second'], $query->pluck('title')->all());
    }

    public function test_a_container_resolved_driver_uses_configured_operator_strategies(): void
    {
        $this->seedPosts();
        config()->set('filterable.operator_strategies.starts_with', StartsWithOperator::class);

        $driver = app(DatabaseDriver::class);
        $query = $driver->apply(new Comparison('title', 'starts_with', 'Sec'), Post::query());

        $this->assertSame(['Second'], $query->pluck('title')->all());
    }

    public function test_it_rejects_a_non_eloquent_target(): void
    {
        $this->expectException(InvalidDriverTargetException::class);

        $this->driver()->apply(new Comparison('views', '>=', 20), new \stdClass());
    }

    public function test_it_rejects_an_unsupported_operation(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        $operation = new class implements Operation {};

        $this->driver()->apply($operation, Post::query());
    }

    private function seedPosts(): array
    {
        return [
            Post::factory()->create(['title' => 'First', 'status' => 'active', 'views' => 10]),
            Post::factory()->create(['title' => 'Second', 'status' => 'pending', 'views' => 20]),
            Post::factory()->create(['title' => 'Third', 'status' => 'stopped', 'views' => 30]),
        ];
    }
}

class StartsWithOperator implements Operator
{
    public function apply(Builder $builder, Payload $payload): Builder
    {
        return $builder->where($payload->field, 'like', $payload->value . '%');
    }
}
