<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\DefaultValue;
use ReflectionMethod;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers\Contracts\AttributeHandlerInterface;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers\DefaultValueHandler;

class AttributeRegistry
{
  /**
   * @var array<string, AttributeHandlerInterface>
   */
  protected array $handlers = [
    DefaultValue::class => DefaultValueHandler::class
  ];

  /**
   * Register an attribute handler.
   * @param string $attributeClass
   * @param AttributeHandlerInterface $handler
   * @return void
   */
  public function register(string $attributeClass, AttributeHandlerInterface $handler): void
  {
    $this->handlers[$attributeClass] = $handler;
  }

  /**
   * Get handlers for the given method of a filterable class.
   *
   * @param Filterable $filterable
   * @param string $method
   * @return array<int, array{0: string, 1: object}>
   */
  public function getHandlersForMethod(Filterable $filterable, string $method): array
  {
    $reflection = new ReflectionMethod($filterable, $method);
    $attributes = $reflection->getAttributes();
    $matchedHandlers = [];

    foreach ($attributes as $attribute) {
      $attrName = $attribute->getName();
      if (isset($this->handlers[$attrName])) {
        $handler = $this->handlers[$attrName];
        $matchedHandlers[] = [$handler, $attribute->newInstance()];
      }
    }

    return $matchedHandlers;
  }
}
