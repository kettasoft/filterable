<?php

namespace Kettasoft\Filterable\Engines\Foundation\Resolvers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Engines\Foundation\Clause;
use Kettasoft\Filterable\Foundation\Bags\RelationBag;

class RelationResolver
{
  protected string $relationPath;
  protected string $field;

  public function __construct(protected RelationBag $bag, protected string $path)
  {
    $this->parse($path);
  }

  public function isAllowed()
  {
    if ($this->bag->has($this->relationPath)) {
      $relation = $this->bag->get($this->relationPath);
      // dd($relation);
      return is_array($relation) ? (in_array($this->field, $relation)) : true;
    }

    return false;
  }

  protected function parse(string $path)
  {
    $segments = explode('.', $this->path);
    $field = array_pop($segments);
    $path = implode('.', $segments);

    $this->field = $field;
    $this->relationPath = $path;
  }

  public function getRelationPath(): string
  {
    return $this->relationPath;
  }

  public function getField()
  {
    return $this->field;
  }

  public function resolve(Builder $builder, Clause $clause)
  {
    return $builder->whereHas($this->relationPath, function (Builder $sub) use ($clause): Builder {
      return $sub->where($this->field, $clause->getOperator(), $clause->getValue());
    });
  }
}
