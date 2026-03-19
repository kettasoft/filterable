---
title: Creating Custom Annotations
description: Learn how to create custom PHP 8 attribute annotations for the Filterable Invokable Engine. Implement the MethodAttribute interface, choose a pipeline stage, and use AttributeContext to read and modify the filter payload.
tags: [annotations, custom-annotation, invokable-engine, extending]
---

Custom annotations let you extend the Invokable Engine's attribute pipeline with
your own reusable control, transform, validate, or behavior logic — declared
directly on filter methods just like built-in annotations.

---

## The `MethodAttribute` Interface

Every annotation must implement `MethodAttribute`:

```php
namespace Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;

interface MethodAttribute
{
    public static function stage(): int;
    public function handle(AttributeContext $context): void;
}
```

| Method     | Description                                                      |
| ---------- | ---------------------------------------------------------------- |
| `stage()`  | Returns the pipeline stage. Controls execution order.            |
| `handle()` | Receives `AttributeContext` and performs the annotation's logic. |

---

## Choosing a Stage

| Stage       | Value | When to use                                  |
| ----------- | ----- | -------------------------------------------- |
| `CONTROL`   | `1`   | Skip or gate the filter before anything runs |
| `TRANSFORM` | `2`   | Modify or normalize the payload value        |
| `VALIDATE`  | `3`   | Assert the value meets a constraint          |
| `BEHAVIOR`  | `4`   | Affect the query builder directly            |

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage;

public static function stage(): int
{
    return Stage::VALIDATE->value; // 3
}
```

---

## The `AttributeContext` Object

`handle()` receives an `AttributeContext` with:

| Property  | Type      | Description                                               |
| --------- | --------- | --------------------------------------------------------- |
| `query`   | `mixed`   | The Eloquent query builder                                |
| `payload` | `Payload` | The filter payload (field, operator, value, rawValue)     |
| `state`   | `array`   | Shared state between annotations in the same pipeline run |

You can read and write to `state` for inter-annotation communication:

```php
$context->set('my_flag', true);
$context->get('my_flag'); // true
$context->has('my_flag'); // true
```

---

## Skipping vs Throwing

Two outcomes are available when an annotation's condition fails:

| Exception             | Effect                                          |
| --------------------- | ----------------------------------------------- |
| `SkipExecution`       | Filter method is silently skipped               |
| `StrictnessException` | Error propagates — use for required constraints |

```php
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Engines\Exceptions\StrictnessException;

// Silent skip
throw new SkipExecution('Value too short.');

// Hard fail
throw new StrictnessException('This field is required.');
```

---

## Example: `#[MinLength]` (Validate Stage)

Skips the filter if the string value is shorter than a minimum length:

```php
<?php

namespace App\Filters\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

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
        $value = $context->payload->value;

        if (is_string($value) && mb_strlen($value) < $this->length) {
            throw new SkipExecution(
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

## Example: `#[Uppercase]` (Transform Stage)

Converts the payload value to uppercase before the filter runs:

```php
<?php

namespace App\Filters\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage;

#[Attribute(Attribute::TARGET_METHOD)]
class Uppercase implements MethodAttribute
{
    public static function stage(): int
    {
        return Stage::TRANSFORM->value;
    }

    public function handle(AttributeContext $context): void
    {
        if (is_string($context->payload->value)) {
            $context->payload->value = strtoupper($context->payload->value);
        }
    }
}
```

---

## Example: `#[OnlyWhen]` (Control Stage)

Skips the filter unless the authenticated user has a specific role:

```php
<?php

namespace App\Filters\Annotations;

use Attribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Contracts\MethodAttribute;
use Kettasoft\Filterable\Engines\Foundation\Attributes\AttributeContext;
use Kettasoft\Filterable\Engines\Foundation\Attributes\Enums\Stage;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OnlyWhen implements MethodAttribute
{
    public function __construct(public string $role) {}

    public static function stage(): int
    {
        return Stage::CONTROL->value;
    }

    public function handle(AttributeContext $context): void
    {
        if (! auth()->user()?->hasRole($this->role)) {
            throw new SkipExecution("User does not have role: {$this->role}");
        }
    }
}
```

Usage:

```php
#[OnlyWhen('admin')]
#[OnlyWhen('manager')]
protected function salary(Payload $payload)
{
    return $this->builder->where('salary', '>=', $payload->value);
}
```

---

## Tips

- Use `Stage::VALIDATE` for constraints that should run after the value is already cleaned.
- Use `SkipExecution` for optional filters, `StrictnessException` for required ones.
- Add `Attribute::IS_REPEATABLE` to `#[Attribute(...)]` if the annotation should be stackable on the same method.
- Store computed values in `$context->state` if a downstream annotation (in the same pipeline run) needs them.
