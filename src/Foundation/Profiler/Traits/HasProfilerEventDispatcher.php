<?php

namespace Kettasoft\Filterable\Foundation\Profiler\Traits;

use Kettasoft\Filterable\Foundation\Profiler\Events\ProfilerEventDispatcher;

trait HasProfilerEventDispatcher
{
  /**
   * @var ProfilerEventDispatcher
   */
  protected static ?ProfilerEventDispatcher $dispatcher = null;

  /**
   * Get the current ProfilerEventDispatcher instance.
   *
   * @return ProfilerEventDispatcher
   */
  public static function dispatcher(): ProfilerEventDispatcher
  {
    if (! static::$dispatcher) {
      static::$dispatcher = new ProfilerEventDispatcher();
    }

    return static::$dispatcher;
  }

  /**
   * Register an event listener (static API).
   *
   * @param string   $event
   * @param callable $callback
   * @return void
   */
  public static function listen(string $event, callable $callback): void
  {
    static::dispatcher()->listen($event, $callback);
  }

  /**
   * Dispatch an event (used internally).
   *
   * @param string $event
   * @param mixed  $payload
   * @return void
   */
  protected function dispatch(string $event, mixed $payload = null): void
  {
    static::dispatcher()->dispatch($event, $payload);
  }
}
