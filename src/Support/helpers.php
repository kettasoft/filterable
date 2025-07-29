<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;

if (! function_exists('filterable')) {
  /**
   * Create a new Filterable instance.
   * @param Illuminate\Http\Request|null $request
   * @param Kettasoft\Filterable\Filterable|string $context
   * @param array $
   * @return Filterable|object
   */
  function filterable(Request|null $request = null, Filterable|string $context = null): Filterable
  {
    if (func_num_args() === 0) {
      return Filterable::create();
    }

    if (is_string($context) && class_exists($context) && is_subclass_of($context, Filterable::class)) {
      return new $context($request);
    }

    if ($context instanceof Filterable) {
      return $context->withRequest($request);
    }

    return Filterable::create($request);
  }
}
