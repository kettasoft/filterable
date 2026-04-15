<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Validation\UnauthorizedException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterAuthorizationTest extends TestCase
{
    /**
     * It cant filtering without authorization.
     *
     * @test
     */
    public function it_cant_filtering_without_authorization()
    {
        $class = new class() extends Filterable {
            public function authorize(): bool
            {
                return false;
            }
        };

        $this->assertThrows(function () use ($class) {
            Post::filter($class);
        }, UnauthorizedException::class);
    }
}
