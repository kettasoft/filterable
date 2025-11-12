<?php

namespace Kettasoft\Filterable\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator;
use Kettasoft\Filterable\Foundation\Caching\FilterableCacheManager;

class FilterableCacheTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Disable profiler for cache tests - no need for profiling overhead in unit tests
        config(['filterable.profiler.enabled' => false]);

        Cache::flush();
        Post::query()->delete(); // Clear posts between tests
        app(FilterableCacheManager::class)->resetInstance(); // Reset singleton
    }

    /** @test */
    public function it_can_get_singleton_instance()
    {
        $manager1 = app(FilterableCacheManager::class)->getInstance();
        $manager2 = app(FilterableCacheManager::class)->getInstance();

        $this->assertSame($manager1, $manager2);
    }

    /** @test */
    public function it_can_cache_and_retrieve_values()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();

        $manager->put('test_key', 'test_value', 60);

        $this->assertTrue($manager->has('test_key'));
        $this->assertEquals('test_value', $manager->get('test_key'));
    }

    /** @test */
    public function it_can_remember_values()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();
        $callCount = 0;

        $result1 = $manager->remember('test_remember', 60, function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        });

        $result2 = $manager->remember('test_remember', 60, function () use (&$callCount) {
            $callCount++;
            return 'computed_value';
        });

        $this->assertEquals('computed_value', $result1);
        $this->assertEquals('computed_value', $result2);
        $this->assertEquals(1, $callCount, 'Callback should only be called once');
    }

    /** @test */
    public function it_can_cache_forever()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();

        $manager->forever('forever_key', 'forever_value');

        $this->assertTrue($manager->has('forever_key'));
        $this->assertEquals('forever_value', $manager->get('forever_key'));
    }

    /** @test */
    public function it_can_forget_cached_values()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();

        $manager->put('forget_key', 'forget_value', 60);
        $this->assertTrue($manager->has('forget_key'));

        $manager->forget('forget_key');
        $this->assertFalse($manager->has('forget_key'));
    }

    /** @test */
    public function it_can_generate_cache_keys_with_scopes()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();

        $manager->addScope('user', 123);
        $manager->addScope('tenant', 'acme');

        $key = $manager->generateKey('base_key');

        $this->assertStringContainsString('base_key', $key);
        $this->assertStringContainsString('tenant:acme', $key);
        $this->assertStringContainsString('user:123', $key);
    }

    /** @test */
    public function it_respects_enabled_disabled_state()
    {
        $manager = app(FilterableCacheManager::class)->getInstance();

        $manager->disable();
        $this->assertFalse($manager->isEnabled());

        $result = $manager->put('disabled_key', 'value', 60);
        $this->assertFalse($result);
        $this->assertFalse($manager->has('disabled_key'));

        $manager->enable();
        $this->assertTrue($manager->isEnabled());
    }

    /** @test */
    public function cache_key_generator_creates_deterministic_keys()
    {
        $generator = new CacheKeyGenerator();

        $key1 = $generator->generate('TestFilter', ['name' => 'John'], ['featured' => true]);
        $key2 = $generator->generate('TestFilter', ['name' => 'John'], ['featured' => true]);

        $this->assertEquals($key1, $key2, 'Keys should be deterministic');
    }

    /** @test */
    public function cache_key_generator_creates_different_keys_for_different_data()
    {
        $generator = new CacheKeyGenerator();

        $key1 = $generator->generate('TestFilter', ['name' => 'John']);
        $key2 = $generator->generate('TestFilter', ['name' => 'Jane']);

        $this->assertNotEquals($key1, $key2, 'Different data should produce different keys');
    }

    /** @test */
    public function cache_key_generator_includes_provided_data()
    {
        $generator = new CacheKeyGenerator();

        $key1 = $generator->generate('TestFilter', ['name' => 'John'], ['featured' => true]);
        $key2 = $generator->generate('TestFilter', ['name' => 'John'], ['featured' => false]);

        $this->assertNotEquals($key1, $key2, 'Different provided data should produce different keys');
    }

    /** @test */
    public function cache_key_generator_supports_user_scoping()
    {
        $generator = new CacheKeyGenerator();

        $key1 = $generator->forUser('TestFilter', 123, ['name' => 'John']);
        $key2 = $generator->forUser('TestFilter', 456, ['name' => 'John']);

        $this->assertNotEquals($key1, $key2, 'Different users should produce different keys');
    }

    /** @test */
    public function cache_key_generator_supports_tenant_scoping()
    {
        $generator = new CacheKeyGenerator();

        $key1 = $generator->forTenant('TestFilter', 'acme', ['name' => 'John']);
        $key2 = $generator->forTenant('TestFilter', 'beta', ['name' => 'John']);

        $this->assertNotEquals($key1, $key2, 'Different tenants should produce different keys');
    }

    /** @test */
    public function cache_key_generator_normalizes_class_names()
    {
        $generator = new CacheKeyGenerator();

        $key = $generator->generate('App\\Filters\\PostFilter', []);

        $this->assertStringContainsString('post_filter', $key);
        $this->assertStringNotContainsString('App\\Filters\\', $key);
    }

    /** @test */
    public function it_caches_filterable_results_on_first_execution()
    {
        // Create a simple test filter
        $filter = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Set up test data
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test Body',
            'status' => 'active',
        ]);

        // Enable caching and execute
        $results = $filter->cache(60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Test Post', $results->first()->title);
    }

    /** @test */
    public function it_retrieves_cached_results_on_second_execution()
    {
        // Create test filter
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create initial data
        $post1 = Post::create([
            'title' => 'First Post',
            'content' => 'Body 1',
            'status' => 'active',
        ]);

        // First execution - should cache
        $filter1 = clone $filterClass;
        $results1 = $filter1->cache(60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results1);

        // Create more data AFTER caching
        $post2 = Post::create([
            'title' => 'Second Post',
            'content' => 'Body 2',
            'status' => 'active',
        ]);

        // Second execution - should return cached results (still 1 post, not 2)
        $filter2 = clone $filterClass;
        $results2 = $filter2->cache(60)
            ->apply(Post::query())
            ->get();

        // If caching works, we should still see only 1 post
        $this->assertCount(1, $results2, 'Should return cached result with 1 post');
        $this->assertEquals('First Post', $results2->first()->title);

        // Verify the database actually has 2 posts
        $this->assertEquals(2, Post::count());
    }

    /** @test */
    public function it_executes_query_when_caching_is_disabled()
    {
        $filter = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        $post1 = Post::create([
            'title' => 'Post 1',
            'content' => 'Body 1',
            'status' => 'active',
        ]);

        // Execute without caching
        $results1 = $filter->apply(Post::query())->get();
        $this->assertCount(1, $results1);

        // Add another post
        $post2 = Post::create([
            'title' => 'Post 2',
            'content' => 'Body 2',
            'status' => 'active',
        ]);

        // Execute again without caching - should see both posts
        $results2 = $filter->apply(Post::query())->get();
        $this->assertCount(2, $results2, 'Without caching, should see all posts');
    }

    /** @test */
    public function it_creates_different_cache_for_different_terminal_methods()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create test data
        Post::create([
            'title' => 'Post 1',
            'content' => 'Body 1',
            'status' => 'active',
        ]);

        Post::create([
            'title' => 'Post 2',
            'content' => 'Body 2',
            'status' => 'active',
        ]);

        // Cache with get()
        $filter1 = clone $filterClass;
        $allPosts = $filter1->cache(60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(2, $allPosts);

        // Cache with first() - should have different cache key
        $filter2 = clone $filterClass;
        $firstPost = $filter2->cache(60)
            ->apply(Post::query())
            ->first();

        $this->assertNotNull($firstPost);
        $this->assertEquals('Post 1', $firstPost->title);
    }

    /** @test */
    public function it_caches_with_user_scope()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create test data
        $post = Post::create([
            'title' => 'Scoped Post',
            'content' => 'Body',
            'status' => 'active',
        ]);

        // Cache with user scope
        $filter = clone $filterClass;
        $results = $filter->cache(60)
            ->scopeByUser(123)
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results);

        // Different user should have different cache
        $filter2 = clone $filterClass;

        // Add more data
        Post::create([
            'title' => 'Another Post',
            'content' => 'Body',
            'status' => 'active',
        ]);

        $results2 = $filter2->cache(60)
            ->scopeByUser(456) // Different user ID
            ->apply(Post::query())
            ->get();

        // Should get fresh results (2 posts) because different user scope
        $this->assertCount(2, $results2, 'Different user scope should not use cached results');
    }

    /** @test */
    public function it_can_flush_cached_results()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create initial data
        Post::create([
            'title' => 'Post 1',
            'content' => 'Body 1',
            'status' => 'active',
        ]);

        // Cache results
        $filter1 = clone $filterClass;
        $results1 = $filter1->cache(60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results1);

        // Add more data
        Post::create([
            'title' => 'Post 2',
            'content' => 'Body 2',
            'status' => 'active',
        ]);

        // Flush cache
        $filter1->flushCache();

        // Execute again - should see fresh data
        $filter2 = clone $filterClass;
        $results2 = $filter2->cache(60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(2, $results2, 'After flushing cache, should see fresh data');
    }

    /** @test */
    public function it_caches_paginated_results()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create test data
        for ($i = 1; $i <= 25; $i++) {
            Post::create([
                'title' => "Post $i",
                'content' => "Body $i",
                'status' => 'active',
            ]);
        }

        // Cache paginated results
        $filter = clone $filterClass;
        $page1 = $filter->cache(60)
            ->apply(Post::query())
            ->paginate(10);

        $this->assertCount(10, $page1);
        $this->assertEquals(25, $page1->total());

        // Add more posts
        for ($i = 26; $i <= 30; $i++) {
            Post::create([
                'title' => "Post $i",
                'content' => "Body $i",
                'status' => 'active',
            ]);
        }

        // Execute again - should return cached paginated results
        $filter2 = clone $filterClass;
        $page1Again = $filter2->cache(60)
            ->apply(Post::query())
            ->paginate(10);

        // Cached result should still show 25 total, not 30
        $this->assertEquals(25, $page1Again->total(), 'Cached pagination should show original total');
    }

    /** @test */
    public function it_uses_cache_tags_correctly()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Only run this test if cache driver supports tags
        if (!Cache::supportsTags()) {
            $this->markTestSkipped('Cache driver does not support tags');
        }

        // Create test data
        Post::create([
            'title' => 'Tagged Post',
            'content' => 'Body',
            'status' => 'active',
        ]);

        // Cache with tags
        $filter = clone $filterClass;
        $results1 = $filter->cache(60)
            ->cacheTags(['posts', 'content'])
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results1);

        // Add more data
        Post::create([
            'title' => 'Another Post',
            'content' => 'Body',
            'status' => 'active',
        ]);

        // Flush by tags
        $filterClass::flushCacheByTagsStatic(['posts']);

        // Execute again - should see fresh data after tag flush
        $filter2 = clone $filterClass;
        $results2 = $filter2->cache(60)
            ->cacheTags(['posts', 'content'])
            ->apply(Post::query())
            ->get();

        $this->assertCount(2, $results2, 'After flushing by tags, should see fresh data');
    }

    /** @test */
    public function it_respects_cache_when_condition()
    {
        $filterClass = new class extends \Kettasoft\Filterable\Filterable {
            protected $filters = [];
        };

        // Create test data
        Post::create([
            'title' => 'Post 1',
            'content' => 'Body 1',
            'status' => 'active',
        ]);

        // Cache only when condition is true
        $filter1 = clone $filterClass;
        $results1 = $filter1->cacheWhen(true, 60)
            ->apply(Post::query())
            ->get();

        $this->assertCount(1, $results1);

        // Add more data
        Post::create([
            'title' => 'Post 2',
            'content' => 'Body 2',
            'status' => 'active',
        ]);

        // Don't cache when condition is false
        $filter2 = clone $filterClass;
        $results2 = $filter2->cacheWhen(false, 60)
            ->apply(Post::query())
            ->get();

        // Should see all posts because caching was disabled
        $this->assertCount(2, $results2, 'When cacheWhen(false), should not use cache');
    }
}
