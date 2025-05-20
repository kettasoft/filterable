<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableHelpersTest extends TestCase
{
  /**
   * It can auto detect filterable fields from model fillable property.
   * @test
   */
  public function it_can_auto_detect_filterable_fields_from_model_fillable_property()
  {
    $filter = Filterable::create()
      ->setBuilder(Post::query())
      ->autoSetAllowedFieldsFromModel();

    $this->assertEquals((new Post)->getFillable(), $filter->getAllowedFields());
  }
}
