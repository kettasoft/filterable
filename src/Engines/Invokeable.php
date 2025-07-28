<?php

namespace Kettasoft\Filterable\Engines;

use Illuminate\Support\Str;
use Kettasoft\Filterable\Support\Payload;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Support\ConditionNormalizer;

class Invokeable extends Engine
{
  use ForwardsCalls;

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
  public function execute(Builder $builder): Builder
  {
    $this->builder = $builder;

    foreach ($this->context->getFilterAttributes() as $filter) {
      $value = $this->context->getRequest()->get($filter);

      if (($this->context->hasIgnoredEmptyValues() || config('filterable.engines.invokable.ignore_empty_values')) && !$value) {
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
    $clause = ConditionNormalizer::normalize($value, '=');

    $operator = $clause['operator'];
    $val = $clause['value'];

    if (method_exists($this->context, $method)) {

      $payload = new Payload($key, $operator, $this->resolveValueSanitizer($key, $val), $val);

      $this->forwardCallTo($this->context, $method, [$payload]);
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

  /**
   * Get allowed fields to filtering.
   * @return array
   */
  protected function getAllowedFieldsFromConfig(): array
  {
    return config('filterable.engines.invokable.allowed_fields', []);
  }

  public function getOperatorsFromConfig(): array
  {
    return config('filterable.engines.invokable.allowed_operators', []);
  }

  public function isStrictFromConfig(): bool
  {
    return config('filterable.engines.invokable.strict', true);
  }

  /**
   * Get engine default operator.
   * @return string
   */
  public function defaultOperator(): string
  {
    return config('filterable.engines.invokable.default_operator', 'eq');
  }
}
