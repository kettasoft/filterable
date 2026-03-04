---
sidebarDepth: 1
---

# #[Trim]

**Stage:** `TRANSFORM` (2)

Removes whitespace (or custom characters) from the payload value before the filter method executes.

---

## Parameters

| Parameter     | Type     | Required | Default                 | Description                                    |
| ------------- | -------- | -------- | ----------------------- | ---------------------------------------------- |
| `$characters` | `string` | ❌       | `" \t\n\r\0\x0B"`      | Characters to trim                             |
| `$side`       | `string` | ❌       | `'both'`                | Side to trim: `'both'`, `'left'`, or `'right'` |

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

| Scenario                  | Result                              |
| ------------------------- | ----------------------------------- |
| Value is a string         | Whitespace/characters are trimmed   |
| Value is not a string     | No modification (silently skipped)  |
