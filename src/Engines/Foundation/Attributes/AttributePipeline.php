<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

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
   * @return void
   */
  public function process(Filterable $target, string $method): void
  {
    $handlers = $this->registry->getHandlersForMethod($target, $method);

    foreach ($handlers as [$handler, $attributeInstance]) {
      (new $handler)->handle($this->context, $attributeInstance);
    }
  }
}
