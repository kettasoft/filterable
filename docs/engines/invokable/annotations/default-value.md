---
sidebarDepth: 1
---

# #[DefaultValue]

**Stage:** `TRANSFORM` (2)

Sets a fallback value when the payload value is empty or null. The filter method still executes, but with the default value instead of the empty input.

---

## Parameters

| Parameter | Type    | Required | Description                          |
| --------- | ------- | -------- | ------------------------------------ |
| `$value`  | `mixed` | ✅       | The default value to use as fallback |

---

## Usage

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\DefaultValue;

#[DefaultValue('active')]
protected function status(Payload $payload)
{
    // If status is empty → uses "active"
    return $this->builder->where('status', $payload->value);
}
```

### With Numeric Default

```php
#[DefaultValue(10)]
protected function perPage(Payload $payload)
{
    // If perPage is empty → uses 10
    return $this->builder->limit($payload->value);
}
```

---

## Behavior

| Scenario                       | Result                                    |
| ------------------------------ | ----------------------------------------- |
| Value is empty or null         | Payload value is set to the default       |
| Value is provided (non-empty)  | Default is **not** applied, original kept |

---

## Combining with Other Attributes

```php
#[DefaultValue('pending')]
#[In('active', 'pending', 'archived')]
protected function status(Payload $payload)
{
    // Empty input → "pending" → passes In validation
    return $this->builder->where('status', $payload->value);
}
```
