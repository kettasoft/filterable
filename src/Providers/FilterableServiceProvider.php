<?php

namespace Kettasoft\Filterable\Providers;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Kettasoft\Filterable\Filterable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Kettasoft\Filterable\Commands\MakeFilterCommand;
use Kettasoft\Filterable\Commands\TestFilterCommand;
use Kettasoft\Filterable\Commands\ListFiltersCommand;
use Kettasoft\Filterable\Commands\InspectFilterCommand;
use Kettasoft\Filterable\Commands\SetupFilterableCommand;
use Kettasoft\Filterable\Commands\FilterableDiscoverCommand;
use Kettasoft\Filterable\Foundation\Events\FilterableEventManager;
use Kettasoft\Filterable\Foundation\Caching\FilterableCacheManager;
use Kettasoft\Filterable\Foundation\Caching\CacheInvalidationObserver;
use Kettasoft\Filterable\Foundation\Profiler\Storage\FileProfilerStorage;
use Kettasoft\Filterable\Foundation\Profiler\Storage\DatabaseProfilerStorage;
use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;

/**
 * Service provider for the Kettasoft Filterable package.
 * 
 * This provider handles:
 * - Configuration publishing and merging
 * - Service bindings and singletons
 * - Command registration
 * - Stub publishing
 * - Profiler storage configuration
 * 
 * @package Kettasoft\Filterable\Providers
 * @method void registerCustomEngines()
 * @method void registerCustomSanitizers()
 * @method void registerAdditionalServices()
 * @method void bootCustomizations()
 * @method void registerMiddleware()
 * @method void registerEventListeners()
 */
class FilterableServiceProvider extends ServiceProvider
{
    /**
     * Configuration file path relative to package root.
     */
    private const CONFIG_PATH = __DIR__ . '/../../config/filterable.php';

    /**
     * Stubs directory path relative to package root.
     */
    private const STUBS_PATH = __DIR__ . '/../../stubs/';

    /**
     * Package configuration key.
     */
    private const CONFIG_KEY = 'filterable';

    /**
     * Facade binding key.
     */
    private const FACADE_BINDING = 'filterable';

    /**
     * Supported profiler storage drivers.
     */
    private const PROFILER_DRIVERS = [
        'database' => DatabaseProfilerStorage::class,
        'log' => FileProfilerStorage::class,
    ];

    /**
     * Bootstrap any application services.
     * 
     * This method is called after all providers have been registered.
     * It handles asset publishing, command registration, and other
     * bootstrap operations that depend on the container.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishAssets();
        $this->registerCommands();
        $this->registerCacheInvalidationObservers();
        $this->bootExtensions();
    }

    /**
     * Register any application services.
     * 
     * This method is called during the registration phase and should
     * only register bindings in the container. It should not perform
     * any operations that depend on other services being available.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfiguration();
        $this->registerCoreBindings();
        $this->registerEventManager();
        $this->registerCacheManager();
        $this->registerProfilerStorage();
        $this->registerExtensions();
    }

    /**
     * Publish package assets (config files, stubs, etc.).
     * 
     * Separates different types of publishable assets with clear
     * tags for selective publishing.
     *
     * @return void
     */
    protected function publishAssets(): void
    {
        // Publish configuration file
        $this->publishes([
            self::CONFIG_PATH => config_path(self::CONFIG_KEY . '.php'),
        ], [self::CONFIG_KEY . '-config', 'config']);

        // Publish stub files for code generation
        $this->publishes([
            self::STUBS_PATH => base_path('stubs'),
        ], [self::CONFIG_KEY . '-stubs', 'stubs']);

        // Allow publishing all assets at once
        $this->publishes([
            self::CONFIG_PATH => config_path(self::CONFIG_KEY . '.php'),
            self::STUBS_PATH => base_path('stubs'),
        ], self::CONFIG_KEY);
    }

    /**
     * Merge package configuration with application configuration.
     * 
     * This ensures package defaults are available even if the user
     * hasn't published the config file.
     *
     * @return void
     */
    protected function mergeConfiguration(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_KEY);
    }

    /**
     * Register core service bindings.
     * 
     * Registers the main Filterable class as a singleton to ensure
     * consistent state across the application request lifecycle.
     *
     * @return void
     */
    protected function registerCoreBindings(): void
    {
        // Register the main Filterable class as a singleton
        $this->app->singleton(self::FACADE_BINDING, function (Application $app): Filterable {
            return new Filterable($app->make(Request::class));
        });

        // Register the Filterable class itself for dependency injection
        $this->app->bind(Filterable::class, function (Application $app): Filterable {
            return new Filterable($app->make(Request::class));
        });

        // Register alias for facade
        if (!class_exists('Filterable')) {
            class_alias(\Kettasoft\Filterable\Facades\Filterable::class, 'Filterable');
        }
    }

    /**
     * Register the FilterableEventManager as a singleton.
     * 
     * This ensures that only one instance of the event manager exists
     * throughout the application lifecycle, maintaining consistent
     * event listener registration across the entire application.
     *
     * @return void
     */
    protected function registerEventManager(): void
    {
        $this->app->singleton(FilterableEventManager::class, function (Application $app): FilterableEventManager {
            return FilterableEventManager::getInstance(config('filterable.events', []));
        });

        // Register alias for easy access
        $this->app->alias(FilterableEventManager::class, 'filterable.events');
    }

    /**
     * Register the FilterableCacheManager as a singleton.
     * 
     * This ensures that only one instance of the cache manager exists
     * throughout the application lifecycle, providing consistent caching
     * behavior across all filterable instances.
     *
     * @return void
     */
    protected function registerCacheManager(): void
    {
        $this->app->singleton(FilterableCacheManager::class, function (Application $app): FilterableCacheManager {
            return FilterableCacheManager::getInstance();
        });

        // Register alias for easy access
        $this->app->alias(FilterableCacheManager::class, 'filterable.cache');
    }

    /**
     * Register profiler storage implementations.
     * 
     * Uses a factory pattern to create storage instances based on
     * configuration, with clear error handling for invalid drivers.
     *
     * @return void
     * @throws InvalidArgumentException When an unsupported storage driver is configured
     */
    protected function registerProfilerStorage(): void
    {
        $this->app->bind(ProfilerStorageContract::class, function (Application $app): ProfilerStorageContract {
            $driver = config('filterable.profiler.store', 'database');

            if (!isset(self::PROFILER_DRIVERS[$driver])) {
                throw new InvalidArgumentException(
                    "Unsupported profiler storage driver [{$driver}]. " .
                        "Supported drivers are: " . implode(', ', array_keys(self::PROFILER_DRIVERS))
                );
            }

            $storageClass = self::PROFILER_DRIVERS[$driver];

            return $app->make($storageClass);
        });
    }

    /**
     * Register cache invalidation observers for auto-invalidation.
     * 
     * Sets up automatic cache invalidation when configured models change.
     *
     * @return void
     */
    protected function registerCacheInvalidationObservers(): void
    {
        CacheInvalidationObserver::register();
    }

    /**
     * Register Artisan commands.
     * 
     * Only registers commands when running in console to avoid
     * unnecessary overhead in web requests.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MakeFilterCommand::class,
            ListFiltersCommand::class,
            InspectFilterCommand::class,
            FilterableDiscoverCommand::class,
            TestFilterCommand::class,
            SetupFilterableCommand::class,
        ]);
    }

    /**
     * Register package extensions and hooks.
     * 
     * Provides extensibility points for developers to customize
     * package behavior without modifying core files.
     *
     * @return void
     */
    protected function registerExtensions(): void
    {
        // Hook for custom engine registration
        if (method_exists($this, 'registerCustomEngines')) {
            $this->registerCustomEngines();
        }

        // Hook for custom sanitizer registration
        if (method_exists($this, 'registerCustomSanitizers')) {
            $this->registerCustomSanitizers();
        }

        // Hook for additional service bindings
        if (method_exists($this, 'registerAdditionalServices')) {
            $this->registerAdditionalServices();
        }
    }

    /**
     * Boot package extensions and perform post-registration setup.
     * 
     * Provides boot-time extensibility points for operations that
     * require the full container to be available.
     *
     * @return void
     */
    protected function bootExtensions(): void
    {
        // Hook for post-boot customizations
        if (method_exists($this, 'bootCustomizations')) {
            $this->bootCustomizations();
        }

        // Hook for middleware registration
        if (method_exists($this, 'registerMiddleware')) {
            $this->registerMiddleware();
        }

        // Hook for event listener registration
        if (method_exists($this, 'registerEventListeners')) {
            $this->registerEventListeners();
        }
    }

    /**
     * Get the services provided by the provider.
     * 
     * This method helps Laravel optimize the container by knowing
     * which services this provider offers.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            self::FACADE_BINDING,
            Filterable::class,
            FilterableEventManager::class,
            'filterable.events',
            ProfilerStorageContract::class,
        ];
    }

    /**
     * Determine if the provider is deferred.
     * 
     * Returns false to ensure the provider is always loaded since
     * it provides essential configuration merging and publishing.
     *
     * @return bool
     */
    public function isDeferred(): bool
    {
        return false;
    }
}
