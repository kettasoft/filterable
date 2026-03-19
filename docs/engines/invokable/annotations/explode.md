---
title: "#[Explode]"
description: Splits a string filter payload into an array using the #[Explode] attribute. Use it before whereIn queries when input arrives as a delimited string like comma-separated values.
tags: [annotations, transform, array]
sidebarDepth: 1
---

::: info Stage
`TRANSFORM` — runs after control attributes, modifies the [payload](/api/payload) value before validation and execution.
:::

Splits a string value into an array using a specified delimiter. The [payload](/api/payload) value is overwritten with the resulting array, making it ready for `whereIn` and similar queries.

---

## Parameters

| Parameter    | Type     | Required | Default | Description               |
| ------------ | -------- | -------- | ------- | ------------------------- |
| `$delimiter` | `string` | ❌       | `','`   | The delimiter to split by |

---

## Usage

### Default Delimiter (Comma)

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Explode;

#[Explode]
protected function tags(Payload $payload)
{
    // "php,laravel,testing" → ["php", "laravel", "testing"]
    return $this->builder->whereIn('tag', $payload->value);
}
```

### Custom Delimiter

```php
#[Explode('|')]
protected function categories(Payload $payload)
{
    // "news|sports|tech" → ["news", "sports", "tech"]
    return $this->builder->whereIn('category', $payload->value);
}
```

---

## Behavior

| Scenario                  | Result                                         |
| ------------------------- | ---------------------------------------------- |
| Value is a string         | Split into array, payload value is overwritten |
| Value is already an array | Returned as-is                                 |

---

## Combining with Other Attributes

```php
#[Trim]
#[Explode(',')]
protected function statuses(Payload $payload)
{
    // "  active,pending,archived  " → ["active", "pending", "archived"]
    return $this->builder->whereIn('status', $payload->value);
}
```
