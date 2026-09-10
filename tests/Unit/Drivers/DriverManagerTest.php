<?php

namespace Kettasoft\Filterable\Tests\Unit\Drivers;

use Kettasoft\Filterable\Drivers\Contracts\Driver;
use Kettasoft\Filterable\Drivers\DatabaseDriver;
use Kettasoft\Filterable\Drivers\DriverManager;
use Kettasoft\Filterable\Exceptions\InvalidDriverDefinitionException;
use Kettasoft\Filterable\Operations\Contracts\Operation;
use Kettasoft\Filterable\Tests\TestCase;

class DriverManagerTest extends TestCase
{
    public function test_it_resolves_the_default_database_driver(): void
    {
        $this->assertInstanceOf(DatabaseDriver::class, DriverManager::resolve());
    }

    public function test_it_resolves_a_configured_driver_alias(): void
    {
        config()->set('filterable.drivers.testing', FakeDriver::class);

        $this->assertInstanceOf(FakeDriver::class, DriverManager::resolve('testing'));
    }

    public function test_it_resolves_a_driver_class_or_instance(): void
    {
        $instance = new FakeDriver();

        $this->assertInstanceOf(FakeDriver::class, DriverManager::resolve(FakeDriver::class));
        $this->assertSame($instance, DriverManager::resolve($instance));
    }

    public function test_it_can_register_a_runtime_driver_extension(): void
    {
        DriverManager::extend('runtime-testing', FakeDriver::class);

        $this->assertInstanceOf(FakeDriver::class, DriverManager::resolve('runtime-testing'));
    }

    public function test_it_rejects_an_invalid_driver_definition(): void
    {
        config()->set('filterable.drivers.invalid', \stdClass::class);

        $this->expectException(InvalidDriverDefinitionException::class);

        DriverManager::resolve('invalid');
    }
}

class FakeDriver implements Driver
{
    public function apply(Operation $operation, object $query): object
    {
        return $query;
    }
}
