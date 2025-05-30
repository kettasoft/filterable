# Request Sanitization

Sanitization allows you to clean or transform incomming reuqest data **before** validation or filtering is applied.
This feature ensures your filters always work with clean and normalized data.

## Overview

To enable sanitization in your filter class,
define a `protected $sanitizers` property.
Each entry in this array maps a **request key** to one or more sanitizer classes.

## Basic Example

```php
class PostFilter extends Filterable
{
  protected $sanitizers = [
    'title' => TitleSanitizer::class
  ];

  // ...
}
```

In this example, `TitleSanitizer` will be applied to the title field of the request before validation or filtering.

## Creating a Sanitizer Class

A sanitizer class must implement a `Sanitizable` interface

```php
class TitleSanitizer implement Sanitizable
{
  public function sanitize(mixed $value)
  {
    return is_string($value) ? trim($value) : $value;
  }
}
```

## Multiple Sanitizers Per field

You can apply multiple sanitizers to the same field by using array:

```php
protected $sanitizers = [
  'title' => [
    TrimSanitizer::class,
    CapitalizeSanitizer::class
  ]
];
```

Sanitizers are applied **in the order defined**.

## Global Sanitizers

You may apply a sanitizer globally to all request inputs by specifying the class **without a key**:

```php
protected $sanitizers = [
    TrimSanitizer::class, // will apply to all keys
];
```

::: tip Note
Global sanitizers will run **before** field-specific sanitizers.
:::

## Execution Lifecycle

1. **Global sanitizers** (apply to all keys)
2. Field-specific sanitizers (per key, in array order)
3. Validation
4. Authorization
5. Filtering

## Example Scenario

```php
class ProductFilter extends Filterable
{
  protected $sanitizers = [
    TrimSanitizer::class,
    'name' => [
      StripTagsSanitizer::class,
      CapitalizeSanitizer::class
    ]
  ];

  protected $rules [
    'name' => ['required', 'string']
  ];

  public function name($value)
  {
    return $this->builder->where('name', $value);
  }
}
```
