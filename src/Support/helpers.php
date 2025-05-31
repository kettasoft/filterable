<?php

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;

if (! function_exists('filterable')) {
  /**
   * Create a new Filterable instance.
   * @param Illuminate\Http\Request|null $request
   * @return Filterable
   */
  function filterable(Request|null $request = null): Filterable
  {
    return Filterable::create($request);
  }
}
