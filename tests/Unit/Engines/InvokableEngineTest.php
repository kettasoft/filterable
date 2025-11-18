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

  /**
   * It can filter with basic class filter.
   * @test
   */
  public function it_filter_with_ignored_null_or_empty_values()
  {
    request()->merge([
      'status' => ''
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];
      protected $mentors = [
        'status' => 'filterBystatus'
      ];

      public function filterBystatus(Payload $payload)
      {
        if ($payload->value) {
          return $this->builder->where('status', $payload);
        }

        return $this->builder->where($payload->field, 'pending');
      }
    };

    $posts = Post::filter($filter)->count();

    $this->assertEquals(5, $posts);
  }

  /**
   * It can filter with field and operator.
   * @test
   */
  public function it_can_filter_with_field_and_operator()
  {
    request()->merge([
      'status' => [
        'operator' => 'eq',
        'value' => 'pending'
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->operator, $payload->value);
      }
    };

    $posts = Post::filter($filter)->count();

    $this->assertEquals(5, $posts);
  }

  /**
   * @test
   */
  public function it_can_filter_with_multiple_filters()
  {
    Post::factory()->create([
      'status' => 'active',
      'title' => 'Laravel Tutorial'
    ]);

    Post::factory()->create([
      'status' => 'pending',
      'title' => 'PHP Guide'
    ]);

    request()->merge([
      'status' => 'active',
      'title' => 'Laravel Tutorial'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status', 'title'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }

      public function title(Payload $payload)
      {
        return $this->builder->where('title', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('active', $posts->first()->status);
    $this->assertEquals('Laravel Tutorial', $posts->first()->title);
  }

  /**
   * @test
   */
  public function it_can_filter_with_multiple_filters_and_operators()
  {
    // Clear setUp data for this specific test
    Post::truncate();

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Test Post',
      'views' => 100
    ]);

    Post::factory()->create([
      'status' => 'pending',
      'title' => 'Another Post',
      'views' => 50
    ]);

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Third Post',
      'views' => 150
    ]);

    request()->merge([
      'status' => [
        'operator' => 'eq',
        'value' => 'active'
      ],
      'views' => [
        'operator' => 'gt',
        'value' => 75
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status', 'views'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->operator, $payload->value);
      }

      public function views(Payload $payload)
      {
        return $this->builder->where('views', $payload->operator, $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => $post->status === 'active' && $post->views > 75));
  }

  /**
   * @test
   */
  public function it_can_filter_with_like_operator()
  {
    Post::truncate();

    Post::factory()->create(['title' => 'Laravel Framework']);
    Post::factory()->create(['title' => 'PHP Tutorial']);
    Post::factory()->create(['title' => 'Laravel Tips']);

    request()->merge([
      'title' => [
        'operator' => 'like',
        'value' => '%Laravel%'
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['title'];

      public function title(Payload $payload)
      {
        return $this->builder->where('title', $payload->operator, $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => str_contains($post->title, 'Laravel')));
  }

  /**
   * @test
   */
  public function it_can_filter_with_in_operator()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active']);
    Post::factory()->create(['status' => 'pending']);
    Post::factory()->create(['status' => 'stopped']);

    request()->merge([
      'status' => [
        'operator' => 'in',
        'value' => ['active', 'stopped']
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      public function status(Payload $payload)
      {
        return $this->builder->whereIn('status', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => in_array($post->status, ['active', 'stopped'])));
  }

  /**
   * @test
   */
  public function it_can_filter_with_between_operator()
  {
    Post::truncate();

    Post::factory()->create(['views' => 10]);
    Post::factory()->create(['views' => 50]);
    Post::factory()->create(['views' => 100]);
    Post::factory()->create(['views' => 150]);

    request()->merge([
      'views' => [
        'operator' => 'between',
        'value' => [40, 110]
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['views'];

      public function views(Payload $payload)
      {
        return $this->builder->whereBetween('views', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => $post->views >= 40 && $post->views <= 110));
  }

  /**
   * @test
   */
  public function it_can_filter_with_null_operator()
  {
    Post::truncate();

    Post::factory()->create(['description' => null]);
    Post::factory()->create(['description' => 'Some description']);
    Post::factory()->create(['description' => null]);

    request()->merge([
      'description' => [
        'operator' => 'null',
        'value' => true
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['description'];

      public function description(Payload $payload)
      {
        if ($payload->value) {
          return $this->builder->whereNull('description');
        }

        return $this->builder->whereNotNull('description');
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => is_null($post->description)));
  }

  /**
   * @test
   */
  public function it_can_handle_camel_case_filter_methods()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active', 'is_featured' => true]);
    Post::factory()->create(['status' => 'active', 'is_featured' => false]);

    request()->merge([
      'is_featured' => true
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['is_featured'];

      public function isFeatured(Payload $payload)
      {
        return $this->builder->where('is_featured', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertTrue($posts->first()->is_featured);
  }

  /**
   * @test
   */
  public function it_can_use_method_mentors_mapping()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active']);
    Post::factory()->create(['status' => 'pending']);

    request()->merge([
      'post_status' => 'active'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['post_status'];
      protected $mentors = [
        'post_status' => 'filterByStatus'
      ];

      public function filterByStatus(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('active', $posts->first()->status);
  }

  /**
   * @test
   */
  public function it_can_handle_complex_nested_requests()
  {
    Post::truncate();

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Laravel',
      'views' => 100
    ]);

    Post::factory()->create([
      'status' => 'pending',
      'title' => 'PHP',
      'views' => 50
    ]);

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Vue.js',
      'views' => 150
    ]);

    request()->merge([
      'status' => [
        'operator' => '=',
        'value' => 'active'
      ],
      'views' => [
        'operator' => '>=',
        'value' => 100
      ],
      'title' => [
        'operator' => 'like',
        'value' => '%a%'
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status', 'views', 'title'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->operator, $payload->value);
      }

      public function views(Payload $payload)
      {
        return $this->builder->where('views', $payload->operator, $payload->value);
      }

      public function title(Payload $payload)
      {
        return $this->builder->where('title', $payload->operator, $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('Laravel', $posts->first()->title);
  }

  /**
   * @test
   */
  public function it_can_apply_sanitization_to_filters()
  {
    Post::truncate();

    Post::factory()->create(['title' => 'Laravel']);

    request()->merge([
      'title' => '  Laravel  '
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['title'];
      protected $sanitizers = [
        'title' => 'trim'
      ];

      public function title(Payload $payload)
      {
        return $this->builder->where('title', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('Laravel', $posts->first()->title);
  }

  /**
   * @test
   */
  public function it_ignores_empty_values_when_configured()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active']);
    Post::factory()->create(['status' => 'pending']);

    request()->merge([
      'status' => ''
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status', 'title'];
      protected $ignoreEmptyValues = true;

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }
    };

    // Should not filter by status since it's empty
    $posts = Post::filter($filter)->get();

    // All posts should be returned since status is ignored
    $this->assertGreaterThanOrEqual(2, $posts->count());
  }

  /**
   * @test
   */
  public function it_can_chain_multiple_where_conditions_in_single_filter()
  {
    Post::factory()->create(['status' => 'active', 'views' => 100]);
    Post::factory()->create(['status' => 'active', 'views' => 50]);
    Post::factory()->create(['status' => 'pending', 'views' => 100]);

    request()->merge([
      'combined' => [
        'status' => 'active',
        'views' => 100
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['combined'];

      public function combined(Payload $payload)
      {
        $data = $payload->value;
        return $this->builder
          ->where('status', $data['status'])
          ->where('views', $data['views']);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('active', $posts->first()->status);
    $this->assertEquals(100, $posts->first()->views);
  }

  /**
   * @test
   */
  public function it_can_use_or_where_conditions()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active']);
    Post::factory()->create(['status' => 'pending']);
    Post::factory()->create(['status' => 'stopped']);

    request()->merge([
      'status_or' => ['active', 'pending']
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status_or'];

      public function statusOr(Payload $payload)
      {
        return $this->builder->where(function ($query) use ($payload) {
          foreach ($payload->value as $status) {
            $query->orWhere('status', $status);
          }
        });
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($post) => in_array($post->status, ['active', 'pending'])));
  }

  /**
   * @test
   */
  public function it_can_handle_boolean_filters()
  {
    Post::truncate();

    Post::factory()->create(['is_featured' => true, 'status' => 'active']);
    Post::factory()->create(['is_featured' => false, 'status' => 'active']);
    Post::factory()->create(['is_featured' => true, 'status' => 'pending']);

    request()->merge([
      'is_featured' => true,
      'status' => 'active'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['is_featured', 'status'];

      public function isFeatured(Payload $payload)
      {
        return $this->builder->where('is_featured', $payload->asBoolean());
      }

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertTrue($posts->first()->is_featured);
    $this->assertEquals('active', $posts->first()->status);
  }

  /**
   * @test
   */
  public function it_can_handle_date_range_filters()
  {
    Post::factory()->create(['created_at' => now()->subDays(5)]);
    Post::factory()->create(['created_at' => now()->subDays(3)]);
    Post::factory()->create(['created_at' => now()->subDay()]);

    request()->merge([
      'created_at' => [
        'from' => now()->subDays(4)->toDateString(),
        'to' => now()->subDays(2)->toDateString()
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['created_at'];

      public function createdAt(Payload $payload)
      {
        return $this->builder->whereBetween('created_at', [
          $payload->value['from'],
          $payload->value['to']
        ]);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
  }

  /**
   * @test
   */
  public function it_can_handle_json_payload_values()
  {
    Post::truncate();

    Post::factory()->create(['tags' => json_encode(['php', 'laravel'])]);
    Post::factory()->create(['tags' => json_encode(['vue', 'javascript'])]);

    request()->merge([
      'tags' => json_encode(['php', 'laravel'])
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['tags'];

      public function tags(Payload $payload)
      {
        if ($payload->isJson()) {
          return $this->builder->where('tags', 'LIKE', '"' . str_replace('"', '\"', $payload->value) . '"');
        }

        return $this->builder;
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
  }

  /**
   * @test
   */
  public function it_returns_all_records_when_no_filters_applied()
  {
    Post::truncate();
    Post::factory()->count(5)->create();

    request()->merge([]);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(5, $posts);
  }

  /**
   * @test
   */
  public function it_can_use_payload_helper_methods()
  {
    Post::factory()->create(['title' => 'Laravel Tutorial']);
    Post::factory()->create(['title' => 'PHP Guide']);

    request()->merge([
      'title' => 'Laravel'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['title'];

      public function title(Payload $payload)
      {
        // Using payload helper method
        return $this->builder->where('title', 'like', $payload->asLike('both'));
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertStringContainsString('Laravel', $posts->first()->title);
  }

  /**
   * @test
   */
  public function it_can_handle_numeric_string_filters()
  {
    Post::truncate();
    Post::factory()->create(['views' => 100]);
    Post::factory()->create(['views' => 200]);
    Post::factory()->create(['views' => 50]);

    request()->merge([
      'views' => '100'
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['views'];

      public function views(Payload $payload)
      {
        return $this->builder->where('views', $payload->asInt());
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals(100, $posts->first()->views);
  }

  /**
   * @test
   */
  public function it_can_filter_with_array_values()
  {
    Post::truncate();

    Post::factory()->create(['status' => 'active']);
    Post::factory()->create(['status' => 'pending']);
    Post::factory()->create(['status' => 'stopped']);

    request()->merge([
      'statuses' => ['active', 'pending']
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['statuses'];

      public function statuses(Payload $payload)
      {
        if ($payload->isArray()) {
          return $this->builder->whereIn('status', $payload->value);
        }

        return $this->builder;
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(2, $posts);
  }

  /**
   * @test
   */
  public function it_properly_handles_payload_operators()
  {
    Post::truncate();

    Post::factory()->create(['views' => 50]);
    Post::factory()->create(['views' => 100]);
    Post::factory()->create(['views' => 150]);

    $operators = [
      ['operator' => 'gt', 'value' => 75, 'expected' => 2],
      ['operator' => 'lt', 'value' => 125, 'expected' => 2],
      ['operator' => 'gte', 'value' => 100, 'expected' => 2],
      ['operator' => 'lte', 'value' => 100, 'expected' => 2],
      ['operator' => 'eq', 'value' => 100, 'expected' => 1],
      ['operator' => 'neq', 'value' => 100, 'expected' => 2],
    ];

    foreach ($operators as $test) {
      request()->merge([
        'views' => [
          'operator' => $test['operator'],
          'value' => $test['value']
        ]
      ]);

      $filter = new class extends Filterable {
        protected $filters = ['views'];

        public function views(Payload $payload)
        {
          return $this->builder->where('views', $payload->operator, $payload->value);
        }
      };

      $count = Post::filter($filter)->count();

      $this->assertEquals(
        $test['expected'],
        $count,
        "Failed for operator {$test['operator']} with value {$test['value']}"
      );
    }
  }

  /**
   * @test
   */
  public function it_can_access_raw_payload_value()
  {
    Post::truncate();

    Post::factory()->create(['title' => 'Test']);

    request()->merge([
      'title' => '  Test  '
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['title'];
      protected $sanitizers = [
        'title' => 'trim'
      ];

      public function title(Payload $payload)
      {
        // Value is sanitized
        $this->assertEquals('Test', $payload->value);
        // Raw value is not sanitized
        $this->assertEquals('  Test  ', $payload->raw());

        return $this->builder->where('title', $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
  }

  /**
   * @test
   */
  public function it_can_combine_multiple_filter_patterns()
  {
    Post::truncate();

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Laravel Framework',
      'views' => 100,
      'is_featured' => true
    ]);

    Post::factory()->create([
      'status' => 'pending',
      'title' => 'PHP Tutorial',
      'views' => 50,
      'is_featured' => false
    ]);

    Post::factory()->create([
      'status' => 'active',
      'title' => 'Vue.js Guide',
      'views' => 150,
      'is_featured' => true
    ]);

    request()->merge([
      'status' => 'active',
      'is_featured' => true,
      'views' => [
        'operator' => '>=',
        'value' => 100
      ],
      'title' => [
        'operator' => 'like',
        'value' => '%Framework%'
      ]
    ]);

    $filter = new class extends Filterable {
      protected $filters = ['status', 'is_featured', 'views', 'title'];

      public function status(Payload $payload)
      {
        return $this->builder->where('status', $payload->value);
      }

      public function isFeatured(Payload $payload)
      {
        return $this->builder->where('is_featured', $payload->asBoolean());
      }

      public function views(Payload $payload)
      {
        return $this->builder->where('views', $payload->operator, $payload->value);
      }

      public function title(Payload $payload)
      {
        return $this->builder->where('title', $payload->operator, $payload->value);
      }
    };

    $posts = Post::filter($filter)->get();

    $this->assertCount(1, $posts);
    $this->assertEquals('Laravel Framework', $posts->first()->title);
    $this->assertTrue($posts->first()->is_featured);
    $this->assertEquals('active', $posts->first()->status);
    $this->assertGreaterThanOrEqual(100, $posts->first()->views);
  }
}
