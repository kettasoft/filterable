---
title: Exception Handling
sidebarDepth: 2
---

# Exception Handling

Filterable provides a structured and predictable exception-handling system that
allows engines to decide whether filtering should stop, skip the current filter,
or continue normally.  
This mechanism was redesigned to offer clearer behavior, improved safety, and
better extensibility.

The system is built around three main components:

-   **Exception types** (how engines signal different situations)
-   **Handlers** (how exceptions are processed)
-   **Configuration** (how strict or lenient the system should behave)

---

## Exception Flow Overview

During filtering, an engine may encounter invalid, empty, or malformed input.
Instead of halting the entire process, the engine throws a specific exception
to indicate what happened.

The handler then decides—based on the exception type and strict configuration—
whether the exception should be:

-   **thrown** (stop filtering),
-   **or skipped** (ignore this filter and continue with the next one).

If a handler returns `false`, the current filter is skipped.

---

## Exception Types

Filterable defines two fundamental exception categories.  
Each one represents a different kind of failure and implies different behavior.

### **SkipExecution**

`SkipExecution` is used when the engine cannot apply the filter, but the situation
is not considered critical.

Typical scenarios include:

-   empty values when the engine does not accept empty input,
-   unsupported operators,
-   incomplete data structures.

**Behavior:**

-   If strict mode is enabled → **the exception is thrown**
-   If strict mode is disabled → **the filter is skipped**

This allows engines to ignore irrelevant or incomplete input without failing the
whole filtering pipeline.

---

### **StrictnessException**

`StrictnessException` represents invalid or unsafe input.  
This type signals that the engine cannot proceed safely with the given data.

Examples include:

-   corrupted or malformed values,
-   invalid structure or types,
-   contradictory or logically impossible conditions.

**Behavior:**

-   strict mode enabled → **always thrown**
-   strict mode disabled → handler may return `false` to skip, but the exception
    indicates a more serious issue

This class of exceptions enforces higher input correctness.

---

## Exception Handlers

Handlers determine what happens when an exception is thrown.  
They receive both the exception and the engine instance.

Returning `false` means:  
**"Skip this filter and continue."**

Throwing the exception stops filtering immediately.

### **ExceptionHandlerInterface**

Every handler must implement:

```php
interface ExceptionHandlerInterface
{
    public function handle(\Throwable $exception, Engine $engine): bool;
}
```

This gives full control to define how exceptions are processed.

---

## Helper Base Class: FilterableExceptionHandler

`FilterableExceptionHandler` provides shared logic that custom handlers
can use to simplify implementation.

Key helper methods:

-   `isStrictThrowing()`
    Checks whether global strict mode is enabled via config.

-   `hasSkipping($exception)`
    Detects `SkipExecution`.

-   `isStrictness($exception)`
    Detects strictness-related exceptions.

Custom handlers may extend this abstract class to avoid duplicating logic.

```php
abstract class FilterableExceptionHandler implements ExceptionHandlerInterface
{
    abstract public function handle(\Throwable $exception, Engine $engine): bool;

    protected function isStrictThrowing(): bool
    {
        return config('filterable.exception.strict', false);
    }

    protected function hasSkipping($exception): bool
    {
        return $exception instanceof SkipExecution;
    }

    protected function isStrictness($exception): bool
    {
        return $exception instanceof StrictnessException;
    }
}
```

---

## DefaultHandler Behavior

The default handler implements the standard strategy for both exception types:

```php
class DefaultHandler extends FilterableExceptionHandler
{
    public function handle(\Throwable|SkipExecution $exception, Engine $engine): bool
    {
        // SkipExecution: skip if not strict
        if ($this->hasSkipping($exception)) {
            if ($engine->isStrict() || $this->isStrictThrowing()) {
                throw $exception;
            }
            return false; // skip current filter
        }

        // StrictnessException: throw when strict
        if ($this->isStrictness($exception) || $this->isStrictThrowing()) {
            throw $exception;
        }

        return false; // default: skip non-critical cases
    }
}
```

### Summary of Behavior

| Exception Type      | Strict Mode | Behavior        |
| ------------------- | ----------- | --------------- |
| SkipExecution       | Enabled     | Throw exception |
| SkipExecution       | Disabled    | Skip filter     |
| StrictnessException | Enabled     | Throw exception |
| StrictnessException | Disabled    | Skip filter     |

---

## Configuration

Exception handling is defined in the `filterable.exceptions` config section:

```php
'exceptions' => [

    'handler' => Kettasoft\Filterable\Exceptions\Handlers\DefaultHandler::class,

    'strict' => env('FILTERABLE_EXCEPTION_STRICT', false),
]
```

### `handler`

Defines the class responsible for handling exceptions.

Must implement:
`ExceptionHandlerInterface`.

### `strict`

When enabled:

-   exceptions are always thrown,
-   skipping behavior is disabled,
-   engine-level strict settings are overridden.

---

## How Filter Skipping Works

If the handler returns `false`, the current filter is skipped and the next filter
is processed.

Example:

Filters: **status**, **name**, **is_active**
Suppose:

-   `status` receives empty data,
-   engine does not accept empty values → throws `SkipExecution`.

If strict mode is disabled:

-   `status` is skipped,
-   filtering continues with `name` then `is_active`.

This allows the filtering pipeline to continue gracefully without failing
because of optional or incomplete input.

---

## Creating Custom Handlers

To implement your own rules:

```php
class MyCustomHandler extends FilterableExceptionHandler
{
    public function handle(\Throwable $exception, Engine $engine): bool
    {
        // custom logic
    }
}
```

Register it in the config:

```php
'exceptions' => [
    'handler' => App\Filters\Handlers\MyCustomHandler::class,
]
```

---

## Conclusion

This unified exception-handling pipeline provides:

-   clear distinction between skip-level and failure-level issues,
-   configurable strictness,
-   customizable handlers,
-   consistent engine behavior,
-   predictable filter skipping.

It enables robust and flexible filtering without breaking existing APIs.
