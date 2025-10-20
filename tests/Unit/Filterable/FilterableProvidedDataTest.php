<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;

class FilterableProvidedDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Post::factory(5)->create();

        Filterable::provide([
            "post_id" => 1,
        ]);
    }

    public function test_it_can_filter_by_provided_data(): void
    {
        $filterable = Filterable::tap(function (Filterable $f) {
            $f->setBuilder(Post::query());
            $f->getBuilder()->where("id", $f->provided("post_id"));
        });

        $this->assertStringContainsString('where "id" = 1', $filterable->filter()->toRawSql());
        $this->assertEquals(1, $filterable->filter()->count());
        $this->assertEquals(1, $filterable->provided("post_id"));
    }
}
