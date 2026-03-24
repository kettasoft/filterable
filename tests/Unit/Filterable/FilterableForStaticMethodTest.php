<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableForStaticMethodTest extends TestCase
{
  /**
   * Filterable::for() returns a Filterable instance with the model set.
   */
  public function test_for_returns_filterable_instance_with_model_class()
  {
    $filter = Filterable::for(Post::class);

    $this->assertInstanceOf(Filterable::class, $filter);
    $this->assertSame(Post::class, $filter->getModel());
  }

  /**
   * Filterable::for() works with a model instance.
   */
  public function test_for_returns_filterable_instance_with_model_instance()
  {
    $post = new Post;
    $filter = Filterable::for($post);

    $this->assertInstanceOf(Filterable::class, $filter);
    $this->assertSame($post, $filter->getModel());
  }

  /**
   * Filterable::for() accepts a custom request.
   */
  public function test_for_accepts_custom_request()
  {
    $request = Request::create('/', 'GET', ['status' => 'active']);

    $filter = Filterable::for(Post::class, $request);

    $this->assertInstanceOf(Filterable::class, $filter);
    $this->assertSame($request, $filter->getRequest());
    $this->assertSame(Post::class, $filter->getModel());
  }

  /**
   * Filterable::for() can apply filters and resolve a query builder.
   */
  public function test_for_can_apply_and_return_builder()
  {
    $result = Filterable::for(Post::class)->apply();

    $this->assertInstanceOf(Invoker::class, $result);
  }

  /**
   * A subclass can also use ::for() without the model needing HasFilterable.
   */
  public function test_for_works_on_subclass()
  {
    $filter = new class extends Filterable {};

    $instance = $filter::for(Post::class);

    $this->assertSame(Post::class, $instance->getModel());
  }

  /**
   * Filterable::for() is a proper alternative to the HasFilterable scope.
   * The model does NOT need to use the HasFilterable trait.
   */
  public function test_for_does_not_require_has_filterable_trait_on_model()
  {
    // Use a plain anonymous model class without the HasFilterable trait
    $plainModel = new class extends \Illuminate\Database\Eloquent\Model {
      protected $table = 'posts';
      public $timestamps = false;
    };

    $filter = Filterable::for($plainModel);

    $this->assertInstanceOf(Filterable::class, $filter);
    $this->assertSame($plainModel, $filter->getModel());
  }
}
