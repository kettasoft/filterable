<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Support\FilterResolver;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;

class FilterResolverTest extends \Kettasoft\Filterable\Tests\TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    config()->set('filterable.aliases', Filterable::aliases([
      'post' => PostFilter::class,
    ]));
  }

  public function test_it_resolve_filterable_instance_with_alias()
  {

    $model = new Post();
    $builder = $model->newQuery();

    $resolver = new FilterResolver($builder, 'post');
    $filterable = $resolver->resolve();

    $this->assertInstanceOf(Invoker::class, $filterable);
  }

  public function test_it_resolve_filterable_instance_with_class_name()
  {
    $model = new Post();
    $builder = $model->newQuery();

    $resolver = new FilterResolver($builder, PostFilter::class);
    $filterable = $resolver->resolve();

    $this->assertInstanceOf(Invoker::class, $filterable);
  }

  public function test_it_resolve_filterable_instance_with_instance()
  {
    $model = new Post();
    $builder = $model->newQuery();

    $resolver = new FilterResolver($builder, new PostFilter());
    $filterable = $resolver->resolve();

    $this->assertInstanceOf(Invoker::class, $filterable);
  }

  public function test_it_throws_exception_when_filter_is_not_defined()
  {
    $this->expectException(\Kettasoft\Filterable\Exceptions\FilterIsNotDefinedException::class);

    $model = new Post();
    $builder = $model->newQuery();

    $resolver = new FilterResolver($builder, 'non_existing_alias');
    $resolver->resolve();
  }

  public function test_it_resolve_filterable_instance_from_model_getter()
  {
    $model = new class extends Post {
      public function getFilterable(): ?string
      {
        return PostFilter::class;
      }
    };

    $builder = $model->newQuery();

    $resolver = new FilterResolver($builder);
    $filterable = $resolver->resolve();

    $this->assertInstanceOf(Invoker::class, $filterable);
  }
}
