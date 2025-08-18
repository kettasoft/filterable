# Payload

The **Payload** class is a lightweight data wrapper used to represent a single filter input.  
It normalizes values, provides utility methods, and makes it easier to work with common patterns such as wildcard search, JSON detection, boolean casting, and more.

---

## Overview

When you define filters inside a `Filterable` class, the filter method receives a `Payload` object instead of a raw value.

```php
class PostFilter extends Filterable
{
    protected $filters = ['title'];

    protected function title(Payload $payload)
    {
        return $this->builder->where('title', 'like', $payload->like());
    }
}
```

---

## Properties

| Property          | Type     | Description                            |
| ----------------- | -------- | -------------------------------------- |
| `$field`          | `string` | The field passed from the request.     |
| `$operator`       | `string` | The operator passed from the request.  |
| `$value`          | `mixed`  | The raw value passed from the request. |
| `$beforeSanitize` | `mixed`  | The original value before sanitizing.  |

---

## Public Methods

### `__toString(): string`

Returns the value as string.

```php
(string) $payload; // equivalent to $payload->value
```

---

### `length(): int`

Get the length of the value.  
Dealing with `array` or `string`

```php
if ($payload->length() > 10) {
    // skip filter
}
```

---

### `isEmpty(): bool`

Check if the value is empty (`null`, `""`, or whitespace).

```php
if ($payload->isEmpty()) {
    // skip filter
}
```

---

### `isNotEmpty(): bool`

Check if the value is not empty (`filterable`, `['one', 'tow']`, or any data).

```php
if ($payload->isNotEmpty()) {
    // appliy filter
}
```

---

### `isNull(): bool`

Check if the value is null.

```php
if ($payload->isNull()) {
    // skip filter
}
```

---

### `isJson(): bool`

Check if the payload is a valid JSON string.

```php
if ($payload->isJson()) {
    $data = json_decode($payload->value, true);
}
```

---

### `asBoolean(): bool|null`

Convert value to boolean.  
Supports `"true"`, `"false"`, `"1"`, `"0"`, `"yes"`, `"no"`.

```php
$payload->asBoolean(); // true or false
```

---

### `asLike(string $side = "both"): string`

Wrap the value with `%` for SQL `LIKE` queries.

-   `both` → `%value%`
-   `left` → `%value`
-   `right` → `value%`

```php
$this->builder->where('title', 'like', $payload->asLike());
// WHERE title LIKE "%keyword%"
```

---

### `asInt(): int`

Cast value to integer.

```php
$payload->asInt(); // 42
```

---

### `raw(): mixed`

Get the original unmodified value.

```php
$payload->raw();  // equivalent to $payload->beforeSanitize
```

---

### `isNumeric(): bool`

Check if the value is numeric.

```php
if ($payload->isNumeric()) {
    return $this->builder->where('id', $this->value);
}
```

---

### `isString(): bool`

Check if the value is string.

```php
if ($payload->isString()) {
    return $this->builder->where('name', $this->value);
}
```

---

### `isArray(): bool`

Check if the value is array.

```php
if ($payload->isArray()) {
    return $this->builder->where('name', 'in', $this->value);
}
```

---

### `isTrue(): bool`

Check if the value is `true`.  
Supports `"true"`, `"1"`, `"yes"`.

```php
$payload->isTrue(); // true
```

---

### `isFalse(): bool`

Check if the value is `false`.  
Supports `"false"`, `"0"`, `"no"`, `""`.

```php
$payload->isFalse();
```

---

### `asArray(): array`

If the value is a valid JSON string representing an array/object, it will be decoded into an array.
If the value is already an array, it will be returned directly. Otherwise returns null.

```php
$payload->asArray();
```

---

### `toArray(): array`

Get the instance as an array

```php
$payload->toArray();

/*
  [
    "field" => "status",
    "operator" => "=",
    "value" => "filterable"
  ]
*/
```

---

### `toJson(): string`

Get the instance as an json string

```php
$payload->toJson();

/*
  [
    "field" => "status",
    "operator" => "=",
    "value" => "filterable"
  ]
*/
```

## Example Usage

```php
protected function status(Payload $payload)
{
    return $this->builder->where('is_active', $payload->asBoolean());
}

protected function category(Payload $payload)
{
    return $this->builder->where('category_id', $payload->asInt());
}

protected function meta(Payload $payload)
{
    if ($payload->isJson()) {
        return $this->builder->whereJsonContains('meta', $payload->raw());
    }
}
```

---

## Summary

-   `Payload` standardizes how filter values are processed.
-   It provides helper methods (`asLike`, `asBoolean`, `isJson`, etc.).
-   This reduces repetitive code inside filter classes.
