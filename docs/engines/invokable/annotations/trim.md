---
title: "#[Trim]"
description: Strips whitespace or custom characters from a filter payload using the #[Trim] attribute. Use it in the transform stage to clean string input before validation. Supports trimming both sides, left only, or right only.
tags: [annotations, transform, whitespace]
sidebarDepth: 1
---

::: info Stage
`TRANSFORM` — runs after control attributes, modifies the [payload](/api/payload) value before validation and execution.
:::

Removes whitespace (or custom characters) from the [payload](/api/payload) value before the filter method executes.

---

## Parameters

| Parameter     | Type     | Required | Default          | Description                                    |
| ------------- | -------- | -------- | ---------------- | ---------------------------------------------- |
| `$characters` | `string` | ❌       | `"\t\n\r\0\x0B"` | Characters to trim                             |
| `$side`       | `string` | ❌       | `'both'`         | Side to trim: `'both'`, `'left'`, or `'right'` |

---

## Usage

### Basic — Trim Both Sides

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim;

#[Trim]
protected function title(Payload $payload)
{
    // "  hello world  " → "hello world"
    return $this->builder->where('title', $payload->value);
}
```

### Trim Left Only

```php
#[Trim(side: 'left')]
protected function title(Payload $payload)
{
    // "  hello world  " → "hello world  "
    return $this->builder->where('title', $payload->value);
}
```

### Trim Right Only

```php
#[Trim(side: 'right')]
protected function title(Payload $payload)
{
    // "  hello world  " → "  hello world"
    return $this->builder->where('title', $payload->value);
}
```

### Custom Characters

```php
#[Trim(characters: '-')]
protected function slug(Payload $payload)
{
    // "---hello-world---" → "hello-world"
    return $this->builder->where('slug', $payload->value);
}
```

---

## Behavior

| Scenario              | Result                             |
| --------------------- | ---------------------------------- |
| Value is a string     | Whitespace/characters are trimmed  |
| Value is not a string | No modification (silently skipped) |
