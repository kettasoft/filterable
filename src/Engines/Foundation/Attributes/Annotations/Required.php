<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Required
{
  public function __construct(public string $message = "The parameter '%s' is required.") {}
}
