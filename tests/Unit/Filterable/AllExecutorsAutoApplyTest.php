<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Facades\DB;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class AllExecutorsAutoApplyTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    DB::table('tags')->delete();
    Post::query()->withTrashed()->forceDelete();

    $stable = ['views' => 0];
    Post::factory(2)->create(array_merge($stable, ['title' => 'Active Post', 'status' => 'active']));
    Post::factory(2)->create(array_merge($stable, ['title' => 'Pending Post', 'status' => 'pending']));
    Post::factory(2)->create(array_merge($stable, ['title' => 'Stopped Post', 'status' => 'stopped']));
  }

  /**
   * Ruleset map + request merge (same as AutoApplyFiltersTest).
   */
  private function filterableForQuery(array $query): Filterable
  {
    request()->merge($query);

    return Filterable::for(Post::class)
      ->useEngine('ruleset')
      ->setAllowedFields(['status']);
  }

  public function test_get_auto_applies_filters()
  {
    $posts = $this->filterableForQuery(['status' => 'active'])->get();
    $this->assertCount(2, $posts);
    $this->assertTrue($posts->every(fn($p) => $p->status === 'active'));
  }

  public function test_first_auto_applies_filters()
  {
    $post = $this->filterableForQuery(['status' => 'active'])->first();
    $this->assertNotNull($post);
    $this->assertSame('active', $post->status);
  }

  public function test_firstOr_returns_first_or_callback()
  {
    $one = $this->filterableForQuery(['status' => 'active'])->firstOr(fn() => 'fallback');
    $this->assertInstanceOf(Post::class, $one);

    $none = $this->filterableForQuery(['status' => 'nonexistent'])->firstOr(fn() => 'ok');
    $this->assertSame('ok', $none);
  }

  public function test_firstOrFail_auto_applies_filters()
  {
    $post = $this->filterableForQuery(['status' => 'active'])->firstOrFail();
    $this->assertSame('active', $post->status);

    $this->expectException(ModelNotFoundException::class);
    $this->filterableForQuery(['status' => 'nope'])->firstOrFail();
  }

  public function test_firstOrCreate_finds_with_filters()
  {
    $model = $this->filterableForQuery(['status' => 'active'])->firstOrCreate(
      ['title' => 'Active Post'],
      ['content' => 'x', 'views' => 0, 'is_featured' => false, 'description' => null, 'tags' => null, 'user_id' => null]
    );
    $this->assertSame('active', $model->status);
  }

  public function test_firstOrNew_creates_unsaved_with_filters()
  {
    $new = $this->filterableForQuery(['status' => 'active'])->firstOrNew(
      ['title' => 'completely-unique-xyz-123'],
      ['content' => 'n', 'views' => 0, 'is_featured' => false, 'description' => null, 'tags' => null, 'user_id' => null]
    );
    $this->assertFalse($new->exists);
  }

  public function test_find_respects_filter()
  {
    $pending = Post::where('status', 'pending')->first();
    $this->assertNotNull(
      $this->filterableForQuery(['status' => 'pending'])->find($pending->id)
    );
    $this->assertNull(
      $this->filterableForQuery(['status' => 'active'])->find($pending->id)
    );
  }

  public function test_findOr_callback_when_missing_under_filter()
  {
    $pending = Post::where('status', 'pending')->first();
    $out = $this->filterableForQuery(['status' => 'active'])->findOr($pending->id, fn() => 99);
    $this->assertSame(99, $out);
  }

  public function test_findOrFail_respects_filter()
  {
    $active = Post::where('status', 'active')->first();
    $this->filterableForQuery(['status' => 'active'])->findOrFail($active->id);

    $this->expectException(ModelNotFoundException::class);
    $pending = Post::where('status', 'pending')->first();
    $this->filterableForQuery(['status' => 'active'])->findOrFail($pending->id);
  }

  public function test_findOrNew()
  {
    $pending = Post::where('status', 'pending')->first();
    $m = $this->filterableForQuery(['status' => 'active'])->findOrNew($pending->id);
    $this->assertFalse($m->exists);
  }

  public function test_sole_and_soleValue_require_single_row_under_filter()
  {
    Post::where('status', 'active')->orderBy('id')->skip(1)->first()?->delete();

    $m = $this->filterableForQuery(['status' => 'active'])->sole();
    $this->assertSame('active', $m->status);

    $v = $this->filterableForQuery(['status' => 'active'])->soleValue('status');
    $this->assertSame('active', $v);
  }

  public function test_sole_throws_when_multiple_rows()
  {
    $this->expectException(MultipleRecordsFoundException::class);
    $this->filterableForQuery(['status' => 'active'])->sole();
  }

  public function test_count_sum_avg_average_min_max_auto_apply()
  {
    Post::query()->update(['views' => 10]);
    Post::where('status', 'active')->orderBy('id')->first()->update(['views' => 5]);
    Post::where('status', 'active')->orderBy('id')->skip(1)->first()->update(['views' => 15]);

    $f = $this->filterableForQuery(['status' => 'active']);
    $this->assertSame(2, $f->count());
    $this->assertSame(20, (int) $f->sum('views'));
    $this->assertEquals(10.0, (float) $f->avg('views'));
    $this->assertEquals(10.0, (float) $f->average('views'));
    $this->assertSame(5, (int) $f->min('views'));
    $this->assertSame(15, (int) $f->max('views'));
  }

  public function test_exists_doesntExist()
  {
    $this->assertTrue($this->filterableForQuery(['status' => 'active'])->exists());
    $this->assertTrue($this->filterableForQuery(['status' => 'nope'])->doesntExist());
  }

  public function test_existsOr_returns_true_or_callback()
  {
    $result = $this->filterableForQuery(['status' => 'active'])->existsOr(fn() => 'fallback');
    $this->assertTrue($result);

    $result = $this->filterableForQuery(['status' => 'nonexistent'])->existsOr(fn() => 'no-records');
    $this->assertSame('no-records', $result);
  }

  public function test_doesntExistOr_returns_true_or_callback()
  {
    $result = $this->filterableForQuery(['status' => 'nonexistent'])->doesntExistOr(fn() => 'fallback');
    $this->assertTrue($result);

    $result = $this->filterableForQuery(['status' => 'active'])->doesntExistOr(fn() => 'has-records');
    $this->assertSame('has-records', $result);
  }

  public function test_value()
  {
    $t = $this->filterableForQuery(['status' => 'active'])->value('title');
    $this->assertContains($t, ['Active Post', 'Active Post']);
  }

  public function test_pluck()
  {
    $t = $this->filterableForQuery(['status' => 'in:active,pending'])->pluck('title');
    $this->assertCount(4, $t);
  }

  public function test_implode()
  {
    $s = $this->filterableForQuery(['status' => 'active'])->implode('title', ',');
    $this->assertStringContainsString('Active', $s);
  }

  public function test_paginate_simplePaginate_cursorPaginate()
  {
    $p = $this->filterableForQuery(['status' => 'active'])->paginate(10);
    $this->assertSame(2, $p->total());

    $s = $this->filterableForQuery(['status' => 'active'])->simplePaginate(10);
    $this->assertCount(2, $s->items());

    $c = $this->filterableForQuery(['status' => 'active'])->orderBy('id')->cursorPaginate(10);
    $this->assertLessThanOrEqual(2, count($c->items()));
  }

  public function test_chunk_chunkByIdDesc_each_eachById()
  {
    $f = $this->filterableForQuery(['status' => 'active']);
    $seen = 0;
    $f->chunk(10, function ($c) use (&$seen) {
      $seen += $c->count();
    });
    $this->assertSame(2, $seen);

    $seen2 = 0;
    $f->chunkById(10, function ($c) use (&$seen2) {
      $seen2 += $c->count();
    });
    $this->assertSame(2, $seen2);

    $this->filterableForQuery(['status' => 'active'])->chunkByIdDesc(10, function ($c) {
      $this->assertLessThanOrEqual(2, $c->count());
    });

    $n = 0;
    $this->filterableForQuery(['status' => 'active'])->each(function () use (&$n) {
      $n++;
    });
    $this->assertSame(2, $n);

    $m = 0;
    $this->filterableForQuery(['status' => 'active'])->eachById(function () use (&$m) {
      $m++;
    });
    $this->assertSame(2, $m);
  }

  public function test_lazy_lazyById_and_lazyByIdDesc_and_cursor()
  {
    $a = $this->filterableForQuery(['status' => 'active'])->lazy(100)->count();
    $this->assertSame(2, $a);

    $b = $this->filterableForQuery(['status' => 'active'])->lazyById(100, 'id', 'id')->count();
    $this->assertSame(2, $b);

    $c = $this->filterableForQuery(['status' => 'active'])->lazyByIdDesc(100, 'id', 'id')->count();
    $this->assertSame(2, $c);

    $d = 0;
    foreach ($this->filterableForQuery(['status' => 'active'])->cursor() as $_) {
      $d++;
    }
    $this->assertSame(2, $d);
  }

  public function test_insert_and_insertGetId_and_insertOrIgnore()
  {
    $ts = now();
    $row = [
      'title' => 'inserted-one',
      'content' => 'c',
      'status' => 'stopped',
      'views' => 0,
      'is_featured' => false,
      'description' => null,
      'tags' => null,
      'user_id' => null,
      'deleted_at' => null,
      'created_at' => $ts,
      'updated_at' => $ts,
    ];
    $before = Post::count();
    $this->filterableForQuery(['status' => 'active'])->insert([$row]);
    $this->assertSame($before + 1, Post::count());

    $id = $this->filterableForQuery(['status' => 'active'])->insertGetId($row);
    $this->assertIsInt($id);

    $this->filterableForQuery(['status' => 'active'])->insertOrIgnore([$row]);
  }

  public function test_insertUsing()
  {
    $id = (int) Post::where('status', 'active')->value('id');
    $before = Post::count();
    $this->filterableForQuery(['status' => 'active'])->insertUsing(
      ['title', 'status', 'content', 'views', 'is_featured', 'user_id', 'description', 'tags', 'created_at', 'updated_at', 'deleted_at'],
      Post::query()
        ->select('title', 'status', 'content', 'views', 'is_featured', 'user_id', 'description', 'tags', 'created_at', 'updated_at', 'deleted_at')
        ->where('id', $id)
    );
    $this->assertSame($before + 1, Post::count());
  }

  public function test_insertOrIgnoreUsing()
  {
    $id = (int) Post::where('status', 'active')->value('id');
    $before = Post::count();
    $this->filterableForQuery(['status' => 'active'])->insertOrIgnoreUsing(
      ['title', 'status', 'content', 'views', 'is_featured', 'user_id', 'description', 'tags', 'created_at', 'updated_at', 'deleted_at'],
      Post::query()
        ->select('title', 'status', 'content', 'views', 'is_featured', 'user_id', 'description', 'tags', 'created_at', 'updated_at', 'deleted_at')
        ->where('id', $id)
    );
    $this->assertSame($before + 1, Post::count());
  }

  public function test_update()
  {
    $n = $this->filterableForQuery(['status' => 'active'])->update(['content' => 'all-active-updated']);
    $this->assertSame(2, $n);
    $this->assertSame(2, Post::where('status', 'active')->where('content', 'all-active-updated')->count());
  }

  public function test_updateOrInsert()
  {
    $p = Post::where('status', 'active')->first();
    $this->filterableForQuery(['status' => 'active'])->updateOrInsert(
      ['id' => $p->id],
      ['content' => 'uoi', 'title' => $p->title, 'status' => 'active', 'views' => 0, 'is_featured' => false, 'description' => null, 'tags' => null, 'user_id' => null]
    );
    $this->assertDatabaseHas('posts', ['id' => $p->id, 'content' => 'uoi']);
  }

  public function test_upsert()
  {
    $a = Post::where('status', 'active')->orderBy('id')->first();
    $this->filterableForQuery(['status' => 'active'])->upsert(
      [
        [
          'id' => $a->id,
          'title' => $a->title,
          'content' => 'upsert-content',
          'status' => 'active',
          'views' => 0,
          'is_featured' => false,
          'description' => null,
          'tags' => null,
          'user_id' => null,
          'deleted_at' => null,
          'created_at' => $a->created_at,
          'updated_at' => now(),
        ],
      ],
      ['id'],
      ['content', 'updated_at']
    );
    $this->assertDatabaseHas('posts', ['id' => $a->id, 'content' => 'upsert-content']);
  }

  public function test_delete()
  {
    $n = $this->filterableForQuery(['status' => 'active'])->delete();
    $this->assertSame(2, $n);
    $this->assertSame(0, Post::where('status', 'active')->count());
  }

  public function test_forceDelete()
  {
    $n = $this->filterableForQuery(['status' => 'active'])->forceDelete();
    $this->assertSame(2, $n);
    $this->assertDatabaseCount('posts', 4);
  }

  public function test_restore()
  {
    $p = Post::where('status', 'pending')->first();
    $p->delete();
    $this->assertSame(1, Post::onlyTrashed()->where('status', 'pending')->count());

    $n = $this->filterableForQuery(['status' => 'pending'])->restore();
    $this->assertIsInt($n);
    $this->assertTrue($n >= 1);
    $this->assertSame(0, Post::onlyTrashed()->where('id', $p->id)->count());
  }

  public function test_truncate()
  {
    $this->filterableForQuery(['status' => 'active'])->truncate();
    $this->assertDatabaseCount('posts', 0);
  }

  public function test_increment_decrement()
  {
    $this->filterableForQuery(['status' => 'active'])->increment('views', 2);
    $this->assertSame(2 * 2, (int) Post::where('status', 'active')->sum('views'));

    $this->filterableForQuery(['status' => 'active'])->decrement('views', 1);
    $this->assertSame(2, (int) Post::where('status', 'active')->sum('views'));
  }

  public function test_incrementEach_decrementEach()
  {
    $this->filterableForQuery(['status' => 'active'])->incrementEach(['views' => 1]);
    $this->assertSame(2, (int) Post::where('status', 'active')->sum('views'));

    $this->filterableForQuery(['status' => 'active'])->decrementEach(['views' => 1]);
    $this->assertSame(0, (int) Post::where('status', 'active')->sum('views'));
  }
}
