---
sidebarDepth: 1
---

# #[Required]

**Stage:** `VALIDATE` (3)

Ensures the payload value is present and not empty. If the value is missing or empty, a `StrictnessException` is thrown, which **propagates up** rather than silently skipping.

---

## Parameters

This attribute has no constructor parameters. The error message includes the parameter name automatically.

---

## Usage

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Required;

#[Required]
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```

---

## Error Message

When the value is empty, the exception message follows this format:

```
The parameter 'status' is required.
```

The parameter name (`status`) is taken from the filter key in the request.

---

## Behavior

| Scenario                   | Result                                              |
| -------------------------- | --------------------------------------------------- |
| Value is provided          | Filter executes normally                            |
| Value is empty (`''`)      | `StrictnessException` is thrown                     |
| Value is null              | `StrictnessException` is thrown                     |

::: warning
Unlike other validation attributes (like `#[In]` or `#[Between]`) which **skip** the filter silently, `#[Required]` throws a `StrictnessException` that propagates to the caller.
:::

---

## Combining with Other Attributes

```php
#[Trim]                    // First: trim whitespace
#[Required]                // Then: ensure it's not empty after trimming
#[In('active', 'pending')] // Finally: validate allowed values
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```
