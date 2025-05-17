<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Validation\UnauthorizedException;

class FilterAuthorizationTest extends TestCase
{
  /**
   * It cant filtering without authorization.
   * @test
   */
  public function it_cant_filtering_without_authorization()
  {
    $class = new class extends Filterable {
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
