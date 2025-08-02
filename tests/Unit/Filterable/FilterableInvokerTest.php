<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Support\Facades\Queue;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Kettasoft\Filterable\Foundation\Invoker;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;
use Kettasoft\Filterable\Tests\Jobs\TestExecuteFilterJob;

class FilterableInvokerTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Post::factory(10)->create([
      'status' => 'active'
    ]);

    Post::factory(10)->create([
      'status' => 'stopped'
    ]);
  }

  public function test_it_can_recover_invoker_instance()
  {
    $result = Post::filter(new PostFilter);

    $this->assertInstanceOf(Invoker::class, $result);
  }

  public function test_it_can_invoke_callback_before()
  {

    $result = Post::filter(new PostFilter);

    $count = $result->beforeExecute(function ($builder) {
      $builder->where('status', 'active');
    })->count();

    $this->assertEquals(10, $count);
  }

  public function test_it_can_invoke_callback_after()
  {

    /**
     * @var Invoker
     */
    $result = Post::filter(new PostFilter);

    $result = $result->afterExecute(function (Collection $result) {
      return $result->filter(function ($item) {
        return $item->id > 10;
      });
    })->get();

    $this->assertEquals(10, $result->count());
  }

  public function test_it_executes_when_callback_if_condition_is_true()
  {
    $invoker = $this->createPartialMock(Invoker::class, []);
    $invoked = false;

    $invoker->when(true, function ($instance) use (&$invoked) {
      $invoked = true;
      $this->assertInstanceOf(Invoker::class, $instance);
    });

    $this->assertTrue($invoked, 'Expected the callback to be invoked when condition is true.');
  }

  public function test_it_does_not_execute_when_callback_if_condition_is_false()
  {
    $invoker = $this->createPartialMock(Invoker::class, []);
    $invoked = false;

    $invoker->when(false, function () use (&$invoked) {
      $invoked = true;
    });

    $this->assertFalse($invoked, 'Expected the callback NOT to be invoked when condition is false.');
  }

  public function test_it_executes_unless_callback_if_condition_is_false()
  {
    $invoker = $this->createPartialMock(Invoker::class, []);
    $invoked = false;

    $invoker->unless(false, function ($instance) use (&$invoked) {
      $invoked = true;
      $this->assertInstanceOf(Invoker::class, $instance);
    });

    $this->assertTrue($invoked, 'Expected the callback to be invoked when condition is false.');
  }

  public function test_it_does_not_execute_unless_callback_if_condition_is_true()
  {
    $invoker = $this->createPartialMock(Invoker::class, []);
    $invoked = false;

    $invoker->unless(true, function () use (&$invoked) {
      $invoked = true;
    });

    $this->assertFalse($invoked, 'Expected the callback NOT to be invoked when condition is true.');
  }

  public function test_it_dispatches_job_with_invoker_through_asJob()
  {
    Queue::fake();

    $invoker = Post::filter(new PostFilter());

    $invoker->asJob(TestExecuteFilterJob::class, ['extra' => 'test']);

    Queue::assertPushed(TestExecuteFilterJob::class, function ($job) use ($invoker) {
      return $job->invoker === $invoker && $job->extra === 'test';
    });
  }

  public function test_it_invoke_callback_on_error()
  {
    $this->expectException(\InvalidArgumentException::class);

    /**
     * @var Invoker
     */
    $result = Post::filter(new PostFilter);

    $result->onError(function ($context, $th) {
      throw new \InvalidArgumentException();
    });

    $result->whereHas('invalid', 'invalid');

    $result->get();
  }
}
