<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvokableEngineTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    $countOfActiveStatus = 7;
    $countOfPendingStatus = 5;

    Post::factory($countOfActiveStatus)->create([
      'status' => 'active',
      'title' => 'Active posts'
    ]);

    Post::factory($countOfPendingStatus)->create([
      'status' => 'pending',
      'title' => 'Pending posts'
    ]);
  }

  /**
   * It can filter with basic class filter.
   * @test
   */
  public function it_can_filter_by_request_query_key_name()
  {
    request()->merge([
      'status' => 'pending',
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }
    };

    $posts = Post::filter($filter)->count();

    $this->assertEquals(5, $posts);
  }

  /**
   * It can filter with basic class filter.
   * @test
   */
  public function it_can_test_method_mapping_filter()
  {
    request()->merge([
      'status' => 'pending'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];
      protected $mentors = [
        'status' => 'filterBystatus'
      ];

      public function filterBystatus(Payload $payload)
      {
        return $this->builder->where('status', $payload);
      }
    };

    $posts = Post::filter($filter)->count();

    $this->assertEquals(5, $posts);
  }
}
