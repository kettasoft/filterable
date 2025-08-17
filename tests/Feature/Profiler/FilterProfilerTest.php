<?php

namespace Kettasoft\Filterable\Tests\Feature\Profiler;

use Illuminate\Support\Facades\DB;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Tag;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Database\Events\QueryExecuted;
use Kettasoft\Filterable\Foundation\Profiler\Profiler;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;

class FilterProfilerTest extends TestCase
{
  public function test_it_triggers_slow_query_event()
  {
    $triggered = false;

    Profiler::listen('onSlowQuery', function ($query) use (&$triggered) {
      $this->assertArrayHasKey('sql', $query);
      $this->assertArrayHasKey('time', $query);
      $triggered = true;
    });

    $profiler = app(Profiler::class);
    $profiler->start();

    // Simulate a slow query manually
    $event = new QueryExecuted('select 1', [], 150, DB::connection());
    $this->invokeMethod($profiler, 'addQuery', [$event]);

    $this->assertTrue($triggered, 'Slow query event was not triggered');
  }

  public function test_it_triggers_duplicate_query_event()
  {
    $triggered = false;

    app(Profiler::class)->dispatcher()->listen('onDuplicateQuery', function ($dup) use (&$triggered) {
      $this->assertEquals('select 1', $dup['sql']);
      $this->assertEquals(2, $dup['count']);
      $triggered = true;
    });

    $profiler = app(Profiler::class);
    $profiler->start();

    $event1 = new QueryExecuted('select 1', [], 5, DB::connection());
    $event2 = new QueryExecuted('select 1', [], 7, DB::connection());

    $this->invokeMethod($profiler, 'addQuery', [$event1]);
    $this->invokeMethod($profiler, 'addQuery', [$event2]);

    $this->assertTrue($triggered, 'Duplicate query event was not triggered');
  }


  protected function invokeMethod(&$object, $methodName, array $parameters = [])
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }
}
