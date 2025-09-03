<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers\Contracts\AttributeHandlerInterface;

class RequiredHandler implements AttributeHandlerInterface
{
  /**
   * @inheritDoc
   */
  public function handle(AttributeContext $context, object $attribute): void
  {
    if (! $attribute instanceof Required) {
      return;
    }

    /** @var \Kettasoft\Filterable\Support\Payload $payload */
    $payload = $context->payload;

    if ($payload && ($payload->isEmpty() || $payload->isNull())) {
      throw new \InvalidArgumentException(sprintf($attribute->message, $context->state['key']));
    }
  }
}
