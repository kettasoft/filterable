<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableHelperTest extends TestCase
{
  /**
   * It can creates filterable instance.
   * @test
   */
  public function it_can_creates_filterable_instance()
  {
    $query = Post::query();
    $input = ['status' => 'pending'];
    $filter = filterable(request());

    $filter->setData($input)->setBuilder($query);

    $this->assertInstanceOf(Filterable::class, $filter);
    $this->assertSame($query, $filter->getBuilder());
    $this->assertSame($input, $filter->getData());
  }
}
