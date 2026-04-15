<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Authorize;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Feature\Engines\Attributes\Authorizations\CanMakeFilter;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class AuthorizeAttributeTest extends TestCase
{
    public function test_authorize_attribute_can_make_filter()
    {
        request()->merge([
            'tags' => 'testing',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['tags'];

            #[Authorize(CanMakeFilter::class)]
            public function tags(Payload $payload)
            {
                $this->builder->where('tags', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('where "tags"', $sql);
    }
}
