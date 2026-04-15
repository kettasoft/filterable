<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Trim implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * Constructor for Trim attribute.
     *
     * @param string $characters Optional characters to trim. Defaults to standard whitespace.
     * @param string $side       The side to trim: 'both', 'left', or 'right'. Defaults to 'both'.
     */
    public function __construct(
        public string $characters = " \t\n\r\0\x0B",
        public string $side = 'both'
    ) {
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

        if (!is_string($payload->value)) {
            return;
        }

        $payload->setValue(match ($this->side) {
            'left'  => ltrim($payload->value, $this->characters),
            'right' => rtrim($payload->value, $this->characters),
            default => trim($payload->value, $this->characters),
        });
    }
}
