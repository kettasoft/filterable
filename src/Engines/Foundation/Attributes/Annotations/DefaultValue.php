<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DefaultValue
{
  /**
   * Constructor for DefaultValue attribute.
   * @param mixed $value The default value to be used if none is provided.
   */
  public function __construct(public mixed $value) {}
}
