<?php

namespace Kettasoft\Filterable\Foundation\Profiler;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Kettasoft\Filterable\Foundation\Profiler\Events\ProfilerEventDispatcher;
use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;
use Kettasoft\Filterable\Foundation\Profiler\Traits\HasProfilerEventDispatcher;

/**
 * Profiler class for collecting and analyzing database queries.
 *
 * This class listens to database query events and collects information
 * about executed queries, including SQL, bindings, execution time, and
 * provides methods to retrieve statistics about the queries.
 *
 * @package Kettasoft\Filterable\Foundation\Profiler
 */
class Profiler
{
  use HasProfilerEventDispatcher;

  /**
   * Array to hold collected queries.
   * 
   * @var array<int, array{sql: string, time: float, bindings: array}>
   */
  protected array $queries = [];

  /**
   * The Laravel application instance.
   *
   * @var \Illuminate\Foundation\Application
   */
  protected Application $app;

  /**
   * The current HTTP request instance.
   *
   * @var \Illuminate\Http\Request
   */
  protected $request;

  /**
   * Create a new Profiler instance.
   *
   * @param ProfilerStorageContract $storage Storage implementation for profiler data.
   */
  public function __construct(protected ProfilerStorageContract $storage, protected Builder $builder)
  {
    $this->app = app();
    $this->request = app('request');
  }

  /**
   * Start the profiler by listening to database query events.
   * 
   * @return void
   */
  public function start()
  {
    DB::listen(fn(QueryExecuted $query) => $this->addQuery($query));
  }

  /**
   * Add a query to the profiler's collection.
   *
   * @param QueryExecuted $query
   * @return void
   */
  protected function addQuery(QueryExecuted $query): void
  {
    $queryData = [
      'sql' => $query->sql,
      'bindings' => $query->bindings,
      'time' => $query->time,
    ];

    $this->queries[] = $queryData;

    if ($query->time > config('filterable.profiler.slow_query_threshold', 100)) {
      $this->dispatch(ProfilerEventDispatcher::SLOW_QUERY_EVENT_NAME, $queryData);
    }

    // check for duplicate query
    $duplicates = $this->getDuplicates();
    foreach ($duplicates as $dup) {
      if ($dup['sql'] === $query->sql) {
        $this->dispatch(ProfilerEventDispatcher::DUBLICATE_QUERY_EVENT_NAME, $dup);
        break;
      }
    }
  }

  /**
   * Get all collected queries.
   *
   * @return array<int, array{sql: string, time: float, bindings: array}>
   */
  public function getQueries(): array
  {
    return $this->queries;
  }

  /**
   * Get duplicated queries (same SQL executed multiple times).
   *
   * @return array<int, array{sql: string, count: int, total_time: float}>
   */
  public function getDuplicates(): array
  {
    $counts = [];

    foreach ($this->queries as $query) {
      $key = md5($query['sql']);
      $counts[$key]['sql'] = $query['sql'];
      $counts[$key]['count'] = ($counts[$key]['count'] ?? 0) + 1;
      $counts[$key]['total_time'] = ($counts[$key]['total_time'] ?? 0) + $query['time'];
    }

    return array_values(array_filter($counts, fn($q) => $q['count'] > 1));
  }

  /**
   * Get only slow queries over a certain threshold.
   *
   * @param float $threshold
   * @return array<int, array{sql: string, time: float}>
   */
  public function getSlowQueries(float $threshold = 100): array
  {
    return array_filter($this->queries, fn($q) => $q['time'] > $threshold);
  }

  /**
   * Export profiler report as structured array.
   *
   * @return array<string, mixed>
   */
  public function toExportArray(): array
  {
    return [
      'duplicates' => $this->getDuplicates(),
      'slow_queries' => $this->getSlowQueries(),
      'queries' => $this->queries,
      'total_queries' => count($this->queries),
      'total_time' => array_sum(array_column($this->queries, 'time')),
      'total_memory' => memory_get_usage(true),
      'connection_name' => $this->builder->getConnection()->getDatabaseName(),
      'executed_at' => now()->toDateTimeString(),
      'model_class' => $this->builder->getModel() ? get_class($this->builder->getModel()) : null,
      'request_method' => $this->request->method(),
      'request_uri' => $this->request->getRequestUri(),
      'request_query' => $this->request->query(),
      'request_body' => $this->request->all()
    ];
  }

  /**
   * Destructor to store profiler data when the object is destroyed.
   * 
   * @return void
   */
  public function __destruct()
  {
    $this->storage->store($this->toExportArray());
  }
}
