<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Handlers\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;

interface AttributeHandlerInterface
{
  /**
   * Handle the attribute logic.
   *
   * @param AttributeContext $context 
   * @param object $attribute
   * @return void
   */
  public function handle(AttributeContext $context, object $attribute): void;
}
