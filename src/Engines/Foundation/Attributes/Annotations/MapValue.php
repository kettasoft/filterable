<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MapValue implements \Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute
{
    /**
     * The value mapping.
     *
     * @var array<string, mixed>
     */
    protected array $map;

    /**
     * Whether to skip the filter if the value is not in the map.
     *
     * @var bool
     */
    protected bool $strict;

    /**
     * Constructor for MapValue attribute.
     *
     * @param array<string, mixed> $map    The value mapping (e.g., ['active' => 1, 'inactive' => 0]).
     * @param bool                 $strict If true, skip execution when value is not found in map. Defaults to false.
     */
    public function __construct(array $map, bool $strict = false)
    {
        $this->map = $map;
        $this->strict = $strict;
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

        $key = (string) $payload->value;

        if (array_key_exists($key, $this->map)) {
            $payload->setValue($this->map[$key]);

            return;
        }

        if ($this->strict) {
            throw new \Kettasoft\Filterable\Engines\Exceptions\SkipExecution(
                "The value '{$key}' is not in the value map: ".implode(', ', array_keys($this->map))
            );
        }
    }
}
