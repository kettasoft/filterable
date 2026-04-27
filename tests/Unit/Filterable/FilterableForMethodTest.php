<?php

namespace Kettasoft\Filterable\Tests\Unit\Foundation;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\Models\User;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableForMethodTest extends TestCase
{
  public function test_it_creates_filterable_instance_for_model_class()
  {
    $filterable = Filterable::for(Post::class);

    $this->assertInstanceOf(Filterable::class, $filterable);
    $this->assertEquals(Post::class, $filterable->getModel());
  }

  public function test_it_creates_filterable_instance_for_model_instance()
  {
    $post = new Post();
    $filterable = Filterable::for($post);

    $this->assertInstanceOf(Filterable::class, $filterable);
    $this->assertInstanceOf(Post::class, $filterable->getModel());
  }

  public function test_it_creates_filterable_instance_for_builder()
  {
    $builder = Post::query();
    $filterable = Filterable::for($builder->where('id', 1));
    $filterable->where('title', 'test');

    $sql = $filterable->filter()->toRawSql();
    $this->assertInstanceOf(Filterable::class, $filterable);
    $this->assertInstanceOf(Builder::class, $filterable->getBuilder());
    $this->assertStringContainsString('where "id" = 1 and "title" = \'test\'', $sql);
  }

  public function test_it_accepts_custom_request()
  {
    $request = Request::create('/test', 'GET', ['filter' => 'value']);
    $filterable = Filterable::for(Post::class, $request);

    $this->assertSame($request, $filterable->getRequest());
  }

  public function test_it_uses_default_request_when_not_provided()
  {
    $filterable = Filterable::for(Post::class);

    $this->assertInstanceOf(Request::class, $filterable->getRequest());
  }

  public function test_it_can_chain_methods_after_for()
  {
    request()->merge(['title' => 'Test']);

    $filterable = Filterable::for(Post::class)
      ->setAllowedFields(['title', 'content']);

    $this->assertEquals(['title', 'content'], $filterable->getAllowedFields());
  }

  public function test_it_can_apply_filters_after_for()
  {
    request()->merge(['title' => 'Test Post']);

    $filterable = Filterable::for(Post::class)->useEngine('ruleset')
      ->setAllowedFields(['title'])->ignoreEmptyValues();

    $query = $filterable->apply();

    $this->assertStringContainsString('where', strtolower($query->toSql()));
  }

  public function test_it_works_with_multiple_models()
  {
    $postFilterable = Filterable::for(Post::class);
    $userFilterable = Filterable::for(User::class);

    $this->assertEquals(Post::class, $postFilterable->getModel());
    $this->assertEquals(User::class, $userFilterable->getModel());
  }

  public function test_it_creates_independent_instances()
  {
    $filterable1 = Filterable::for(Post::class);
    $filterable2 = Filterable::for(User::class);

    $filterable1->setAllowedFields(['title']);
    $filterable2->setAllowedFields(['name', 'email']);

    $this->assertEquals(['title'], $filterable1->getAllowedFields());
    $this->assertEquals(['name', 'email'], $filterable2->getAllowedFields());
  }

  public function test_it_can_use_model_instance_to_apply_filters()
  {
    request()->merge(['id' => 1]);

    $post = new Post();
    $filterable = Filterable::for($post)->useEngine('ruleset')
      ->setAllowedFields(['id']);

    $query = $filterable->apply();

    $this->assertStringContainsString('where', strtolower($query->toSql()));
  }

  public function test_for_method_returns_fluent_interface()
  {
    $result = Filterable::for(Post::class)
      ->setAllowedFields(['title'])
      ->ignoreEmptyValues()
      ->strict();

    $this->assertInstanceOf(Filterable::class, $result);
  }

  public function test_it_can_use_for_with_filtering_and_sorting()
  {
    request()->merge([
      'title' => 'Test',
      'sort' => 'created_at'
    ]);

    Filterable::addSorting(Filterable::class, function ($sort) {
      return $sort->allow(['created_at', 'id']);
    });

    $filterable = Filterable::for(Post::class)
      ->useEngine('ruleset')->permissive()
      ->setAllowedFields(['title']);

    $query = $filterable->apply();
    $sql = strtolower($query->toSql());

    $this->assertStringContainsString('where', $sql);
    $this->assertStringContainsString('order by', $sql);
  }

  public function test_for_method_with_custom_filter_class()
  {
    request()->merge(['title' => 'Custom']);

    $customFilter = new class extends Filterable {
      protected $allowedFields = ['title', 'content'];
    };

    $filterable = $customFilter::for(Post::class);

    $this->assertEquals(['title', 'content'], $filterable->getAllowedFields());
    $this->assertEquals(Post::class, $filterable->getModel());
  }

  public function test_it_initializes_model_in_builder_when_applying()
  {
    $filterable = Filterable::for(Post::class);
    $query = $filterable->apply();

    $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query->getBuilder());
    $this->assertInstanceOf(Post::class, $query->getModel());
  }
}
