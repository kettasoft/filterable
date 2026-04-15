<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class DefaultValue implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * Constructor for DefaultValue attribute.
     *
     * @param mixed $value The default value to be used if none is provided.
     */
    public function __construct(public mixed $value)
    {
    }

    /**
     * Get the stage at which this attribute should be applied.
     *
     * @return int
     */
    public static function stage(): int
    {
        return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::TRANSFORM->value;
    }

    /**
     * Handle the attribute logic.
     *
     * @param \Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context
     *
     * @return void
     */
    public function handle(\Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext $context): void
    {
        /** @var \Kettasoft\Filterable\Support\Payload $payload */
        $payload = $context->payload;

        if ($payload->isEmpty() || $payload->isNull()) {
            $payload->setValue($this->value);
        }
    }
}
