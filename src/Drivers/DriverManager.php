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
     * Resolve a driver from an instance, configured alias, or class name.
     *
     * Passing null resolves the configured default driver. Driver classes are
     * instantiated through Laravel's service container.
     *
     * @param Driver|class-string<Driver>|string|null $driver Driver instance,
     *     configured alias, class name, or null for the default driver.
     * @return Driver The resolved driver instance.
     *
     * @throws InvalidDriverDefinitionException When the definition does not implement Driver.
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
     * Register a driver class under a runtime alias.
     *
     * Runtime extensions take precedence over configured driver definitions.
     *
     * @param string $name Alias used to resolve the driver.
     * @param class-string<Driver> $driver Driver implementation class.
     * @return void
     *
     * @throws InvalidDriverDefinitionException When the class does not implement Driver.
     */
    public static function extend(string $name, string $driver): void
    {
        if (! is_a($driver, Driver::class, true)) {
            throw new InvalidDriverDefinitionException($name, $driver);
        }

        self::$extensions[$name] = $driver;
    }
}
