<?php

namespace Kettasoft\Filterable\Foundation\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder;

trait HandleFluentReturn
{
  /**
   * Processes the result of a forwarded call to the builder.
   *
   * If the result is an instance of Builder, it updates the internal builder
   * reference and returns $this for fluent chaining. Otherwise, it returns the result as-is.
   *
   * @param string $method The method name that was called.
   * @param array $args The arguments passed to the method.
   * @return mixed Returns $this if the result is a Builder, otherwise returns the original result.
   */
  protected function handleFluentReturn($method, $args)
  {
    $result = $this->forwardCallTo($this->getBuilder(), $method, $args);

    if ($result instanceof Builder) {
      $this->setBuilder($result);
      return $this;
    }

    return $result;
  }
}
