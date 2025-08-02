<?php

namespace Kettasoft\Filterable\Foundation\Traits;

use Kettasoft\Filterable\Foundation\Contracts\QueryBuilderInterface;

trait HandleFluentReturn
{
  /**
   * Processes the result of a forwarded call to the builder.
   *
   * If the result is an instance of Builder, it updates the internal builder
   * reference and returns $this for fluent chaining. Otherwise, it returns the result as-is.
   *
   * @param mixed $result The result returned from the forwarded call.
   * @return mixed Returns $this if the result is a Builder, otherwise returns the original result.
   */
  protected function handleFluentReturn($method, $args)
  {

    $result = $this->forwardCallTo($this->builder, $method, $args);

    if ($result instanceof QueryBuilderInterface) {
      $this->builder = $result;
      return $this;
    }

    return $result;
  }
}
