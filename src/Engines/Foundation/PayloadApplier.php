<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;
use Kettasoft\Filterable\Support\Payload;

class PayloadApplier implements Appliable
{
  /**
   * PayloadApplier constructor
   * @param \Kettasoft\Filterable\Support\Payload $payload
   */
  public function __construct(protected Payload $payload) {}

  /**
   * Apply a Payload to the query builder.
   * @param \Illuminate\Contracts\Database\Eloquent\Builder $builder
   * @return \Illuminate\Contracts\Database\Eloquent\Builder
   */
  public function apply(Builder $builder): Builder
  {
    if ($this->isRelational()) {
      return $this->applyRelational($builder);
    }

    return $this->applyDirect($builder);
  }

  /**
   * Check if the payload field is relational.
   * @return bool
   */
  protected function isRelational(): bool
  {
    return is_string($this->payload->field) && str_contains($this->payload->field, '.');
  }

  /**
   * Apply a direct (non-relational) payload to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  protected function applyDirect(Builder $builder)
  {
    return $builder->where($this->payload->field, $this->payload->operator, $this->payload->value);
  }

  /**
   * Apply a relational payload to the query.
   * @param \Illuminate\Database\Eloquent\Builder $builder
   * @return Builder
   */
  protected function applyRelational(Builder $builder)
  {
    [$relation, $field] = explode('.', $this->payload->field, 2);

    return $builder->whereHas($relation, function ($query) use ($field) {
      $query->where($field, $this->payload->operator, $this->payload->value);
    });
  }
}
