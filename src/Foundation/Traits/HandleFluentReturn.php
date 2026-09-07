<?php

namespace Kettasoft\Filterable\Foundation\Traits;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

trait HandleFluentReturn
{
  /**
   * Processes the result of a forwarded call to the builder.
   *
   * If the result is an instance of Builder, it updates the internal builder
   * reference and returns $this for fluent chaining. Otherwise, it returns the result as-is.
   *
   * @param string $method The forwarded method name.
   * @param array $args The forwarded method arguments.
   * @return mixed Returns $this if the result is a Builder, otherwise returns the original result.
   */
  protected function handleFluentReturn(string $method, array $args): mixed
  {
    $builder = method_exists($this, 'getBuilder')
      ? $this->getBuilder()
      : $this->builder;

    $result = $this->forwardCallTo($builder, $method, $args);

    if ($result instanceof EloquentBuilder || $result instanceof QueryBuilder) {
      if (method_exists($this, 'setBuilder')) {
        $this->setBuilder($result);
      } else {
        $this->builder = $result;
      }

      return $this;
    }

    return $result;
  }
}
