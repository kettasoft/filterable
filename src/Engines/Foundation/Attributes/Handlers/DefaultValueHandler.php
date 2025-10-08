<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers;

use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\DefaultValue;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers\Contracts\AttributeHandlerInterface;

class DefaultValueHandler implements AttributeHandlerInterface
{
  /**
   * @inheritDoc
   */
  public function handle(AttributeContext $context, $attribute): void
  {
    if (! $attribute instanceof DefaultValue) {
      return;
    }

    /** @var Payload $payload */
    $payload = $context->payload;

    if ($payload && $payload->isEmpty()) {
      $context->payload->setValue($attribute->value);
    }
  }
}
