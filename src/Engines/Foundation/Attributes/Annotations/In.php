<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class In implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * The allowed values for the parameter.
     *
     * @var array
     */
    protected array $values;

    /**
     * Constructor for In attribute.
     *
     * @param array $values The allowed values for the parameter.
     */
    public function __construct(...$values)
    {
        $this->values = $values;
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

        if ($payload->notIn($this->values)) {
            throw new \Kettasoft\Filterable\Engines\Exceptions\SkipExecution(
                "The value '{$payload->value}' is not in the allowed set: ".implode(', ', $this->values)
            );
        }
    }
}
