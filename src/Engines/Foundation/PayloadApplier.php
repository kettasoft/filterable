<?php

namespace Kettasoft\Filterable\Engines\Foundation;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Contracts\Appliable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\OperatorResolver;

class PayloadApplier implements Appliable
{
  protected OperatorResolver $operators;

  public function __construct(protected Payload $payload, ?OperatorResolver $operators = null)
  {
    $this->operators = $operators ?? OperatorResolver::fromConfig();
  }

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
    return $this->applyOperator($builder, $this->payload);
  }

  protected function applyRelational(Builder $builder): Builder
  {
    $segments = explode('.', $this->payload->field);
    $field = array_pop($segments);
    $relation = implode('.', $segments);

    return $builder->whereHas($relation, function (Builder $query) use ($field): Builder {
      $payload = clone $this->payload;

      return $this->applyOperator($query, $payload->setField($field));
    });
  }

  protected function applyOperator(Builder $builder, Payload $payload): Builder
  {
    return $this->operators->resolve($payload->operator)->apply($builder, $payload);
  }
}
