---
title: Request Sanitization
description: Sanitize and normalize Laravel filter inputs before validation runs. Filterable ships 12 built-in sanitizers (trim, slug, clamp, boolean, escape_html and more) with support for custom aliases and per-field or global rules.
tags:
  - sanitization
  - data transformation
  - data cleaning
  - security
---

Sanitization allows you to clean or transform **incoming** request data **before** validation or filtering is applied.
This feature ensures your filters always work with clean and normalized data.

To enable sanitization in your filter class,
define a `protected $sanitizers` property.
Each entry in this array maps a **request key** to one or more sanitizer classes.

## Basic Example

```php
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;

class PostFilter extends Filterable
{
    protected $sanitizers = [
        'title' => TrimSanitizer::class,
    ];

    // ...
}
```

In this example, `TrimSanitizer` will be applied to the `title` field before validation or filtering runs.
See [Built-in Sanitizers](#built-in-sanitizers) for all available classes, or [Writing a Custom Sanitizer](#writing-a-custom-sanitizer) to create your own.

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

## Built-in Sanitizers

Filterable ships with **12 ready-to-use sanitizer classes** under the
`Kettasoft\Filterable\Sanitization\Defaults` namespace.
Each one can be referenced either by its **short alias string** or by its
full **class name**.

### Available Sanitizers

| Alias           | Class                        | Description                                           |
| --------------- | ---------------------------- | ----------------------------------------------------- |
| `trim`          | `TrimSanitizer`              | Removes leading/trailing whitespace (or custom chars) |
| `lowercase`     | `LowercaseSanitizer`         | Converts to lowercase (multibyte-safe)                |
| `uppercase`     | `UppercaseSanitizer`         | Converts to uppercase (multibyte-safe)                |
| `strip_tags`    | `StripTagsSanitizer`         | Strips HTML/PHP tags (inner text is preserved)        |
| `escape_html`   | `EscapeHtmlSanitizer`        | Converts `< > " '` to HTML entities                   |
| `integer`       | `IntegerSanitizer`           | Casts to `int`; optionally returns `null` on fail     |
| `float`         | `FloatSanitizer`             | Casts to `float`; optionally rounds to N decimals     |
| `boolean`       | `BooleanSanitizer`           | Maps `"true"/"yes"/"on"` → `true`, etc.               |
| `slug`          | `SlugSanitizer`              | Generates a URL-friendly slug via `Str::slug()`       |
| `null_if_empty` | `NullIfEmptySanitizer`       | Returns `null` for empty strings / `"0"` / `"null"`   |
| `clamp`         | `ClampSanitizer`             | Clamps a number between a min/max bound               |
| `strip_chars`   | `StripSpecialCharsSanitizer` | Removes non-alphanumeric characters                   |

---

### Usage by alias (string)

Use the short alias when no constructor arguments are needed:

```php
use Kettasoft\Filterable\Filterable;

class PostFilter extends Filterable
{
    protected $sanitizers = [
        'title'     => 'trim',
        'email'     => ['trim', 'lowercase'],
        'status'    => 'uppercase',
        'bio'       => ['trim', 'strip_tags', 'null_if_empty'],
        'is_active' => 'boolean',
        'category'  => 'slug',
        'search'    => 'null_if_empty',
        'name'      => 'strip_chars',
    ];
}
```

---

### Usage by class name

Use the full class name when you need the default constructor behaviour
but no extra configuration:

```php
use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\LowercaseSanitizer;

protected $sanitizers = [
    'email' => [TrimSanitizer::class, LowercaseSanitizer::class],
];
```

---

### Usage by object instance (with constructor arguments)

Pass an **instance** directly when you need to customise behaviour:

```php
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\FloatSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripTagsSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripSpecialCharsSanitizer;

protected $sanitizers = [
    // keep per_page between 1 and 100
    'per_page' => new ClampSanitizer(min: 1, max: 100),

    // round price to 2 decimal places
    'price'    => new FloatSanitizer(decimals: 2),

    // allow <b> and <i> tags
    'summary'  => new StripTagsSanitizer(allowedTags: '<b><i>'),

    // allow underscores and dashes in usernames
    'username' => new StripSpecialCharsSanitizer(allowed: '_-'),
];
```

---

### Usage by Closure

Pass an anonymous function when the transformation is simple enough not to
warrant a dedicated class:

```php
protected $sanitizers = [
    // cast to float and round inline
    'price' => fn ($v) => round((float) $v, 2),

    // split a comma-separated string into an array
    'tags'  => fn ($v) => is_string($v) ? array_map('trim', explode(',', $v)) : $v,

    // custom normalisation combining multiple steps
    'code'  => fn ($v) => strtoupper(preg_replace('/\s+/', '-', trim((string) $v))),
];
```

::: tip
Closures are great for quick one-off transformations. For reusable or
testable logic, prefer a dedicated class that implements `Sanitizable`.
:::

---

### Sanitizer reference

#### `TrimSanitizer`

```php
new TrimSanitizer(string $characters = " \t\n\r\0\x0B")
```

Trims `$characters` from both ends of the string.
Works on arrays (each element trimmed individually).

---

#### `LowercaseSanitizer` / `UppercaseSanitizer`

No constructor arguments. Uses `mb_strtolower` / `mb_strtoupper`.

---

#### `StripTagsSanitizer`

```php
new StripTagsSanitizer(?string $allowedTags = null)
```

Wraps PHP's `strip_tags()`.
Pass `'<b><i>'` to preserve specific tags.

::: warning
`strip_tags` removes the **tags** but keeps the **inner text** of disallowed
tags. Use `EscapeHtmlSanitizer` if you need to prevent user-supplied markup
from rendering.
:::

---

#### `EscapeHtmlSanitizer`

```php
new EscapeHtmlSanitizer(string $encoding = 'UTF-8')
```

Converts `< > " '` to HTML entities using `htmlspecialchars()`.

---

#### `IntegerSanitizer`

```php
new IntegerSanitizer(bool $nullOnFail = false)
```

Non-numeric values return `0` by default.
Set `$nullOnFail = true` to return `null` instead.

---

#### `FloatSanitizer`

```php
new FloatSanitizer(?int $decimals = null)
```

Pass an integer to round the result to that many decimal places.

---

#### `BooleanSanitizer`

No constructor arguments.

| Input                                       | Result                |
| ------------------------------------------- | --------------------- |
| `"true"` / `"1"` / `"yes"` / `"on"`         | `true`                |
| `"false"` / `"0"` / `"no"` / `"off"` / `""` | `false`               |
| native `bool` / `int`                       | passed through / cast |

---

#### `SlugSanitizer`

```php
new SlugSanitizer(string $separator = '-', string $language = 'en')
```

Delegates to Laravel's `Str::slug()`.

---

#### `NullIfEmptySanitizer`

```php
new NullIfEmptySanitizer(array $emptyValues = ['', '0', 'null', 'undefined', 'none'])
```

Returns `null` when the trimmed value matches any entry in `$emptyValues`.

---

#### `ClampSanitizer`

```php
new ClampSanitizer(int|float|null $min = null, int|float|null $max = null)
```

Non-numeric values pass through unchanged.
Float bounds cause the result to be cast to `float`.

---

#### `StripSpecialCharsSanitizer`

```php
new StripSpecialCharsSanitizer(string $allowed = '', string $replacement = '')
```

Strips everything that is not alphanumeric or whitespace.
`$allowed` is a string of extra characters to keep (e.g. `'_-'`).
`$replacement` is what each stripped character is replaced with (default: removed).

---

### Writing a Custom Sanitizer

Any class that implements the `Sanitizable` interface can be used as a sanitizer.
The interface requires a single method:

```php
namespace Kettasoft\Filterable\Sanitization\Contracts;

interface Sanitizable
{
    public function sanitize(mixed $value): mixed;
}
```

#### Step 1 — Create the class

```php
namespace App\Sanitizers;

use Kettasoft\Filterable\Sanitization\Contracts\Sanitizable;

class PhoneNumberSanitizer implements Sanitizable
{
    /**
     * Strip everything that is not a digit from the value.
     */
    public function sanitize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // Remove all non-digit characters
        return preg_replace('/\D/', '', $value);
    }
}
```

#### Step 2 — Use it in your filter

You can reference the class by name or pass an instance directly:

```php
use App\Sanitizers\PhoneNumberSanitizer;

class ContactFilter extends Filterable
{
    protected $sanitizers = [
        // by class name (default constructor)
        'phone' => PhoneNumberSanitizer::class,

        // or as an instance (useful when constructor args are needed)
        'mobile' => new PhoneNumberSanitizer,
    ];
}
```

#### Step 3 — (Optional) Register a short alias

Register the alias once in your `AppServiceProvider` so you can reference
it by a short string anywhere in your codebase:

```php
use Kettasoft\Filterable\Sanitization\HandlerFactory;
use App\Sanitizers\PhoneNumberSanitizer;

public function boot(): void
{
    HandlerFactory::extend([
        'phone' => PhoneNumberSanitizer::class,
    ]);
}
```

Then use it like any built-in alias:

```php
protected $sanitizers = [
    'phone'  => 'phone',
    'mobile' => 'phone',
];
```

::: tip
Sanitizers can handle **arrays** too — if the request value is an array
(e.g. a multi-select field), loop over each element inside `sanitize()`:

```php
public function sanitize(mixed $value): mixed
{
    if (is_array($value)) {
        return array_map(fn ($v) => $this->clean($v), $value);
    }

    return $this->clean($value);
}

private function clean(mixed $value): mixed
{
    return is_string($value) ? preg_replace('/\D/', '', $value) : $value;
}
```

:::

---

## Runtime Configuration

Sanitizers don't have to be declared statically inside the filter class.
You can add, replace, or remove them at runtime using the fluent API.

### `setSanitizers()`

Override or merge sanitizers after the filter instance is created:

```php
// Override all sanitizers for this request
PostFilter::for(Post::class)
    ->setSanitizers([
        'title' => 'trim',
        'email' => ['trim', 'lowercase'],
    ])
    ->apply()
    ->get();

// Merge with existing sanitizers (override: false)
PostFilter::for(Post::class)
    ->setSanitizers(['title' => 'trim'], override: false)
    ->apply()
    ->get();
```

### `withoutSanitizers()`

Disable all sanitizers entirely for a specific invocation:

```php
PostFilter::for(Post::class)
    ->withoutSanitizers()
    ->apply()
    ->get();
```

::: tip
`withoutSanitizers()` is useful in testing scenarios where you want to assert
raw filter behaviour without any data transformation applied.
:::
