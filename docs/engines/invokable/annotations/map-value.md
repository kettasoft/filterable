---
sidebarDepth: 1
---

# #[MapValue]

**Stage:** `TRANSFORM` (2)

Maps incoming values to different output values using a key-value map. Useful for converting human-readable values (like `'active'`, `'inactive'`) to database values (like `1`, `0`).

---

## Parameters

| Parameter | Type    | Required | Default | Description                                              |
| --------- | ------- | -------- | ------- | -------------------------------------------------------- |
| `$map`    | `array` | ✅       | —       | Key-value mapping (e.g., `['active' => 1]`)              |
| `$strict` | `bool`  | ❌       | `false` | If `true`, skip the filter when value is not in the map  |

---

## Usage

### Basic Mapping

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\MapValue;

#[MapValue(['active' => 1, 'inactive' => 0])]
protected function status(Payload $payload)
{
    // "active" → 1, "inactive" → 0
    return $this->builder->where('status', $payload->value);
}
```

### String to String Mapping

```php
#[MapValue(['published' => 'live', 'draft' => 'hidden'])]
protected function visibility(Payload $payload)
{
    // "published" → "live", "draft" → "hidden"
    return $this->builder->where('visibility', $payload->value);
}
```

### Strict Mode

When `strict: true`, if the incoming value is not found in the map, the filter is skipped entirely:

```php
#[MapValue(['active' => 1, 'inactive' => 0], strict: true)]
protected function status(Payload $payload)
{
    // "unknown" → filter is SKIPPED
    return $this->builder->where('status', $payload->value);
}
```

---

## Behavior

| Scenario                           | Non-Strict (default)                  | Strict Mode                   |
| ---------------------------------- | ------------------------------------- | ----------------------------- |
| Value exists in map                | Value is replaced with mapped value   | Value is replaced             |
| Value does **not** exist in map    | Original value is kept                | Filter is **skipped**         |
