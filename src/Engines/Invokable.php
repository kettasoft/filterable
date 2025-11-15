<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Support\Str;
use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Support\Payload;
use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\ClauseFactory;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributePipeline;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeRegistry;

class Invokable extends Engine
{
  use ForwardsCalls;

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
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function handle(Builder $builder): Builder
  {
    $this->builder = $builder;

    // Set allowed fields from $filters property automatically.
    $this->context->setAllowedFields($this->context->getFilterAttributes());

    foreach ($this->context->getFilterAttributes() as $filter) {

      $dissector = Dissector::parse($this->context->getRequest()->get($filter), $this->defaultOperator());

      $payload = new Payload($filter, $dissector->operator, $this->sanitizeValue($filter, $dissector->value), $dissector->value);

      $clause = (new ClauseFactory($this))->make($payload);

      if (($this->context->hasIgnoredEmptyValues() || config('filterable.engines.invokable.ignore_empty_values')) && !$clause->value) {
        continue;
      }

      $method = $this->getMethodName($filter);

      // Check for method name conflicts with Filterable core methods.
      if (method_exists(Filterable::class, $method)) {
        throw new \RuntimeException(sprintf("Filter method [%s] conflicts with core Filterable method.", [$method]));
      }

      $this->initializeFilters($filter, $method, $payload);

      $this->commit($method, $clause);
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
  protected function initializeFilters(string $key, string $method, Payload $payload): void
  {
    if (! method_exists($this->context, $method)) {
      return;
    }

    $attrContext = new AttributeContext(
      $this->builder,
      $payload,
      state: ['method' => $method, 'key' => $key]
    );

    $pipeline = new AttributePipeline(new AttributeRegistry(), $attrContext);
    $pipeline->process($this->context, $method);

    $this->forwardCallTo($this->context, $method, [$payload]);
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
