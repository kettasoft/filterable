# Sorter

The **Sorter** class provides functionality to manage and apply sorting rules to Eloquent queries.
It simplifies the process of sorting models by accepting parameters like field names, directions, aliases, and default sorting. You can also customize the sorting behavior, including handling nulls and multi-field sorting.

---

### Overview

The **Sorter** class allows developers to configure sorting behavior on Eloquent queries based on user input. You can define allowed sortable fields, set default sorting, create sorting aliases (presets), and customize sorting behaviors such as handling null values and multi-field sorting.

```php
$sorter = new Sorter($request);
$sorter->allow(['title', 'created_at'])
       ->setSortKey('sort')
       ->setDelimiter(',')
       ->setNullsPosition('last')
       ->apply($query);
```

---

### Properties

| Property     | Type                                                     | Description                                                                    |
| ------------ | -------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `$allowed`   | `array<int, string>`                                     | List of allowed fields for sorting.                                            |
| `$default`   | `array{0: string, 1: string} or null`                    | Default sorting field and direction (e.g., `['created_at', 'desc']`).          |
| `$aliases`   | `array<string, array<int, array{0: string, 1: string}>>` | Aliases for sorting presets (e.g., `['recent' => [['created_at', 'desc']]]`).  |
| `$map`       | `array<string, string>`                                  | Field mapping for input to database columns (e.g., `['name' => 'full_name']`). |
| `$config`    | `\Illuminate\Support\Collection`                         | Configuration settings for the sorter.                                         |
| `$sortKey`   | `string`                                                 | The key used for sorting in the request (e.g., `sort`).                        |
| `$delimiter` | `string`                                                 | Delimiter used for multi-field sorting (e.g., `,`).                            |

---

### Public Methods

---

#### `__construct(Request $request, array|null $config = null)`

Creates a new Sorter instance. Optionally accepts a configuration array.

```php
$sorter = new Sorter($request, $config);
```

---

#### `static make(Request $request, array|null $config = null): self`

Static factory method to create a new Sorter instance.

```php
$sorter = Sorter::make($request);
```

---

#### `map(array $fields): self`

Maps input fields to database columns.

```php
$sorter->map(['name' => 'full_name']);
```

---

#### `getFieldMapping(string $field): string`

Gets the mapped database column for a given input field.

```php
$column = $sorter->getFieldMapping('name');
```

---

#### `allow(array $fields): self`

Defines which fields are allowed for sorting.

```php
$sorter->allow(['title', 'created_at']);
```

---

#### `allowAll(): self`

Allows sorting on all fields (use with caution, may expose sensitive fields).

```php
$sorter->allowAll();
```

---

#### `default(string $field, string $direction = 'asc'): self`

Defines a default sorting field and direction.

```php
$sorter->default('created_at', 'desc');
```

---

#### `defaults(array{0: string, 1: string} $defaults): self`

Defines default sorting using an array.

```php
$sorter->defaults(['created_at', 'desc']);
```

---

#### `alias(string $name, array $sorting): self`

Defines a sorting alias (preset).

```php
$sorter->alias('popular', [['views', 'desc'], ['likes', 'desc']]);
```

---

#### `aliases(array<string, array<int, array{0: string, 1: string}>> $aliases): self`

Defines multiple sorting aliases (presets).

```php
$sorter->aliases([
    'popular' => [['views', 'desc'], ['likes', 'desc']],
    'recent' => [['created_at', 'desc']],
]);
```

---

#### `setSortKey(string $key): self`

Sets the key used for sorting in the request.

```php
$sorter->setSortKey('order');
```

---

#### `setDelimiter(string $delimiter): self`

Sets the delimiter used for multi-field sorting.

```php
$sorter->setDelimiter(',');
```

---

#### `setNullsPosition(string|null $position = null): self`

Sets the position of null values in sorting.

-   Accepts: `'first'`, `'last'`, or `null` for default DB behavior.

```php
$sorter->setNullsPosition('first');
```

---

#### `apply(Builder $query): Builder`

Applies the sorting rules to the given Eloquent query.

```php
$sorter->apply($query);
```

---

### Example Usage

```php
$sorter = Sorter::make($request);

$sorter->allow(['title', 'created_at'])
       ->setSortKey('sort')
       ->setDelimiter(',')
       ->setNullsPosition('last');

$query = $sorter->apply(Post::query());
```

---

### Summary

-   **`Sorter`** manages the sorting logic for Eloquent queries.
-   It allows you to define which fields are sortable, set default sorting, and create sorting aliases.
-   Customizable features include sorting with multi-fields, null value handling, and request-based sorting keys.
