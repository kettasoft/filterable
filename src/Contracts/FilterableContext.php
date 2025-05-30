<?php

namespace Kettasoft\Filterable\Contracts;

use Kettasoft\Filterable\Engines\Contracts\{
  TreeFilterableContext,
  RulesetFilterableContect,
  ExpressionEngineContext,
  InvokableEngineContext
};

interface FilterableContext extends TreeFilterableContext, RulesetFilterableContect, ExpressionEngineContext, InvokableEngineContext {}
