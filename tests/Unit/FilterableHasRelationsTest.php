<?php

namespace Kettasoft\Filterable\Tests\Unit;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;

class FilterableHasRelationsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Post::factory()->create();
        Tag::factory(5)->create(['post_id' => 1]);
        Tag::factory(5)->create(['post_id' => 1, 'name' => 'archived']);
    }
    public function test_it_can_filter_using_has_relation()
    {
        // HasMany relation
        $query = Post::find(1)->tags();

        $result = Filterable::tap(function (Filterable $filterable) use ($query) {
            $filterable->setBuilder($query);
            $filterable->setData([
                'name' => 'archived',
            ]);

            $filterable->useEngine('ruleset');

            $filterable->setAllowedFields([
                'name'
            ]);
        })->apply();

        $this->assertEquals(5, $result->count());
    }
}
