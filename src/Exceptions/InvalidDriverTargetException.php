<?php

namespace Kettasoft\Filterable\Exceptions;

use InvalidArgumentException;

class InvalidDriverTargetException extends InvalidArgumentException
{
    public function __construct(string $driver, object $target, string $expected)
    {
        $targetClass = $target::class;

        parent::__construct(
            "Filterable driver [{$driver}] cannot operate on [{$targetClass}]; expected [{$expected}]."
        );
    }
}
