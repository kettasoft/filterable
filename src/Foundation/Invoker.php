<?php

namespace Kettasoft\Filterable\Foundation;

use Closure;
use Serializable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Foundation\Profiler\Profiler;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Kettasoft\Filterable\Foundation\Contracts\HasDynamicCalls;
use Kettasoft\Filterable\Foundation\Traits\HandleFluentReturn;
use Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface;

use function Opis\Closure\{serialize, unserialize};

/**
 * Class Invoker
 *
 * The Invoker class acts as a wrapper around a query builder instance,
 * providing hooks for before/after execution logic, error handling, and
 * flexible runtime behavior such as deferred execution via Laravel jobs.
 * 
 * @link https://kettasoft.github.io/filterable/execution/invoker
 */
class Invoker implements QueryBuilderInterface, Serializable, HasDynamicCalls
{
  use ForwardsCalls,
    HandleFluentReturn;

  /**
   * Callback to be executed before the query is run.
   * 
   * @var Closure|null
   */
  protected $beforeCallback;

  /**
   * Callback to be executed after the query is run.
   * 
   * @var Closure|null
   */
  protected $afterCallback;

  /**
   * Callback to be executed in case of error during query execution.
   * 
   * @var Closure|null
   */
  protected $errorCallback;

  /**
   * Create a new Invoker instance.
   *
   * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|QueryBuilderInterface $builder
   */
  public function __construct(protected EloquentBuilder|QueryBuilderInterface $builder)
  {
    if (config('filterable.profiler.enabled', false)) {
      app(Profiler::class)->start();
    }
  }

  /**
   * Instantiate a new Invoker object using a builder.
   * @param \Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface $builder
   * @return Invoker
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#public-methods
   */
  public static function init(QueryBuilderInterface $builder)
  {
    return new self($builder);
  }

  /**
   * Execute a callback if the given condition is true.
   *
   * @param bool $condition
   * @param callable $callback
   * @return static
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#when
   */
  public function when(bool $condition, callable $callback): static
  {
    if ($condition) {
      $callback($this);
    }

    return $this;
  }

  /**
   * Execute a callback if the given condition is false.
   *
   * @param bool $condition
   * @param callable $callback
   * @return static
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#unless
   */
  public function unless(bool $condition, callable $callback): static
  {
    return $this->when(!$condition, $callback);
  }

  /**
   * Set the callback to be called before execution.
   *
   * @param Closure $callback
   * @return $this
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#beforeExecute
   */
  public function beforeExecute(Closure $callback): static
  {
    $this->beforeCallback = $callback;

    return $this;
  }

  /**
   * Set the callback to be called after execution.
   *
   * @param Closure $callback
   * @return $this
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#afterExecute
   */
  public function afterExecute(Closure $callback): static
  {
    $this->afterCallback = $callback;

    return $this;
  }

  /**
   * Set the callback to be called after execution.
   *
   * @param Closure $callback
   * @return $this
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#onError
   */
  public function onError(Closure $callback)
  {
    $this->errorCallback = $callback;

    return $this;
  }

  /**
   * Get the underlying query builder instance.
   *
   * @return Builder|EloquentBuilder|QueryBuilderInterface
   */
  public function getBuilder(): EloquentBuilder|Builder|QueryBuilderInterface
  {
    return $this->builder;
  }

  /**
   * Dispatch the query execution as a Laravel job.
   *
   * @param string $jobClass
   * @param array $jobData
   * @param string|null $queue
   * @return mixed
   *
   * @throws \InvalidArgumentException
   * @link https://kettasoft.github.io/filterable/execution/invoker.html#asJob
   */
  public function asJob(string $jobClass, array $jobData = [], string|null $queue = null): mixed
  {
    if (!class_exists($jobClass)) {
      throw new \InvalidArgumentException("Job class [$jobClass] does not exist.");
    }

    $job = new $jobClass(array_merge($jobData, [
      'invoker' => $this,
    ]));

    if ($queue) {
      return dispatch($job)->onQueue($queue);
    }

    return dispatch($job);
  }

  /**
   * Serialize the Invoker instance.
   *
   * @return string
   */
  public function serialize(): string
  {
    return serialize([
      'builder_sql' => $this->builder->toSql(),
      'builder_bindings' => $this->builder->getBindings(),
      'beforeCallback' => $this->beforeCallback ? serialize($this->beforeCallback) : null,
      'afterCallback' => $this->afterCallback ? serialize($this->afterCallback) : null,
      'errorCallback' => $this->errorCallback ? serialize($this->errorCallback) : null
    ]);
  }

  /**
   * Unserialize the Invoker instance.
   *
   * @param string $data
   * @return void
   */
  public function unserialize($data): void
  {
    $unserialized = unserialize($data);
    $connection = App::make('db')->connection();

    $this->builder = $connection->table(DB::raw("({$unserialized['builder_sql']}) as t"));

    $this->builder->setBindings($unserialized['builder_bindings']);

    $this->beforeCallback = $unserialized['beforeCallback'];
    $this->afterCallback = $unserialized['afterCallback'];
    $this->errorCallback = $unserialized['errorCallback'];
  }

  /**
   * Handles dynamic calls to the builder, and tracks execution time
   * for terminal methods only.
   *
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function __call($method, $args)
  {
    if (is_callable($callback = $this->beforeCallback)) {
      call_user_func($callback, $this->builder);
    }

    try {
      $result = $this->handleFluentReturn($method, $args);

      if (is_callable($this->afterCallback)) {
        return call_user_func($this->afterCallback, $result);
      }

      return $result;
    } catch (\Throwable $th) {
      if (is_callable($callback = $this->errorCallback)) {
        return call_user_func($callback, $this, $th);
      }

      throw $th;
    }
  }
}
