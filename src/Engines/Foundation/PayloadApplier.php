<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;
use Kettasoft\Filterable\Support\Payload;

class PayloadApplier implements Appliable
{
  public function __construct(protected Payload $payload) {}

  public function apply(Builder $builder): Builder
  {
    if ($this->isRelational()) {
      return $this->applyRelational($builder);
    }

    return $this->applyDirect($builder);
  }

  protected function isRelational(): bool
  {
    return str_contains($this->payload->field, '.');
  }

  protected function applyDirect(Builder $builder): Builder
  {
    return $builder->where(
      $this->payload->field,
      $this->payload->operator,
      $this->payload->value
    );
  }

  protected function applyRelational(Builder $builder): Builder
  {
    $segments = explode('.', $this->payload->field);
    $field = array_pop($segments);
    $relation = implode('.', $segments);

    return $builder->whereHas($relation, function (Builder $query) use ($field): Builder {
      return $query->where($field, $this->payload->operator, $this->payload->value);
    });
  }
}
