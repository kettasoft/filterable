---
title: Filter Drivers
description: Select how Filterable operations are translated for the active query backend.
tags: [drivers, operations, database, architecture]
---

# Filter Drivers

A Driver decides how an approved filter operation is applied to a query backend. Engines remain responsible for understanding the request shape, validating fields and operators, and producing sanitized payloads.

```text
Request data
    ↓
Engine → validation and sanitization
    ↓
Comparison operation
    ↓
Driver
    ↓
Backend query
```

Filterable uses the Eloquent database Driver by default, so existing applications do not need to change their configuration or filtering calls.

## Select a Driver

The default Driver is configured in `config/filterable.php`:

```php
'default_driver' => 'database',

'drivers' => [
    'database' => \Kettasoft\Filterable\Drivers\DatabaseDriver::class,
],
```

Override it for one Filterable instance with `useDriver()`:

```php
$filterable = Filterable::for(Post::class, $request)
    ->using('ruleset')
    ->useDriver('database');
```

Use `getDriver()` when you need to inspect the resolved implementation:

```php
$driver = $filterable->getDriver();
```

Aliases and Driver classes are resolved through Laravel's service container, so constructor dependencies can be injected.

## Operations and Payloads

`Payload` and `Operation` have different responsibilities:

- A `Payload` preserves request lifecycle information, including the raw and sanitized values.
- An `Operation` describes the final backend-independent comparison that should be executed.
- The selected Driver translates that Operation into backend-specific query calls.

Ruleset, Expression, and Tree dispatch their resolved comparisons through the selected Driver. Applied and skipped state continues to store `Payload` snapshots, so existing diagnostics remain unchanged.

## Database Driver

The built-in database Driver translates comparisons into Eloquent constraints. It supports:

- Direct field comparisons
- Dotted and deeply nested relationship fields
- Built-in and custom operator strategies
- Logical and recursively nested Operation groups

Existing calls continue to work without explicitly selecting the Driver:

```php
$posts = Filterable::for(Post::class, $request)
    ->using('expression')
    ->setAllowedFields(['status', 'views'])
    ->get();
```

## Custom Drivers in the Current Lifecycle

A custom Driver implements the `Driver` contract and can be registered under an alias:

```php
use Kettasoft\Filterable\Drivers\Contracts\Driver;
use Kettasoft\Filterable\Drivers\DatabaseDriver;
use Kettasoft\Filterable\Operations\Contracts\Operation;

final class CustomDatabaseDriver implements Driver
{
    public function __construct(
        private DatabaseDriver $database,
    ) {}

    public function apply(Operation $operation, object $query): object
    {
        // Add application-specific behavior before or after delegation.
        return $this->database->apply($operation, $query);
    }
}
```

```php
'drivers' => [
    'database' => \Kettasoft\Filterable\Drivers\DatabaseDriver::class,
    'custom' => App\Filtering\CustomDatabaseDriver::class,
],
```

The current v3 execution lifecycle still starts from an Eloquent Builder. A Driver selected through `Filterable::useDriver()` must therefore return an Eloquent Builder. Returning an incompatible object fails explicitly instead of silently leaving the query unfiltered.

::: warning Invokable filters
Invokable filter methods can contain arbitrary Eloquent domain logic. They keep their existing behavior and are not translated into Operations yet. Portable Invokable Operations and external backend targets will be introduced separately.
:::

## Driver Failures

Invalid Driver definitions, unsupported targets, unsupported Operations, and incompatible results are infrastructure errors. Filterable always surfaces these errors, including in permissive filtering mode, because silently skipping them could return unfiltered data.
