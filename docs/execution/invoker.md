# Invoker – Fluent Control Over Query Execution

## Overview

The `Invoker` class in the Filterable package is a smart execution wrapper that allows you to control the lifecycle of your query after applying filters. It is returned by the `Filterable::apply()` method and supports actions **before**, **after**, or **on error** during query execution.

---

## Purpose

`Invoker` wraps the underlying query builder and enables:

- Executing callbacks **before** the query runs.
- Handling results **after** the query runs.
- Capturing **errors** and providing fallback logic.
- Dispatching the query as a Laravel job.
- Fluent chaining with `when` and `unless` conditions.

---

## Usage Example

```php
$result = Filterable::apply(User::query())
    ->beforeExecute(function ($builder) {
      $builder->where('is_active', true);
    })
    ->afterExecute(function (Collection $result) {
        return $result->filter(fn ($user) => $user->isActive());
    })
    ->onError(function ($invoker, $exception) {
        report($exception);
        return collect(); // fallback
    })
    ->get(); // <-- This will trigger the execution
```

## Public Methods

`Invoker::init(QueryBuilderInterface $builder)`

Create a new Invoker instance manually.

---

### beforeExecute

`->beforeExecute(Closure $callback): static`
Register a callback to be called before the query is executed.

**Parameters**:

- `Closure $callback`: Receives the internal query builder.

---

### afterExecute

`->afterExecute(Closure $callback): static`
Register a callback to process or modify the result after execution.

**Parameters**:

- `Closure $callback`: Receives the result returned by the terminal method.

---

### onError

`->onError(Closure $callback): static`
Register a callback to handle any exceptions that occur during query execution.

**Parameters**:
`Closure $callback`: Receives the Invoker and the thrown exception.

---

### when

`->when(bool $condition, callable $callback): static`
Conditionally apply logic to the Invoker chain.

---

### unless

`->unless(bool $condition, callable $callback): static`
The inverse of when.

---

### asJob

`->asJob(string $jobClass, array $data = [], ?string $queue = null): mixed`
Dispatch the query execution as a Laravel job.

**Parameters**:

- `string $jobClass`: The name of the job class to dispatch.
- `array $data`: Optional additional data.
- `string|null $queue`: Optional queue name.

---

### When Invoker is Skipped

In some advanced use cases, the `Invoker` wrapper will be **skipped**, and the `apply()` method will return the query builder directly.

This happens in two cases:

1. If the target class implements the `ShouldReturnQueryBuilder` interface:

```php
<?php

namespace App\Http\Filters;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Foundation\Contracts\ShouldReturnQueryBuilder;

class PostFilter extends Filterable implements ShouldReturnQueryBuilder
{
//
}
```

2. If you explicitly call the `shouldReturnQueryBuilder()` method before calling a terminal method.

```php
$filter = Filterable::create()
    ->shouldReturnQueryBuilder()
    ->apply(Post::query()) // <- Returns Query builder directly
```

This is useful when you want to bypass Invoker's control layer and interact with the builder as usual.

---

Notes:
The job class must accept an `invoker` key in its constructor data.

## Summary

`Invoker` is your **last-mile control layer** before the query is executed. It's ideal for:

- Logging
- Result transformation
- Fallbacks
- Background execution

This gives Filterable a clean and powerful **declarative style**.
