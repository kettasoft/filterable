# Filterable Service Provider Overview

This guide demonstrates how to use the `FilterableServiceProvider` in a Laravel application.

## Publishing Assets

```bash
# Publish only configuration
php artisan vendor:publish --tag=filterable-config

# Publish only stubs
php artisan vendor:publish --tag=filterable-stubs

# Publish all filterable assets
php artisan vendor:publish --tag=filterable
```

### Service Resolution

```php
// Via Facade (requires facade registration)
use Filterable;

$filterable = Filterable::create();

// Via Container
$filterable = app('filterable');

// Via Dependency Injection
public function index(Filterable $filterable)
{
    return $filterable->setModel(User::class)->apply();
}

// Via Manual Resolution
$filterable = app(Filterable::class);
```

## Advanced Customization

### Creating an Extended Service Provider

Create `app/Providers/CustomFilterableServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Kettasoft\Filterable\Providers\FilterableServiceProvider;
use Kettasoft\Filterable\Engines\Factory\EngineManager;
use App\Filters\Engines\GraphQLEngine;
use App\Filters\Sanitizers\SqlInjectionSanitizer;
use App\Http\Middleware\FilterableAuthMiddleware;

class CustomFilterableServiceProvider extends FilterableServiceProvider
{
    /**
     * Register custom filtering engines.
     */
    protected function registerCustomEngines(): void
    {
        // Register a GraphQL-compatible engine
        EngineManager::extend('graphql', GraphQLEngine::class);
        EngineManager::extend('custom', CustomEngine::class);
    }

    /**
     * Register custom sanitizers.
     */
    protected function registerCustomSanitizers(): void
    {
        // Register additional security sanitizers
        $this->app->bind('filterable.sanitizer.sql_injection', SqlInjectionSanitizer::class);

        // Register in sanitizer registry if using a registry pattern
        $this->app->make('filterable.sanitizer.registry')
            ->register('sql_injection', SqlInjectionSanitizer::class);
    }

    /**
     * Register additional services.
     */
    protected function registerAdditionalServices(): void
    {
        // Custom field resolver for dynamic schema
        $this->app->singleton('filterable.field_resolver', function ($app) {
            return new DynamicFieldResolver(
                $app['db'],
                $app['cache.store']
            );
        });

        // Custom authorization service
        $this->app->singleton('filterable.authorizer', function ($app) {
            return new FilterableAuthorizer(
                $app['auth'],
                $app['gate']
            );
        });
    }

    /**
     * Boot customizations.
     */
    protected function bootCustomizations(): void
    {
        // Register custom macros
        Filterable::macro('withDynamicFields', function () {
            $resolver = app('filterable.field_resolver');
            $fields = $resolver->resolveForModel($this->getModel());
            return $this->setAllowedFields($fields);
        });

        // Configure custom engines based on environment
        if (config('app.env') === 'testing') {
            // Use a mock engine for testing
            $this->app->singleton('filterable.engine.mock', MockEngine::class);
        }
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register authorization middleware
        $router->aliasMiddleware('filterable.auth', FilterableAuthMiddleware::class);

        // Register rate limiting middleware
        $router->aliasMiddleware('filterable.throttle', FilterableThrottleMiddleware::class);
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Listen to filterable events for logging/monitoring
        Event::listen('filterable.query.executed', function ($event) {
            Log::info('Filterable query executed', [
                'model' => $event->model,
                'filters' => $event->filters,
                'execution_time' => $event->executionTime,
            ]);
        });

        // Listen for security events
        Event::listen('filterable.security.violation', function ($event) {
            Log::warning('Filterable security violation detected', [
                'ip' => request()->ip(),
                'filters' => $event->filters,
                'violation' => $event->violation,
            ]);
        });
    }
}
```

### Update config/app.php

Replace the original service provider:

```php
'providers' => [
    // Remove this line:
    // Kettasoft\Filterable\Providers\FilterableServiceProvider::class,

    // Add this line:
    App\Providers\CustomFilterableServiceProvider::class,
],
```

## Configuration Examples

### Environment-Specific Configuration

```bash
# .env
FILTERABLE_PROFILER_ENABLED=true
FILTERABLE_PROFILER_STORE=log
FILTERABLE_PROFILER_THRESHOLD=500

# .env.testing
FILTERABLE_PROFILER_ENABLED=false
FILTERABLE_ENGINE_DEFAULT=mock
```

---

## Performance Considerations

### Caching Dynamic Fields

```php
protected function registerAdditionalServices(): void
{
    $this->app->singleton('filterable.field_resolver', function ($app) {
        return new CachedFieldResolver(
            new DynamicFieldResolver($app['db']),
            $app['cache.store'],
            3600 // Cache for 1 hour
        );
    });
}
```

### Lazy Loading Engines

```php
protected function registerCustomEngines(): void
{
    $this->app->singleton('filterable.engine.heavy', function ($app) {
        // Only instantiate when actually needed
        return new HeavyProcessingEngine(
            $app['config']['filterable.heavy_engine']
        );
    });
}
```

## Monitoring and Debugging

### Enable Detailed Logging

```php
protected function registerEventListeners(): void
{
    if (config('app.debug')) {
        Event::listen('filterable.*', function ($event, $data) {
            Log::debug('Filterable event', [
                'event' => $event,
                'data' => $data,
                'memory' => memory_get_usage(true),
                'time' => microtime(true),
            ]);
        });
    }
}
```

### Custom Profiler Storage

```php
protected function registerAdditionalServices(): void
{
    $this->app->bind(ProfilerStorageContract::class, function ($app) {
        $driver = config('filterable.profiler.store');

        return match ($driver) {
            'database' => new DatabaseProfilerStorage(),
            'log' => new FileProfilerStorage(),
            'redis' => new RedisProfilerStorage($app['redis']),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}")
        };
    });
}
```

This service provider provides a solid foundation for building robust, maintainable, and extensible filtering functionality in Laravel applications.
