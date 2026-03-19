---
title: "#[In]"
description: Validates that a filter payload is one of a predefined set of allowed values using the #[In] attribute. Use it to whitelist accepted inputs. Values outside the set cause the filter to be silently skipped.
tags: [annotations, validation, whitelist]
sidebarDepth: 1
---

::: info Stage
`VALIDATE` — runs after transform attributes, before the filter method executes.
:::

Validates that the [payload](/api/payload) value is within a predefined set of allowed values. If the value is not in the set, the filter is skipped silently.

---

## Parameters

| Parameter    | Type    | Required | Description                   |
| ------------ | ------- | -------- | ----------------------------- |
| `...$values` | `mixed` | ✅       | The allowed values (variadic) |

---

## Usage

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\In;

#[In('active', 'pending', 'archived')]
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```

---

## Behavior

| Scenario                    | Result                                     |
| --------------------------- | ------------------------------------------ |
| Value is in the allowed set | Filter executes normally                   |
| Value is not in the set     | Filter is skipped (`SkipExecution` thrown) |

---

## Examples

### Restrict to Specific Types

```php
#[In('post', 'page', 'article')]
protected function type(Payload $payload)
{
    return $this->builder->where('type', $payload->value);
}
```

### Numeric Values

```php
#[In(1, 2, 3, 4, 5)]
protected function rating(Payload $payload)
{
    return $this->builder->where('rating', $payload->value);
}
```

---

## Combining with Transform Attributes

```php
#[Trim]
#[Sanitize('lowercase')]
#[In('active', 'pending', 'archived')]
protected function status(Payload $payload)
{
    // "  ACTIVE  " → "active" → passes In check
    return $this->builder->where('status', $payload->value);
}
```
