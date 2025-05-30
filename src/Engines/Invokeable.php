<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Support\Str;
use Kettasoft\Filterable\Support\Payload;
use Illuminate\Support\Traits\ForwardsCalls;
use Kettasoft\Filterable\Engines\Contracts\Engine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\InvokableEngineContext;
use Kettasoft\Filterable\Support\ConditionNormalizer;

class Invokeable implements Engine
{
  use ForwardsCalls;

  /**
   * The Eloquent builder instance.
   * @var Builder
   */
  protected Builder $builder;

  /**
   * Create Engine instance.
   * @param \Kettasoft\Filterable\Engines\Contracts\InvokableEngineContext $context
   */
  public function __construct(protected InvokableEngineContext $context) {}

  /**
   * Apply filters to the query.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return Builder
   */
  public function apply(Builder $builder): Builder
  {
    $this->builder = $builder;

    foreach ($this->context->getFilterAttributes() as $filter) {
      $value = $this->context->getRequest()->get($filter);

      if (($this->context->hasIgnoredEmptyValues() || config('filterable.engines.invokeable.ignore_empty_values')) && !$value) {
        continue;
      }

      $method = $this->getMethodName($filter);

      $this->initializeFilters($filter, $method, $value);
    }

    return $this->builder;
  }

  /**
   * Initialize the filter methods and resolve value.
   * @param string $method
   * @param mixed $value
   * @return void
   */
  protected function initializeFilters(string $key, string $method, mixed $value): void
  {
    $value = ConditionNormalizer::normalize($value, '=');

    foreach ($value as $operator => $val) {
      if (method_exists($this->context, $method)) {

        $payload = new Payload($key, $operator, $this->resolveValueSanitizer($key, $val), $val);

        $this->forwardCallTo($this->context, $method, [$payload]);
      }
    }
  }

  /**
   * Run the filter value sanitizer if exist.
   * @param string $key
   * @param string $method
   * @param mixed $value
   */
  protected function resolveValueSanitizer(string $key, mixed $value)
  {
    $sanitizer = $this->context->getSanitizerInstance();

    if (!empty($sanitizer->getSanitizers())) {
      $value = $sanitizer->handle($key, $value);
    }

    return $value;
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
}
