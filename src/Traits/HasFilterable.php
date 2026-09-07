<?php

namespace Kettasoft\Filterable\Traits;

/**
 * @deprecated Use {@see InteractsWithFilterable} instead.
 *
 * @method static \Kettasoft\Filterable\Foundation\Invoker|\Illuminate\Contracts\Database\Eloquent\Builder filter(\Kettasoft\Filterable\Filterable|string|array|null $filter = null)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasFilterable
{
  use InteractsWithFilterable;
}
