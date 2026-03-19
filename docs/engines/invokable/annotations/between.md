---
title: "#[Between]"
description: Validates that a filter payload falls within a numeric range using the #[Between] attribute. Use it on numeric filter methods. Values outside the range or non-numeric values cause the filter to be skipped.
tags: [annotations, validation, numeric-range]
---

::: info Stage
`VALIDATE` — runs after transform attributes, before the filter method executes.
:::

Validates that the [payload](/api/payload) value falls within a specified numeric range. If the value is outside the range or not numeric, the filter is skipped.

---

## Parameters

| Parameter | Type         | Required | Description           |
| --------- | ------------ | -------- | --------------------- |
| `$min`    | `float\|int` | ✅       | Minimum allowed value |
| `$max`    | `float\|int` | ✅       | Maximum allowed value |

---

## Usage

### Integer Range

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Between;

#[Between(min: 1, max: 100)]
protected function views(Payload $payload)
{
    return $this->builder->where('views', '>=', $payload->value);
}
```

### Float Range

```php
#[Between(min: 0.0, max: 5.0)]
protected function rating(Payload $payload)
{
    return $this->builder->where('rating', '>=', $payload->value);
}
```

---

## Behavior

| Scenario                          | Result                               |
| --------------------------------- | ------------------------------------ |
| Value is numeric and within range | Filter executes normally             |
| Value is at the minimum boundary  | Filter executes normally (inclusive) |
| Value is at the maximum boundary  | Filter executes normally (inclusive) |
| Value is below the range          | Filter is skipped                    |
| Value is above the range          | Filter is skipped                    |
| Value is not numeric              | Filter is skipped                    |

---

## Boundary Behavior

The check is **inclusive** on both ends:

```php
#[Between(min: 1, max: 100)]
// 1   → ✅ passes
// 50  → ✅ passes
// 100 → ✅ passes
// 0   → ❌ skipped
// 101 → ❌ skipped
```

---

## Combining with Other Attributes

```php
#[SkipIf('empty')]
#[Trim]
#[Between(min: 1, max: 1000)]
protected function price(Payload $payload)
{
    return $this->builder->where('price', '>=', $payload->value);
}
```
