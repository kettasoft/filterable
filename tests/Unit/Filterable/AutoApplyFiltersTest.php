<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;

class AutoApplyFiltersTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    // Create test posts
    Post::factory(2)->create(['title' => 'Active Post', 'status' => 'active']);
    Post::factory(2)->create(['title' => 'Pending Post', 'status' => 'pending']);
    Post::factory(2)->create(['title' => 'Stopped Post', 'status' => 'stopped']);
  }

  public function test_it_auto_applies_filters_on_get()
  {
    request()->merge(['status' => 'active']);

    $posts = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($p) => $p->status === 'active'));
  }

  public function test_it_auto_applies_filters_on_first()
  {
    request()->merge(['status' => 'active']);

    $post = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->first();

    $this->assertNotNull($post);
    $this->assertEquals('active', $post->status);
  }

  public function test_it_auto_applies_filters_on_count()
  {
    request()->merge(['status' => 'active']);

    $count = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->count();

    $this->assertEquals(2, $count);
  }

  public function test_it_auto_applies_filters_on_exists()
  {
    request()->merge(['status' => 'active']);

    $exists = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->exists();

    $this->assertTrue($exists);
  }

  public function test_it_auto_applies_filters_on_doesnt_exist()
  {
    request()->merge(['status' => 'doesnt_exist']);

    $doesntExist = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->doesntExist();

    $this->assertTrue($doesntExist);
  }

  public function test_it_auto_applies_filters_on_paginate()
  {
    request()->merge(['status' => 'active']);

    $paginated = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->paginate(10);

    $this->assertEquals(2, $paginated->total());
  }

  public function test_it_prevents_double_apply()
  {
    request()->merge(['status' => 'active']);

    $filterable = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status']);

    // First auto-apply
    $count1 = $filterable->count();

    // Second call should not re-apply
    $count2 = $filterable->count();

    $this->assertEquals($count1, $count2);
  }

  public function test_manual_apply_still_works()
  {
    request()->merge(['status' => 'active']);

    $posts = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->apply()
      ->get();

    $this->assertCount(2, $posts);
  }

  public function test_it_auto_applies_on_sum()
  {
    Post::query()->update(['views' => 10]);
    request()->merge(['status' => 'active']);

    $sum = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->sum('views');

    $this->assertEquals(20, $sum); // 2 active posts * 10 views
  }

  public function test_it_auto_applies_on_avg()
  {
    Post::query()->update(['views' => 10]);
    request()->merge(['status' => 'active']);

    $avg = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->avg('views');

    $this->assertEquals(10, $avg);
  }

  public function test_it_auto_applies_on_min()
  {
    Post::where('status', 'active')->first()->update(['views' => 5]);
    Post::where('status', 'active')->skip(1)->first()->update(['views' => 15]);

    request()->merge(['status' => 'active']);

    $min = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->min('views');

    $this->assertEquals(5, $min);
  }

  public function test_it_auto_applies_on_max()
  {
    Post::where('status', 'active')->first()->update(['views' => 5]);
    Post::where('status', 'active')->skip(1)->first()->update(['views' => 15]);

    request()->merge(['status' => 'active']);

    $max = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->max('views');

    $this->assertEquals(15, $max);
  }

  public function test_it_auto_applies_on_pluck()
  {
    request()->merge(['status' => 'in:active,pending']);

    $titles = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->pluck('title');

    $this->assertCount(4, $titles);
    $this->assertTrue($titles->contains('Active Post'));
    $this->assertTrue($titles->contains('Pending Post'));
  }

  public function test_it_auto_applies_on_value()
  {
    request()->merge(['status' => 'active']);

    $title = Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status'])
      ->value('title');

    $this->assertNotNull($title);
    $this->assertContains($title, ['Active Post', 'Another Active']);
  }
}
