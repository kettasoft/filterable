<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Facades\Filterable as FilterableFacade;

class FilterableForMethodTest extends TestCase
{
  public function test_it_creates_an_initialized_instance_for_a_model_class()
  {
    $filterable = Filterable::for(Post::class);

    $this->assertSame(Post::class, $filterable->getModel());
    $this->assertInstanceOf(Builder::class, $filterable->getBuilder());
    $this->assertInstanceOf(Post::class, $filterable->getBuilder()->getModel());
  }

  public function test_it_preserves_a_model_instance()
  {
    $model = new Post;
    $filterable = Filterable::for($model);

    $this->assertSame($model, $filterable->getModel());
    $this->assertSame($model, $filterable->getBuilder()->getModel());
  }

  public function test_it_preserves_a_builder_and_derives_its_model()
  {
    $builder = Post::query()->where('status', 'published');
    $filterable = Filterable::for($builder);

    $this->assertSame($builder, $filterable->getBuilder());
    $this->assertSame($builder->getModel(), $filterable->getModel());
    $this->assertStringContainsString('where "status" = ?', $filterable->getBuilder()->toSql());
  }

  public function test_it_accepts_a_custom_request()
  {
    $request = Request::create('/posts', 'GET', ['status' => 'published']);

    $filterable = Filterable::for(Post::class, $request);

    $this->assertSame($request, $filterable->getRequest());
    $this->assertSame('published', $filterable->getData()['status']);
  }

  public function test_it_uses_late_static_binding()
  {
    $filterClass = new class extends Filterable {};

    $filterable = $filterClass::for(Post::class);

    $this->assertInstanceOf($filterClass::class, $filterable);
  }

  public function test_it_rejects_a_non_model_class_string()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('must extend ' . \Illuminate\Database\Eloquent\Model::class);

    Filterable::for(\stdClass::class);
  }

  public function test_it_is_available_through_the_facade()
  {
    $filterable = FilterableFacade::for(Post::class);

    $this->assertInstanceOf(Filterable::class, $filterable);
    $this->assertInstanceOf(Post::class, $filterable->getBuilder()->getModel());
  }

  public function test_builder_methods_remain_fluent_on_filterable()
  {
    $filterable = Filterable::for(Post::class);

    $result = $filterable->where('status', 'published');

    $this->assertSame($filterable, $result);
    $this->assertStringContainsString('where "status" = ?', $filterable->getBuilder()->toSql());
  }

  public function test_invoker_builder_methods_remain_fluent()
  {
    $invoker = Filterable::for(Post::class)->apply();

    $result = $invoker->where('status', 'published');

    $this->assertSame($invoker, $result);
    $this->assertStringContainsString('where "status" = ?', $invoker->getBuilder()->toSql());
  }
}
