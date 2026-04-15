<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;
use Kettasoft\Filterable\Filterable;
use ReflectionMethod;

class AttributeRegistry
{
    /**
     * Get handlers for the given method of a filterable class.
     *
     * @param Filterable $filterable
     * @param string     $method
     *
     * @return array<int, MethodAttribute>
     */
    public function getHandlersForMethod(Filterable $filterable, string $method): array
    {
        $reflection = new ReflectionMethod($filterable, $method);

        $resolved = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if (!$instance instanceof MethodAttribute) {
                continue;
            }

            $resolved[] = $instance;
        }

        usort(
            $resolved,
            fn ($a, $b) => $a::stage() <=> $b::stage()
        );

        return $resolved;
    }
}
