---
title: Request Sanitization
description: Learn how to clean and transform incoming request data using the powerful sanitization features of the Filterable package.
tags:
  - sanitization
  - cleaning
  - transformation
  - normalization
---

Sanitization allows you to clean or transform incoming request data **before** validation or filtering is applied.
This feature ensures your filters always work with clean and normalized data.

---

## Overview

To enable sanitization in your filter class, define a `protected $sanitizers` property.
Each entry maps a **request key** to one or more sanitizers, which can be:

- **Aliases** (e.g., `'trim'`, `'lowercase'`)
- **Pipe-separated strings** (e.g., `'trim|lowercase|slug'`)
- **Class names** (e.g., `TrimSanitizer::class`)
- **Closures** (e.g., `fn($value) => strtolower($value)`)
- **Arrays** of any of the above
- **Instantiated objects** implementing `Sanitizable`

---

## Quick Start

### Using Built-in Aliases

```php
use Kettasoft\Filterable\Filterable;

class PostFilter extends Filterable
{
  protected $sanitizers = [
    'title' => 'trim|lowercase',
    'price' => 'float',
    'status' => 'boolean',
    'slug' => 'trim|slug'
  ];

  // ...
}
```

---

## Built-in Sanitizers

The package includes 12 ready-to-use sanitizers accessible via **short aliases**:

| Alias           | Class                        | Description                                  |
| --------------- | ---------------------------- | -------------------------------------------- |
| `trim`          | `TrimSanitizer`              | Removes leading/trailing whitespace          |
| `lowercase`     | `LowercaseSanitizer`         | Converts to lowercase (multibyte-safe)       |
| `uppercase`     | `UppercaseSanitizer`         | Converts to uppercase (multibyte-safe)       |
| `integer`       | `IntegerSanitizer`           | Casts value to integer                       |
| `float`         | `FloatSanitizer`             | Casts value to float                         |
| `boolean`       | `BooleanSanitizer`           | Converts truthy/falsy values to boolean      |
| `slug`          | `SlugSanitizer`              | Converts to URL-friendly slug                |
| `strip_tags`    | `StripTagsSanitizer`         | Removes HTML/PHP tags                        |
| `strip_chars`   | `StripSpecialCharsSanitizer` | Removes special characters                   |
| `escape_html`   | `EscapeHtmlSanitizer`        | Escapes HTML special characters              |
| `null_if_empty` | `NullIfEmptySanitizer`       | Converts empty strings to `null`             |
| `clamp`         | `ClampSanitizer`             | Clamps numeric values between min/max bounds |

---

## Usage Patterns

### 1. Using Aliases (Recommended)

The simplest and most readable approach:

```php
protected $sanitizers = [
  'email' => 'trim|lowercase',
  'name' => 'trim|uppercase',
  'age' => 'integer',
  'bio' => 'strip_tags|trim'
];
```

### 2. Using Class Names

```php
use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\LowercaseSanitizer;

protected $sanitizers = [
  'email' => [
    TrimSanitizer::class,
    LowercaseSanitizer::class
  ]
];
```

### 3. Using Closures

For inline transformations:

```php
protected $sanitizers = [
  'phone' => fn($value) => preg_replace('/[^0-9]/', '', $value),
  'username' => fn($value) => strtolower(trim($value))
];
```

### 4. Using Instantiated Objects

Useful when you need to pass constructor parameters:

```php
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;

protected $sanitizers = [
  'per_page' => new ClampSanitizer(min: 1, max: 100),
  'discount' => new ClampSanitizer(min: 0, max: 100)
];
```

### 5. Mixing Approaches

You can combine different approaches:

```php
protected $sanitizers = [
  'title' => 'trim|strip_tags',
  'slug' => [
    'trim',
    'lowercase',
    fn($value) => str_replace(' ', '-', $value)
  ],
  'price' => new ClampSanitizer(min: 0)
];
```

---

## Global Sanitizers

Apply a sanitizer to **all request inputs** by specifying it without a key (using numeric array indexes):

```php
protected $sanitizers = [
  'trim',      // Global: applies to all fields
  'name' => 'lowercase',
  'email' => 'lowercase'
];
```

You can also use multiple global sanitizers:

```php
protected $sanitizers = [
  TrimSanitizer::class,           // Global #1
  fn($v) => strtolower($v),       // Global #2
  'slug' => 'slug'                // Field-specific
];
```

::: tip Execution Order
Global sanitizers run **before** field-specific sanitizers. This ensures all fields are cleaned globally first, then processed individually.

**Example:**

```php
protected $sanitizers = [
  'trim',                  // Step 1: trim all fields
  'email' => 'lowercase'   // Step 2: lowercase only email
];

// Input: '  ADMIN@EXAMPLE.COM  '
// After global (trim): 'ADMIN@EXAMPLE.COM'
// After field-specific (lowercase): 'admin@example.com'
```

:::

---

## Creating Custom Sanitizers

### Implement the `Sanitizable` Interface

```php
<?php

namespace App\Sanitizers;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

class TitleSanitizer implements Sanitizable
{
  public function sanitize(mixed $value): mixed
  {
    if (!is_string($value)) {
      return $value;
    }

    // Remove extra whitespace and capitalize
    return ucwords(trim(preg_replace('/\s+/', ' ', $value)));
  }
}
```

### Use It in Your Filter

```php
use App\Sanitizers\TitleSanitizer;

class PostFilter extends Filterable
{
  protected $sanitizers = [
    'title' => TitleSanitizer::class
  ];
}
```

---

## Registering Custom Aliases

You can register your own aliases globally using `Sanitizer::extend()`:

```php
use Kettasoft\Filterable\Sanitization\Sanitizer;
use App\Sanitizers\TitleSanitizer;

// In a service provider's boot() method:
Sanitizer::extend('title', TitleSanitizer::class);
```

Now you can use the alias anywhere:

```php
protected $sanitizers = [
  'post_title' => 'trim|title',
  'page_title' => 'title'
];
```

---

## Execution Lifecycle

Sanitization happens early in the filter execution pipeline:

1. **Authorization**
2. **Validation**
3. **Global sanitizers** (applied to all keys)
4. **Field-specific sanitizers** (per key, in order)
5. **Filtering**

This ensures all downstream processes work with clean, normalized data.

---

## Advanced Examples

### Example 1: E-commerce Product Filter

```php
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;

class ProductFilter extends Filterable
{
  protected $sanitizers = [
    'trim', // global: trim all inputs

    'name' => 'strip_tags|trim',
    'slug' => 'lowercase|slug',
    'price' => 'float',
    'stock' => 'integer',
    'is_active' => 'boolean',
    'discount' => new ClampSanitizer(min: 0, max: 100),
    'per_page' => new ClampSanitizer(min: 1, max: 100)
  ];

  protected $rules = [
    'name' => ['sometimes', 'string', 'max:255'],
    'price' => ['sometimes', 'numeric', 'min:0']
  ];

  public function name(Payload $payload)
  {
    return $this->builder->where('name', 'like', $payload->asLike());
  }

  public function price(Payload $payload)
  {
    return $this->builder->where('price', '<=', $payload->asInt());
  }
}
```

### Example 2: User Search Filter

```php
class UserFilter extends Filterable
{
  protected $sanitizers = [
    'trim', // Global: trim all inputs

    'email' => 'lowercase|null_if_empty',
    'username' => 'lowercase',
    'age' => 'integer',
    'bio' => 'strip_tags|escape_html',
    'role' => fn($value) => in_array($value, ['admin', 'user']) ? $value : 'user'
  ];

  public function email(Payload $payload)
  {
    return $this->builder->where('email', $payload);
  }

  public function username(Payload $payload)
  {
    return $this->builder->where('username', 'like', $payload->asLike());
  }
}
```

---

## Tips & Best Practices

::: tip Always Trim User Input
Add `'trim'` as a global sanitizer to prevent whitespace-related bugs:

```php
protected $sanitizers = [
  'trim',
  // other sanitizers...
];
```

:::

::: tip Use Pipe Syntax for Readability
Instead of arrays, use pipe-separated strings for better readability:

```php
// ✅ Good
'email' => 'trim|lowercase|null_if_empty'

// ❌ Less readable
'email' => [TrimSanitizer::class, LowercaseSanitizer::class, NullIfEmptySanitizer::class]
```

:::

::: warning Order Matters
Sanitizers execute in the order defined. Make sure the order is logical:

```php
// ✅ Correct order
'slug' => 'trim|lowercase|slug'

// ❌ Wrong order (slug should be last)
'slug' => 'slug|trim|lowercase'
```

:::
