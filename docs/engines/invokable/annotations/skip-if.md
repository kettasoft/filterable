---
title: "#[SkipIf]"
description: Skips a filter method when a Payload condition is met using the #[SkipIf] attribute. Use it in the control stage to guard against empty, null, or non-numeric input. Supports negation and stacking multiple checks.
tags: [annotations, control, conditional-skip]
sidebarDepth: 1
---

::: info Stage
`CONTROL` — runs first in the pipeline, before any transform or validation attributes. This attribute is **repeatable** and can be stacked multiple times on the same method.
:::

Skips the filter execution if a specified condition on the [Payload](/api/payload) is met. Uses the Payload's `is*` methods for checks.

---

## Parameters

| Parameter  | Type     | Required | Default | Description                                                                            |
| ---------- | -------- | -------- | ------- | -------------------------------------------------------------------------------------- |
| `$check`   | `string` | ✅       | —       | The [Payload](/api/payload) `is*` check name (e.g., `'empty'`, `'null'`, `'!numeric'`) |
| `$message` | `string` | ❌       | `''`    | Custom message when the filter is skipped                                              |

---

## Usage

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\SkipIf;

#[SkipIf('empty')]
protected function status(Payload $payload)
{
    return $this->builder->where('status', $payload->value);
}
```

---

## Negation with `!`

Prefix the check name with `!` to negate it:

```php
// Skip if value is NOT numeric
#[SkipIf('!numeric')]
protected function price(Payload $payload)
{
    return $this->builder->where('price', $payload->value);
}
```

---

## Available Checks

Any `is*` method on the `Payload` class can be used:

| Check           | Maps To                     | Description              |
| --------------- | --------------------------- | ------------------------ |
| `'empty'`       | `$payload->isEmpty()`       | Value is empty           |
| `'null'`        | `$payload->isNull()`        | Value is null            |
| `'emptyString'` | `$payload->isEmptyString()` | Value is a blank string  |
| `'numeric'`     | `$payload->isNumeric()`     | Value is numeric         |
| `'boolean'`     | `$payload->isBoolean()`     | Value is boolean-like    |
| `'string'`      | `$payload->isString()`      | Value is a string        |
| `'array'`       | `$payload->isArray()`       | Value is an array        |
| `'date'`        | `$payload->isDate()`        | Value is a valid date    |
| `'json'`        | `$payload->isJson()`        | Value is valid JSON      |
| `'!numeric'`    | `!$payload->isNumeric()`    | Value is **not** numeric |
| `'!empty'`      | `!$payload->isEmpty()`      | Value is **not** empty   |

---

## Stacking Multiple Checks

Since `#[SkipIf]` is repeatable, you can stack multiple conditions:

```php
#[SkipIf('empty')]
#[SkipIf('emptyString')]
protected function title(Payload $payload)
{
    return $this->builder->where('title', 'like', $payload->asLike());
}
```

Each `#[SkipIf]` is evaluated independently. If **any** of them triggers, the filter is skipped.

---

## Behavior

| Scenario                 | Result                               |
| ------------------------ | ------------------------------------ |
| Check returns `true`     | Filter is skipped                    |
| Check returns `false`    | Filter executes normally             |
| Negated check (`!`) true | Filter is skipped                    |
| Method doesn't exist     | `InvalidArgumentException` is thrown |
