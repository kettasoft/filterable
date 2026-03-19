---
title: "#[Sanitize]"
description: Applies one or more sanitization rules to a filter payload using the #[Sanitize] attribute. Use it to normalize input with operations like lowercase, strip_tags, trim, and slug before validation or query execution.
tags: [annotations, transform, sanitization]
sidebarDepth: 1
---

::: info Stage
`TRANSFORM` — runs after control attributes, modifies the [payload](/api/payload) value before validation and execution.
:::

Applies one or more sanitization rules to the [payload](/api/payload) value in order. This is the most versatile transform attribute, supporting multiple chained operations.

---

## Parameters

| Parameter   | Type     | Required | Description                                  |
| ----------- | -------- | -------- | -------------------------------------------- |
| `...$rules` | `string` | ✅       | One or more sanitization rule names to apply |

---

## Supported Rules

| Rule         | Description                       | Example                           |
| ------------ | --------------------------------- | --------------------------------- |
| `lowercase`  | Convert to lowercase              | `"ACTIVE"` → `"active"`           |
| `uppercase`  | Convert to uppercase              | `"active"` → `"ACTIVE"`           |
| `ucfirst`    | Capitalize first letter           | `"hello world"` → `"Hello world"` |
| `strip_tags` | Remove HTML and PHP tags          | `"<b>hello</b>"` → `"hello"`      |
| `nl2br`      | Convert newlines to `<br>` tags   | `"a\nb"` → `"a<br>\nb"`           |
| `slug`       | Convert to URL-friendly slug      | `"Hello World"` → `"hello-world"` |
| `trim`       | Remove whitespace from both sides | `"  hello  "` → `"hello"`         |

---

## Usage

### Single Rule

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Sanitize;

#[Sanitize('lowercase')]
protected function status(Payload $payload)
{
    // "ACTIVE" → "active"
    return $this->builder->where('status', $payload->value);
}
```

### Multiple Rules (Applied in Order)

```php
#[Sanitize('trim', 'strip_tags', 'lowercase')]
protected function status(Payload $payload)
{
    // "  <b>ACTIVE</b>  " → "active"
    return $this->builder->where('status', $payload->value);
}
```

### Generate Slug

```php
#[Sanitize('slug')]
protected function category(Payload $payload)
{
    // "Hello World Post" → "hello-world-post"
    return $this->builder->where('slug', $payload->value);
}
```

---

## Rule Order Matters

Rules are applied **left to right**, so the order can affect the result:

```php
// ✅ Correct: strip tags first, then lowercase
#[Sanitize('strip_tags', 'lowercase')]
// "<B>HELLO</B>" → "HELLO" → "hello"

// ⚠️ Different result: lowercase first, then strip tags
#[Sanitize('lowercase', 'strip_tags')]
// "<B>HELLO</B>" → "<b>hello</b>" → "hello"
```

---

## Behavior

| Scenario              | Result                               |
| --------------------- | ------------------------------------ |
| Value is a string     | All rules are applied in order       |
| Value is not a string | No modification (silently skipped)   |
| Unknown rule name     | `InvalidArgumentException` is thrown |
