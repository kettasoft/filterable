<?php

namespace Tests\Unit\Providers;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Kettasoft\Filterable\Filterable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Commands\MakeFilterCommand;
use Kettasoft\Filterable\Providers\FilterableServiceProvider;
use Kettasoft\Filterable\Foundation\Events\FilterableEventManager;
use Kettasoft\Filterable\Foundation\Profiler\Storage\FileProfilerStorage;
use Kettasoft\Filterable\Foundation\Profiler\Storage\DatabaseProfilerStorage;
use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;

/**
 * Test suite for FilterableServiceProvider.
 * 
 * Tests the service provider's registration, configuration merging,
 * binding resolution, and extension hooks.
 */
class FilterableServiceProviderTest extends TestCase
{
    protected FilterableServiceProvider $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new FilterableServiceProvider($this->app);
    }

    /** @test */
    public function it_registers_filterable_as_singleton(): void
    {
        $this->provider->register();

        $this->assertTrue($this->app->bound('filterable'));

        $instance1 = $this->app->make('filterable');
        $instance2 = $this->app->make('filterable');

        $this->assertInstanceOf(Filterable::class, $instance1);
        $this->assertSame($instance1, $instance2, 'Filterable should be registered as singleton');
    }

    /** @test */
    public function it_registers_filterable_class_for_dependency_injection(): void
    {
        $this->provider->register();

        $this->assertTrue($this->app->bound(Filterable::class));

        $instance = $this->app->make(Filterable::class);

        $this->assertInstanceOf(Filterable::class, $instance);
    }

    /** @test */
    public function it_registers_database_profiler_storage_by_default(): void
    {
        config(['filterable.profiler.store' => 'database']);

        $this->provider->register();

        $storage = $this->app->make(ProfilerStorageContract::class);

        $this->assertInstanceOf(DatabaseProfilerStorage::class, $storage);
    }

    /** @test */
    public function it_registers_file_profiler_storage_when_configured(): void
    {
        config(['filterable.profiler.store' => 'log']);

        $this->provider->register();

        $storage = $this->app->make(ProfilerStorageContract::class);

        $this->assertInstanceOf(FileProfilerStorage::class, $storage);
    }

    /** @test */
    public function it_throws_exception_for_invalid_profiler_storage_driver(): void
    {
        config(['filterable.profiler.store' => 'invalid_driver']);

        $this->provider->register();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported profiler storage driver [invalid_driver]');

        $this->app->make(ProfilerStorageContract::class);
    }

    /** @test */
    public function it_registers_commands_when_running_in_console(): void
    {
        $app = \Mockery::mock($this->app)->makePartial();
        $app->shouldReceive('runningInConsole')->andReturn(true);

        $this->provider = new FilterableServiceProvider($app);
        $this->provider->boot();

        $commands = Artisan::all();

        $this->assertArrayHasKey('make:filter', $commands);
    }

    /** @test */
    public function it_provides_expected_services(): void
    {
        $expectedServices = [
            'filterable',
            Filterable::class,
            FilterableEventManager::class,
            'filterable.events',
            ProfilerStorageContract::class,
        ];

        $providedServices = $this->provider->provides();

        $this->assertEquals($expectedServices, $providedServices);
    }

    /** @test */
    public function it_is_not_deferred(): void
    {
        $this->assertFalse($this->provider->isDeferred());
    }

    /** @test */
    public function it_merges_configuration_correctly(): void
    {
        $this->provider->register();

        // Check that configuration is available
        $this->assertNotNull(config('filterable'));
        $this->assertIsArray(config('filterable'));
    }

    /** @test */
    public function filterable_receives_request_instance(): void
    {
        // Create a real Request instance with empty data
        $request = Request::create('/test', 'GET', []);

        $this->app->instance(Request::class, $request);

        $this->provider->register();

        $filterable = $this->app->make('filterable');

        $this->assertSame($request, $filterable->getRequest());
    }

    /** @test */
    public function extension_hooks_are_called_when_available(): void
    {
        $provider = new class ($this->app) extends FilterableServiceProvider {
            public bool $customEnginesRegistered = false;
            public bool $customSanitizersRegistered = false;
            public bool $additionalServicesRegistered = false;
            public bool $customizationsBooted = false;
            public bool $middlewareRegistered = false;
            public bool $eventListenersRegistered = false;

            protected function registerCustomEngines(): void
            {
                $this->customEnginesRegistered = true;
            }

            protected function registerCustomSanitizers(): void
            {
                $this->customSanitizersRegistered = true;
            }

            protected function registerAdditionalServices(): void
            {
                $this->additionalServicesRegistered = true;
            }

            protected function bootCustomizations(): void
            {
                $this->customizationsBooted = true;
            }

            protected function registerMiddleware(): void
            {
                $this->middlewareRegistered = true;
            }

            protected function registerEventListeners(): void
            {
                $this->eventListenersRegistered = true;
            }
        };

        $provider->register();
        $provider->boot();

        $this->assertTrue($provider->customEnginesRegistered);
        $this->assertTrue($provider->customSanitizersRegistered);
        $this->assertTrue($provider->additionalServicesRegistered);
        $this->assertTrue($provider->customizationsBooted);
        $this->assertTrue($provider->middlewareRegistered);
        $this->assertTrue($provider->eventListenersRegistered);
    }
}
