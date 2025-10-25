# Custom Engines

Filterable provides a powerful way to extend its functionality by creating and registering custom filtering engines. This feature enables you to implement your own filtering logic while maintaining compatibility with the package's architecture.

[[toc]]

## Introduction

Custom engines allow you to create specialized filtering logic that fits your specific needs while leveraging the Filterable package's infrastructure. Each custom engine can implement its own filtering strategy while maintaining consistent integration with the rest of the package.

## Creating a Custom Engine

To create a custom engine, you need to extend the base `Engine` class and implement its required methods:

```php
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Illuminate\Database\Eloquent\Builder;

class CustomEngine extends Engine
{
    public function execute(Builder $builder): Builder
    {
        // Implement your custom filtering logic here
        return $builder;
    }

    protected function isStrictFromConfig(): bool
    {
        // Define if the engine should use strict mode by default
        return false;
    }

    protected function getAllowedFieldsFromConfig(): array
    {
        // Define which fields are allowed for filtering
        return [];
    }

    protected function isIgnoredEmptyValuesFromConfig(): bool
    {
        // Define if empty values should be ignored
        return false;
    }

    public function getEngineName(): string
    {
        // Return a unique name for your engine
        return 'custom';
    }

    public function defaultOperator()
    {
        // Define the default operator for filtering
        return '=';
    }

    public function getOperatorsFromConfig(): array
    {
        // Define supported operators
        return ['=', '>', '<', 'LIKE'];
    }
}
```

## Registering a Custom Engine

Once you have created your custom engine, you can register it using the `EngineManager::extend()` method:

```php
use Kettasoft\Filterable\Engines\Factory\EngineManager;

EngineManager::extend('custom', CustomEngine::class);
```

This registers your engine under the name 'custom', which you can then use throughout your application.

## Using a Custom Engine

After registration, you can use your custom engine in several ways:

1. Direct usage with the Filterable facade:

```php
use Kettasoft\Filterable\Facades\Filterable;

Filterable::useEngine('custom')->apply($query);
```

2. In a filter class:

```php
use Kettasoft\Filterable\Foundation\Filter;

class UserFilter extends Filter
{
    protected string $engine = 'custom';
}
```

## Built-in Engines

The package comes with several built-in engines:

-   `tree`: Tree-based filtering structure
-   `ruleset`: Rule-based filtering
-   `expression`: Expression-based filtering
-   `invokable`: Callback-based filtering

## Error Handling

The engine manager includes built-in validation to ensure that custom engines implement the required interface:

-   Attempting to register a class that doesn't extend `Engine` will throw an `InvalidArgumentException`
-   Using an unregistered engine name will throw an `InvalidArgumentException`

## Best Practices

1. **Naming Convention**: Use descriptive names for your custom engines that reflect their purpose
2. **Implementation**: Ensure your engine implements all required methods properly
3. **Testing**: Write comprehensive tests for your custom engine
4. **Documentation**: Document any specific behavior or requirements of your custom engine

## Example Implementation

Here's a complete example of implementing and using a custom engine:

```php
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Illuminate\Database\Eloquent\Builder;

class RangeEngine extends Engine
{
    public function execute(Builder $builder): Builder
    {
        $data = $this->getData();

        foreach ($data as $field => $range) {
            if (isset($range['min'])) {
                $builder->where($field, '>=', $range['min']);
            }
            if (isset($range['max'])) {
                $builder->where($field, '<=', $range['max']);
            }
        }

        return $builder;
    }

    public function getEngineName(): string
    {
        return 'range';
    }

    // ... implement other required methods
}

// Register the engine
EngineManager::extend('range', RangeEngine::class);

// Use in a filter
class PriceFilter extends Filter
{
    protected string $engine = 'range';
}
```

## Performance Considerations

When implementing a custom engine, consider the following performance aspects:

1. Query Optimization: Ensure your engine generates efficient SQL queries
2. Memory Usage: Be mindful of memory consumption in your filtering logic
3. Caching: Implement caching strategies where appropriate

## Limitations

-   Custom engines must extend the base `Engine` class
-   Engine names must be unique across your application
-   Some advanced features might require additional implementation in custom engines
