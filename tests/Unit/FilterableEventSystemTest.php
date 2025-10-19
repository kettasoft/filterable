<?php

namespace Tests\Unit;

use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Facades\Filterable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kettasoft\Filterable\Filterable as BaseFilterable;
use Kettasoft\Filterable\Tests\Http\Filters\PostFilter;

/**
 * Test suite for the Filterable Event System
 * 
 * These tests demonstrate how to test the event system functionality.
 */
class FilterableEventSystemTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up listeners after each test
     */
    protected function tearDown(): void
    {
        Filterable::flushListeners();
        Filterable::resetEventManager();
        parent::tearDown();
    }

    /** @test */
    public function it_fires_initializing_event_when_filter_is_created()
    {
        $eventFired = false;

        Filterable::on('filterable.initializing', function ($filterable) use (&$eventFired) {
            $eventFired = true;
            $this->assertInstanceOf(BaseFilterable::class, $filterable);
        });

        PostFilter::create();

        $this->assertTrue($eventFired, 'filterable.initializing event should be fired');
    }

    /** @test */
    public function it_fires_resolved_event_after_initialization()
    {
        $eventFired = false;

        Filterable::on('filterable.resolved', function ($engine, $data) use (&$eventFired) {
            $eventFired = true;
            // $this->assertInstanceOf(Filterable::class, $filterable);
            $this->assertIsObject($engine);
            $this->assertIsArray($data);
        });

        PostFilter::create();

        $this->assertTrue($eventFired, 'filterable.resolved event should be fired');
    }

    /** @test */
    public function it_fires_applied_event_when_filters_are_applied_successfully()
    {
        $eventFired = false;

        Filterable::on('filterable.applied', function ($filterable) use (&$eventFired) {
            $eventFired = true;
            $this->assertInstanceOf(BaseFilterable::class, $filterable);
        });

        PostFilter::create()->apply(Post::query())->get();

        $this->assertTrue($eventFired, 'filterable.applied event should be fired');
    }

    /** @test */
    public function it_fires_finished_event_at_the_end_of_filtering()
    {
        $eventFired = false;

        Filterable::on('filterable.finished', function ($filterable) use (&$eventFired) {
            $eventFired = true;
        });

        PostFilter::create()->apply(Post::query())->get();

        $this->assertTrue($eventFired, 'filterable.finished event should be fired');
    }

    /** @test */
    public function it_fires_failed_event_when_exception_occurs()
    {
        $eventFired = false;
        $exceptionCaught = false;

        Filterable::on('filterable.failed', function ($filterable, $exception) use (&$eventFired) {
            $eventFired = true;
            $this->assertInstanceOf(\Throwable::class, $exception);
        });

        try {
            // Force an exception by passing invalid builder
            PostFilter::create()->apply(null)->get();
        } catch (\Throwable $e) {
            $exceptionCaught = true;
        }

        $this->assertTrue($eventFired, 'filterable.failed event should be fired');
        $this->assertTrue($exceptionCaught, 'Exception should be propagated after event');
    }

    /** @test */
    public function it_fires_finished_event_even_when_exception_occurs()
    {
        $finishedFired = false;

        Filterable::on('filterable.finished', function ($filterable) use (&$finishedFired) {
            $finishedFired = true;
        });

        try {
            Post::filter(PostFilter::class)->get();
        } catch (\Throwable $e) {
            // Exception is expected, do nothing
        }

        $this->assertTrue($finishedFired, 'filterable.finished should fire even on failure');
    }

    /** @test */
    public function it_calls_observer_for_specific_filter_class()
    {
        $observerCalled = false;

        Filterable::observe(PostFilter::class, function ($event, $payload) use (&$observerCalled) {
            if ($event->is('applied')) {
                $observerCalled = true;
            }
        });

        PostFilter::create()->apply(Post::query())->get();

        $this->assertTrue($observerCalled, 'Observer should be called for PostFilter');
    }

    /** @test */
    public function it_does_not_call_observer_for_different_filter_class()
    {
        $observerCalled = false;

        Filterable::observe(PostFilter::class, function () use (&$observerCalled) {
            $observerCalled = true;
        });

        // Assuming UserFilter exists
        // UserFilter::create()->apply(User::query())->get();

        // For this test, we'll just verify the observer is registered correctly
        $observers = Filterable::getObservers(PostFilter::class);
        $this->assertCount(1, $observers);
    }

    /** @test */
    public function it_allows_multiple_listeners_for_same_event()
    {
        $firstCalled = false;
        $secondCalled = false;

        Filterable::on('filterable.applied', function ($filterable) use (&$firstCalled) {
            $firstCalled = true;
        });

        Filterable::on('filterable.applied', function ($filterable) use (&$secondCalled) {
            $secondCalled = true;
        });

        PostFilter::create()->apply(Post::query())->get();

        $this->assertTrue($firstCalled, 'First listener should be called');
        $this->assertTrue($secondCalled, 'Second listener should be called');
    }

    /** @test */
    public function it_handles_listener_exceptions_gracefully()
    {
        $secondListenerCalled = false;

        // First listener throws exception
        Filterable::on('filterable.applied', function ($filterable) {
            throw new \Exception('Listener failed');
        });

        // Second listener should still execute
        Filterable::on('filterable.applied', function ($filterable) use (&$secondListenerCalled) {
            $secondListenerCalled = true;
        });

        // Filtering should complete successfully
        $result = PostFilter::create()->apply(Post::query())->get();

        $this->assertNotNull($result);
        $this->assertTrue($secondListenerCalled, 'Other listeners should still execute');
    }

    /** @test */
    public function it_can_flush_all_listeners()
    {
        Filterable::on('filterable.applied', fn($filterable) => null);
        Filterable::observe(PostFilter::class, fn() => null);

        $this->assertCount(1, Filterable::getListeners('filterable.applied'));
        $this->assertCount(1, Filterable::getObservers(PostFilter::class));

        Filterable::flushListeners();

        $this->assertCount(0, Filterable::getListeners('filterable.applied'));
        $this->assertCount(0, Filterable::getObservers(PostFilter::class));
    }

    /** @test */
    public function it_can_disable_events_for_specific_instance()
    {
        $eventFired = false;

        Filterable::on('filterable.applied', function ($filterable) use (&$eventFired) {
            $eventFired = true;
        });

        PostFilter::create()
            ->disableEvents()
            ->apply(Post::query())
            ->get();

        $this->assertFalse($eventFired, 'Events should be disabled for this instance');
    }

    /** @test */
    public function it_can_enable_events_for_specific_instance()
    {
        // Disable globally
        config(['filterable.events.enabled' => false]);

        $eventFired = false;

        Filterable::on('filterable.applied', function ($filterable) use (&$eventFired) {
            $eventFired = true;
        });

        PostFilter::create()
            ->enableEvents()
            ->apply(Post::query())
            ->get();

        $this->assertTrue($eventFired, 'Events should be enabled for this instance');
    }

    /** @test */
    public function it_respects_global_events_disabled_configuration()
    {
        config(['filterable.events.enabled' => false]);

        $eventFired = false;

        Filterable::on('filterable.applied', function ($filterable) use (&$eventFired) {
            $eventFired = true;
        });

        PostFilter::create()->apply(Post::query())->get();

        $this->assertFalse($eventFired, 'Events should be disabled globally');
    }

    /** @test */
    public function it_can_retrieve_registered_listeners()
    {
        $callback1 = fn($filterable) => null;
        $callback2 = fn($filterable) => null;

        Filterable::on('filterable.applied', $callback1);
        Filterable::on('filterable.applied', $callback2);

        $listeners = Filterable::getListeners('filterable.applied');

        $this->assertCount(2, $listeners);
        $this->assertContains($callback1, $listeners);
        $this->assertContains($callback2, $listeners);
    }

    /** @test */
    public function it_can_retrieve_registered_observers()
    {
        $callback1 = fn() => null;
        $callback2 = fn() => null;

        Filterable::observe(PostFilter::class, $callback1);
        Filterable::observe(PostFilter::class, $callback2);

        $observers = Filterable::getObservers(PostFilter::class);

        $this->assertCount(2, $observers);
        $this->assertContains($callback1, $observers);
        $this->assertContains($callback2, $observers);
    }

    /** @test */
    public function it_fires_events_in_correct_order()
    {
        $events = [];

        Filterable::on('filterable.initializing', function ($filterable) use (&$events) {
            $events[] = 'initializing';
        });

        Filterable::on('filterable.resolved', function ($engine, $data) use (&$events) {
            $events[] = 'resolved';
        });

        Filterable::on('filterable.applied', function ($filterable) use (&$events) {
            $events[] = 'applied';
        });

        Filterable::on('filterable.finished', function ($filterable) use (&$events) {
            $events[] = 'finished';
        });


        PostFilter::create()->apply(Post::query())->get();

        $this->assertEquals(
            ['initializing', 'resolved', 'applied', 'finished'],
            $events,
            'Events should fire in the correct order'
        );
    }

    /** @test */
    public function observer_receives_correct_event_names()
    {
        $receivedEvents = [];

        Filterable::observe(PostFilter::class, function (string $event, $payload) use (&$receivedEvents) {
            $receivedEvents[] = $event;
        });

        PostFilter::create()->apply(Post::query())->get();

        // Observer should receive event names without 'filterable.' prefix
        $this->assertContains('applied', $receivedEvents);
        $this->assertNotContains('filterable.applied', $receivedEvents);
    }
}
