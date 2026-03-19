---
title: Annotations
description: Reference for all PHP 8 attribute annotations in the Invokable Engine. Covers control, transform, validate, and behavior stages with execution order and usage examples.
tags: [invokable-engine, php-attributes, annotations, filter-pipeline]
sidebarDepth: 2
---

# Annotations (PHP Attributes)

The **Invokable Engine** supports PHP 8 Attributes as a powerful declarative way to control filter behavior. Instead of writing validation, transformation, and authorization logic inside your filter methods, you declare it with attributes directly on the method signature.

---

## How Annotations Work

When the Invokable Engine processes a filter method, it runs all declared attributes through an **Attribute Pipeline** before executing the method itself. If any attribute throws a `SkipExecution` exception, the filter method is skipped entirely. If an attribute throws a `StrictnessException`, the error propagates up.

```php
#[Trim]
#[Sanitize('lowercase')]
#[Required]
#[In('active', 'pending', 'archived')]
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```

---

## Execution Stages

Attributes are **sorted by stage** before execution, regardless of the order you declare them. This ensures a predictable pipeline:

| Order | Stage         | Value | Purpose                        | Description                            |
| ----- | ------------- | ----- | ------------------------------ | -------------------------------------- |
| 1     | **CONTROL**   | `1`   | Gate / Skip                    | Decide whether the filter should run   |
| 2     | **TRANSFORM** | `2`   | Modify [Payload](/api/payload) | Clean, convert, or map the input value |
| 3     | **VALIDATE**  | `3`   | Assert Correctness             | Verify the value meets constraints     |
| 4     | **BEHAVIOR**  | `4`   | Affect Query                   | Modify query behavior directly         |

### Pipeline Flow

```text
Incoming Payload
    │
    ▼
┌─────────────────┐
│  CONTROL (1)    │  → #[Authorize], #[SkipIf]
│  Should we run? │  → Throws SkipExecution to abort
└────────┬────────┘
         │ ✓ Pass
         ▼
┌─────────────────┐
│  TRANSFORM (2)  │  → #[Trim], #[Sanitize], #[Cast], #[MapValue], #[DefaultValue], #[Explode]
│  Clean the data │  → Modifies payload.value in place
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  VALIDATE (3)   │  → #[Required], #[In], #[Between], #[Regex]
│  Is data valid? │  → Throws SkipExecution or StrictnessException
└────────┬────────┘
         │ ✓ Pass
         ▼
┌─────────────────┐
│  BEHAVIOR (4)   │  → #[Scope]
│  Affect query   │  → May apply scopes or modify builder
└────────┬────────┘
         │
         ▼
   Filter Method Executes
```

---

## Available Annotations

### Control Stage

| Attribute                        | Description                                                  |
| -------------------------------- | ------------------------------------------------------------ |
| [`#[Authorize]`](./authorize.md) | Require authorization before running the filter              |
| [`#[SkipIf]`](./skip-if.md)      | Skip the filter based on a [Payload](/api/payload) condition |

### Transform Stage

| Attribute                               | Description                                            |
| --------------------------------------- | ------------------------------------------------------ |
| [`#[Trim]`](./trim.md)                  | Remove whitespace from string values                   |
| [`#[Sanitize]`](./sanitize.md)          | Apply sanitization rules (lowercase, strip_tags, etc.) |
| [`#[Cast]`](./cast.md)                  | Cast the value to a specific type                      |
| [`#[MapValue]`](./map-value.md)         | Map input values to different values                   |
| [`#[DefaultValue]`](./default-value.md) | Set a fallback value when input is empty               |
| [`#[Explode]`](./explode.md)            | Split a string value into an array                     |

### Validate Stage

| Attribute                      | Description                                  |
| ------------------------------ | -------------------------------------------- |
| [`#[Required]`](./required.md) | Ensure the value is present and not empty    |
| [`#[In]`](./in.md)             | Validate the value is in an allowed set      |
| [`#[Between]`](./between.md)   | Validate the value is within a numeric range |
| [`#[Regex]`](./regex.md)       | Validate the value matches a regex pattern   |

### Behavior Stage

| Attribute                | Description                                                         |
| ------------------------ | ------------------------------------------------------------------- |
| [`#[Scope]`](./scope.md) | Auto-apply an Eloquent scope with the [payload](/api/payload) value |

---

## Combining Attributes

You can stack multiple attributes on a single method. They always execute in stage order:

```php
#[SkipIf('empty')]                              // Stage 1: Skip if empty
#[Trim]                                          // Stage 2: Remove whitespace
#[Sanitize('lowercase', 'strip_tags')]           // Stage 2: Clean the value
#[Cast('int')]                                   // Stage 2: Cast to integer
#[Required]                                      // Stage 3: Must have a value
#[Between(min: 1, max: 1000)]                    // Stage 3: Range check
protected function price(Payload $payload)
{
    return $this->builder->where('price', $payload->value);
}
```

---

## Creating Custom Annotations

All annotations implement the `MethodAttribute` interface:

```php
<?php

namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;

interface MethodAttribute
{
    public static function stage(): int;
    public function handle(AttributeContext $context): void;
}
```

### Example: Custom Annotation

```php
<?php

namespace App\Filters\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage;

#[Attribute(Attribute::TARGET_METHOD)]
class MinLength implements MethodAttribute
{
    public function __construct(public int $length) {}

    public static function stage(): int
    {
        return Stage::VALIDATE->value;
    }

    public function handle(AttributeContext $context): void
    {
        $payload = $context->payload;

        if (is_string($payload->value) && mb_strlen($payload->value) < $this->length) {
            throw new \Kettasoft\Filterable\Engines\Exceptions\SkipExecution(
                "Value must be at least {$this->length} characters."
            );
        }
    }
}
```

Usage:

```php
#[MinLength(3)]
protected function search(Payload $payload)
{
    return $this->builder->where('title', 'like', $payload->asLike());
}
```

---

## AttributeContext

The `AttributeContext` object passed to each annotation's `handle()` method contains:

| Property  | Type    | Description                                       |
| --------- | ------- | ------------------------------------------------- |
| `query`   | `mixed` | The Eloquent query builder instance               |
| `payload` | `mixed` | The `Payload` object with the filter value        |
| `state`   | `array` | Shared state array (`method`, `key`, custom data) |

You can read and write to `state` for inter-attribute communication:

```php
$context->set('my_flag', true);
$context->get('my_flag'); // true
$context->has('my_flag'); // true
```
