---
sidebarDepth: 1
---

# #[Cast]

**Stage:** `TRANSFORM` (2)

Casts the payload value to a specific type using the Payload's `as*` methods.

---

## Parameters

| Parameter | Type     | Required | Description                                          |
| --------- | -------- | -------- | ---------------------------------------------------- |
| `$type`   | `string` | ✅       | The target type name (maps to `Payload::as{Type}()`) |

---

## Supported Types

| Type      | Maps To                 | Description                          |
| --------- | ----------------------- | ------------------------------------ |
| `int`     | `$payload->asInt()`     | Cast to integer                      |
| `boolean` | `$payload->asBoolean()` | Cast to boolean                      |
| `array`   | `$payload->asArray()`   | Decode JSON or return existing array |
| `carbon`  | `$payload->asCarbon()`  | Parse to Carbon date instance        |
| `slug`    | `$payload->asSlug()`    | Convert to URL-friendly slug         |
| `like`    | `$payload->asLike()`    | Wrap with `%` for LIKE queries       |

---

## Usage

### Cast to Integer

```php
use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;

#[Cast('int')]
protected function views(Payload $payload)
{
    // "42" → 42
    return $this->builder->where('views', '>=', $payload->value);
}
```

### Cast to Boolean

```php
#[Cast('boolean')]
protected function isFeatured(Payload $payload)
{
    // "true" → true, "false" → false
    return $this->builder->where('is_featured', $payload->value);
}
```

### Cast to Array (from JSON)

```php
#[Cast('array')]
protected function tags(Payload $payload)
{
    // '["php","laravel"]' → ['php', 'laravel']
    $tags = $payload->value;
    return $this->builder->whereIn('tag', $tags);
}
```

---

## Behavior

| Scenario                   | Result                          |
| -------------------------- | ------------------------------- |
| Cast type is supported     | Value is cast and returned      |
| Cast type is not supported | `StrictnessException` is thrown |
| Cast fails (invalid value) | `StrictnessException` is thrown |
