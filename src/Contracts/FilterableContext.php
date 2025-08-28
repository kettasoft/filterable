<?php

namespace Kettasoft\Filterable\Contracts;

use Kettasoft\Filterable\Engines\Contracts\{
  TreeFilterableContext,
  RulesetFilterableContect,
  ExpressionEngineContext,
  InvokableEngineContext
};
use Kettasoft\Filterable\Sanitization\Sanitizer;

interface FilterableContext extends TreeFilterableContext, RulesetFilterableContect, ExpressionEngineContext, InvokableEngineContext
{
  /**
   * Get sanitizer instance.
   * @return Sanitizer
   */
  public function getSanitizerInstance(): Sanitizer;
}
