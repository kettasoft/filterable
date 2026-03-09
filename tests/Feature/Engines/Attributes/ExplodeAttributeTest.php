<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Explode;
use Kettasoft\Filterable\Exceptions\StrictnessException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class ExplodeAttributeTest extends TestCase
{
  public function test_explode_attribute_splits_string_into_array()
  {
    request()->merge([
      'tags' => 'php,laravel,testing',
    ]);

    $class = new class extends Filterable {
      protected $filters = ['tags'];

      #[Explode(',')]
      public function tags(Payload $payload)
      {
        $this->builder->whereIn('tags', $payload->value);
      }
    };

    $sql = Post::filter($class)->toRawSql();

    $this->assertStringContainsString('where "tags" in', $sql);
    $this->assertStringContainsString('php', $sql);
    $this->assertStringContainsString('laravel', $sql);
    $this->assertStringContainsString('testing', $sql);
  }
}
