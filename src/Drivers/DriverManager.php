<?php

namespace Kettasoft\Filterable\Drivers;

use Kettasoft\Filterable\Drivers\Contracts\Driver;
use Kettasoft\Filterable\Exceptions\InvalidDriverDefinitionException;

class DriverManager
{
    /**
     * Runtime driver extensions keyed by alias.
     *
     * @var array<string, class-string<Driver>>
     */
    private static array $extensions = [];

    /**
     * Resolve a driver instance by name or class.
     *
     * @param Driver|string|null $driver
     *
     * @throws InvalidDriverDefinitionException
     */
    public static function resolve(Driver|string|null $driver = null): Driver
    {
        if ($driver instanceof Driver) {
            return $driver;
        }

        $name = $driver ?? config('filterable.default_driver', 'database');
        $definition = self::$extensions[$name]
            ?? config("filterable.drivers.{$name}")
            ?? (is_a($name, Driver::class, true) ? $name : null);

        if (! is_string($definition) || ! is_a($definition, Driver::class, true)) {
            throw new InvalidDriverDefinitionException($name, $definition);
        }

        $resolved = app($definition);

        if (! $resolved instanceof Driver) {
            throw new InvalidDriverDefinitionException($name, $resolved);
        }

        return $resolved;
    }

    /**
     * Register a new driver extension.
     * 
     * @param class-string<Driver> $driver
     * 
     * @throws InvalidDriverDefinitionException
     */
    public static function extend(string $name, string $driver): void
    {
        if (! is_a($driver, Driver::class, true)) {
            throw new InvalidDriverDefinitionException($name, $driver);
        }

        self::$extensions[$name] = $driver;
    }
}
