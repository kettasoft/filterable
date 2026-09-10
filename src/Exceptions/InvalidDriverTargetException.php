<?php

namespace Kettasoft\Filterable\Exceptions;

use InvalidArgumentException;

class InvalidDriverTargetException extends InvalidArgumentException
{
    /**
     * Create an exception for a backend query unsupported by a driver.
     *
     * @param string $driver Driver that rejected the target.
     * @param object $target Backend query object supplied to the driver.
     * @param string $expected Expected target contract or class name.
     */
    public function __construct(string $driver, object $target, string $expected)
    {
        $targetClass = $target::class;

        parent::__construct(
            "Filterable driver [{$driver}] cannot operate on [{$targetClass}]; expected [{$expected}]."
        );
    }
}
