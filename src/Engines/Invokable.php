<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use ReflectionMethod;
use Illuminate\Support\Str;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributePipeline;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Foundation\PayloadFactory;
use Kettasoft\Filterable\Engines\Foundation\Parsers\Dissector;
use Kettasoft\Filterable\Exceptions\FilterableMethodConflictException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class Invokable extends Engine
{
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
      $method = $this->getMethodName($filter);

      // Conflicts are configuration errors and must not be swallowed in non-strict mode.
      if (method_exists(Filterable::class, $method)) {
        throw new FilterableMethodConflictException($method);
      }

      $this->attempt(function () use ($filter, $method) {
        $dissector = Dissector::parse($this->context->getRequest()->get($filter), $this->defaultOperator());

        $payload = new Payload($filter, $dissector->operator, $this->sanitizeValue($filter, $dissector->value), $dissector->value);

        $payload = (new PayloadFactory($this))->make($payload);

        $this->applyFilterMethod($filter, $method, $payload);

        $this->commit($method, $payload);
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
    if (! method_exists($this->context, $method)) {
      return;
    }

    $attrContext = new AttributeContext($this, $payload);

    $pipeline = new AttributePipeline($attrContext);
    $process = $pipeline->process($this->context, $method);

    $process->then(function () use ($method, $payload) {
      $result = (new ReflectionMethod($this->context, $method))
        ->invoke($this->context, $payload);

      if ($result instanceof Builder) {
        $this->builder = $result;
        $this->context->setBuilder($result);
      }
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
