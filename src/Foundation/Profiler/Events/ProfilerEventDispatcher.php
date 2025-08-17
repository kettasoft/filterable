<?php

namespace Kettasoft\Filterable\Foundation\Profiler\Events;

class ProfilerEventDispatcher
{
  /**
   * @var array<string, callable[]>
   */
  protected array $listeners = [];

  /**
   * Event name for slow queries.
   * 
   * @var string
   */
  public const SLOW_QUERY_EVENT_NAME = 'onSlowQuery';

  /**
   * Event name for duplicate queries.
   * 
   * @var string
   */
  public const DUBLICATE_QUERY_EVENT_NAME = 'onDuplicateQuery';

  /**
   * Register an event listener.
   *
   * @param string   $event
   * @param callable $listener
   * @return void
   */
  public function listen(string $event, callable $listener): void
  {
    $this->listeners[$event][] = $listener;
  }

  /**
   * Dispatch an event.
   *
   * @param string $event
   * @param mixed  $payload
   * @return void
   */
  public function dispatch(string $event, mixed $payload = null): void
  {
    foreach ($this->listeners[$event] ?? [] as $listener) {
      $listener($payload);
    }
  }

  /**
   * Flush all listeners.
   * 
   * @return void
   */
  public function flush(): void
  {
    $this->listeners = [];
  }
}
