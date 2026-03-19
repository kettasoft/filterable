---
title: "#[Regex]"
description: Validates a filter payload against a regular expression using the #[Regex] attribute. Use it to enforce format constraints like slugs, emails, or product codes. Non-matching values cause the filter to be skipped.
tags: [annotations, validation, regex]
sidebarDepth: 1
---

::: info Stage
`VALIDATE` — runs after transform attributes, before the filter method executes.
:::

Validates that the [payload](/api/payload) value matches a given regular expression pattern. If it doesn't match, the filter is skipped.

---

## Parameters

| Parameter  | Type     | Required | Default | Description                                |
| ---------- | -------- | -------- | ------- | ------------------------------------------ |
| `$pattern` | `string` | ✅       | —       | The regex pattern to match against         |
| `$message` | `string` | ❌       | `''`    | Custom error message when validation fails |

---

## Usage

### Alphabetic Only

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Regex;

#[Regex('/^[a-zA-Z]+$/')]
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```

### Email Validation

```php
#[Regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')]
protected function email(Payload $payload)
{
    return $this->builder->where('email', $payload->value);
}
```

### Slug Pattern

```php
#[Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
protected function slug(Payload $payload)
{
    return $this->builder->where('slug', $payload->value);
}
```

### Numeric Only

```php
#[Regex('/^\d+$/')]
protected function zipCode(Payload $payload)
{
    return $this->builder->where('zip_code', $payload->value);
}
```

### Custom Error Message

```php
#[Regex('/^[A-Z]{2}-\d{4}$/', message: 'Invalid product code format. Expected: XX-1234')]
protected function productCode(Payload $payload)
{
    return $this->builder->where('code', $payload->value);
}
```

---

## Behavior

| Scenario                  | Result                   |
| ------------------------- | ------------------------ |
| Value matches the pattern | Filter executes normally |
| Value does not match      | Filter is skipped        |
| Value is not a string     | Filter is skipped        |

---

## Combining with Transform Attributes

```php
#[Trim]
#[Sanitize('lowercase')]
#[Regex('/^[a-z0-9-]+$/')]
protected function slug(Payload $payload)
{
    // "  My-Slug-123  " → "my-slug-123" → passes regex
    return $this->builder->where('slug', $payload->value);
}
```
