<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines;

use Carbon\Carbon;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\In;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class SkipTrackingTest extends TestCase
{
  public function test_it_tracks_a_skipped_payload_and_reason()
  {
    request()->merge(['status' => 'invalid']);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      #[In('active', 'pending')]
      public function status(Payload $payload)
      {
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertTrue($filter->hasSkipped('status'));
    $this->assertNull($filter->applied('status'));

    $skipped = $filter->skipped('status');

    $this->assertCount(1, $skipped);
    $this->assertInstanceOf(Payload::class, $skipped[0]['payload']);
    $this->assertSame('status', $skipped[0]['field']);
    $this->assertSame('invalid', $skipped[0]['value']);
    $this->assertStringContainsString('not in the allowed set', $skipped[0]['reason']);
    $this->assertInstanceOf(Carbon::class, $skipped[0]['timestamp']);
  }

  public function test_skipped_payloads_are_stored_as_snapshots()
  {
    $filter = new Filterable;
    $filter->skip(
      Payload::create('title', '=', 'invalid', 'invalid'),
      'Invalid title'
    );

    $payload = Payload::create('status', '=', 'invalid', 'invalid');

    $filter->skip($payload, 'Invalid status');
    $payload->setValue('changed');

    $skipped = $filter->skipped('status');

    $this->assertArrayHasKey(0, $skipped);
    $this->assertCount(1, $skipped);
    $this->assertSame('invalid', $skipped[0]['payload']->value);
    $this->assertSame('invalid', $skipped[0]['value']);
  }

  public function test_engine_skip_uses_a_default_reason()
  {
    $filter = new Filterable;
    $payload = Payload::create('status', '=', 'invalid', 'invalid');

    try {
      $filter->getEngine()->skip($payload);
      $this->fail('Expected SkipExecution to be thrown.');
    } catch (SkipExecution $exception) {
      $this->assertSame('Filter execution skipped.', $exception->getMessage());
      $this->assertSame($payload, $exception->getPayload());
    }
  }

  public function test_strict_mode_still_records_the_skipped_payload()
  {
    request()->merge(['status' => 'invalid']);

    $filter = new class extends Filterable {
      protected $filters = ['status'];

      #[In('active', 'pending')]
      public function status(Payload $payload) {}
    };

    try {
      Post::filter($filter->strict())->get();
      $this->fail('Expected SkipExecution to be thrown.');
    } catch (SkipExecution $exception) {
      $this->assertTrue($filter->hasSkipped('status'));
      $this->assertSame('status', $exception->getPayload()?->field);
    }
  }
}
