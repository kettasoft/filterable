<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Contracts\Outcome;
use Kettasoft\Filterable\Engines\Foundation\Execution;
use Kettasoft\Filterable\Filterable;

class AttributePipeline
{
  /**
   * Create a new attribute pipeline instance.
   *
   * @param AttributeRegistry $registry
   * @param AttributeContext $context
   */
  public function __construct(protected AttributeRegistry $registry, protected AttributeContext $context) {}

  /**
   * Process the attributes for the given target and method.
   *
   * @param object|string $target
   * @return \Kettasoft\Filterable\Engines\Foundation\Contracts\Outcome
   */
  public function process(Filterable $target, string $method): Outcome
  {
    $execution = new Execution();

    try {
      $handlers = $this->registry->getHandlersForMethod($target, $method);

      foreach ($handlers as [$handler, $attributeInstance]) {
        (new $handler)->handle($this->context, $attributeInstance);
      }
    } catch (\Exception $e) {
      $execution->fail($e);
    }

    return $execution;
  }
}
