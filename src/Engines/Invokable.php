<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributePipeline;
use Kettasoft\Filterable\Engines\Foundation\ClauseFactory;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\Hooks\Concerns\HasHooks;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Exceptions\FilterableMethodConflictException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class Invokable extends Engine
{
  use HasHooks,
    ForwardsCalls;

  /**
   * Engine name.
   * @var string
   */
  protected $name = 'invokable';

  /**
   * The Eloquent builder instance.
   * @var Builder
   */
  protected Builder $builder;

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function execute(Builder $builder): Builder
  {
    $this->builder = $builder;

    // Set allowed fields from $filters property automatically.
    $this->context->setAllowedFields($this->context->getFilterAttributes());

    foreach ($this->context->getFilterAttributes() as $filter) {
      $this->attempt(function () use ($filter) {
        $dissector = Dissector::parse($this->context->getRequest()->get($filter), $this->defaultOperator());

        $payload = new Payload($filter, $dissector->operator, $this->sanitizeValue($filter, $dissector->value), $dissector->value);

        $clause = (new ClauseFactory($this))->make($payload);

        $method = $this->getMethodName($filter);

        // ── Empty value hook ──────────────────────────────────────────────
        if (! $payload->isNotNullOrEmpty()) {
          $this->runEmptyHook($filter, $payload);
        }

        // ── Field-level before hook ───────────────────────────────────────
        if (! $this->runBefore($filter, $payload)) {
          return;
        }

        // ── Main filter method ────────────────────────────────────────────
        $this->applyFilterMethod($filter, $method, $payload);

        // ── Field-level after hook ────────────────────────────────────────
        $this->runAfter($filter, $payload);

        $this->commit($method, $clause);
      });
    }

    return $this->builder;
  }

  /**
   * Initialize the filter methods and resolve value.
   * @param string $key
   * @param string $method
   * @param Payload $payload
   * @return void
   */
  protected function applyFilterMethod(string $key, string $method, Payload $payload): void
  {
    // Check for method name conflicts with Filterable core methods.
    if (method_exists(Filterable::class, $method)) {
      throw new FilterableMethodConflictException($method);
    }

    if (! method_exists($this->context, $method)) {
      $this->runSkip($key, $payload);
      return;
    }

    $attrContext = new AttributeContext(
      $this->builder,
      $payload,
      state: ['method' => $method, 'key' => $key]
    );

    $pipeline = new AttributePipeline($attrContext);
    $process = $pipeline->process($this->context, $method);

    $process->then(function () use ($method, $payload) {
      $this->forwardCallTo($this->context, $method, [$payload]);
    })
      ->catch(function ($e) {
        throw $e;
      });
  }

  /**
   * Get method name.
   * @param string $filter
   * @return string
   */
  protected function getMethodName(string $filter): string
  {
    if (array_key_exists($filter, $this->context->getMentors())) {
      return $this->context->getMentors()[$filter];
    }

    return $this->context->getRequest()->has($filter) ? Str::camel($filter) : 'default' . Str::studly($filter);
  }

  /**
   * Get engine default operator.
   * @return string
   */
  public function defaultOperator(): string
  {
    return config('filterable.engines.invokable.default_operator', 'eq');
  }

  /**
   * Get engine name.
   * @return string
   */
  public function getEngineName(): string
  {
    return $this->name;
  }
}
