<?php

namespace Kettasoft\Filterable\Engines\Foundation\Mappers;

use Kettasoft\Filterable\Engines\Contracts\Mappable;
use Kettasoft\Filterable\Engines\Foundation\OperatorDefinition;
use Kettasoft\Filterable\Engines\Contracts\OperatorDefinitionContract;
use Kettasoft\Filterable\Foundation\Bags\OperatorBag;

class OperatorMapper implements Mappable
{
  /**
   * OperatorMapper constructor.
   * @param \Kettasoft\Filterable\Engines\Contracts\OperatorDefinitionContract $definition
   */
  public function __construct(protected OperatorDefinitionContract $definition, protected bool $strict) {}

  /**
   * Init OperatorMapper instance.
   * @param \Kettasoft\Filterable\Foundation\Bags\OperatorBag $bag
   * @return self
   */
  public static function init(OperatorBag $bag, bool $isStrict): self
  {
    return new self(new OperatorDefinition($bag, $isStrict), $isStrict);
  }

  /**
   * @inheritDoc
   */
  public function map(string|null $operator = null): string|null
  {
    return $this->definition->resolve($operator);
  }
}
