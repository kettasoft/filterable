<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Explode implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * Constructor for Explode attribute.
     *
     * @param string $delimiter The delimiter to use for exploding the parameter value.
     */
    public function __construct(public string $delimiter = ',')
    {
    }

    /**
     * Get the stage at which this attribute should be applied.
     *
     * @return int
     */
    public static function stage(): int
    {
        return \Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage::VALIDATE->value;
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

        $payload->explode($this->delimiter, true);
    }
}
