# Sorting

---

The `Sorting` feature provides a declarative and customizable way to apply ordering to Eloquent queries via request parameters.

It allows you to control which fields are sortable, set default sorting behavior, define aliases for complex sorting logic, and decide how multiple fields and null values should be handled.

You can apply sorting rules locally per filter or register global sorting logic across multiple filters. Sorting can be configured using closures or invokable classes.

-   Control which fields are sortable.
-   Apply default sorting automatically.
-   Use aliases for common sorting patterns.
-   Support multiple fields with ascending/descending directions.

## Configuration

The sorting behavior is configured using the following options:

```php
'sorting' => [
    'sort_key' => 'sort', // The request key, e.g., ?sort=name
    'allowed' => [], // List of sortable fields
    'default' => null, // ['field', 'direction']
    'aliases' => [], // Custom named sort definitions
    'multi_sort' => true, // Enable multi-field sorting
    'delimiter' => ',', // Separator for multi-sorting
    'direction_map' => [
        'asc' => 'asc',
        'desc' => 'desc',
        'prefix' => '-', // Prefix for descending fields: ?sort=-created_at
    ],
    'nulls_position' => null, // 'first', 'last', or null (default DB behavior)
],
],
```

## How it Works

-   The request parameter sort controls the sorting.
-   Fields are separated by `delimiter`.
-   A leading - means descending order.
-   Sorting only applies to fields defined in allow().

## Local Sorting

You can define sorting behavior directly within a filter instance by chaining the `sorting()` method.

Example:

```http
GET /posts?sort=title,-id
```

```php
use Kettasoft\Filterable\Foundation\Contracts\Sortable;

$request = request()->merge(['sort' => 'id,title']);

$filter = PostFilter::create($request)->sorting(function (Sortable $sort) {
    return $sort->allow(['id', 'title']);
});

$posts = Post::filter($filter)->get();
```

> Use `allow()` to restrict sortable fields and prevent unauthorized sorting.

## Global Sorting

You can register sorting logic globally across multiple filters using `Filterable::addSorting()`:

```php
use Kettasoft\Filterable\Foundation\Filterable;
use Kettasoft\Filterable\Foundation\Contracts\Sortable;

Filterable::addSorting(
    [ProductFilter::class, ServiceFilter::class],
    function (Sortable $sort) {
        return $sort->allow(['created_at', 'price', 'id']);
    }
);
```

This approach is ideal when multiple filters share the same sorting rules.

## Using Invokable Sort Classes

Instead of using closures, you can define reusable invokable sorting classes that implement:

Example:

```php
use Kettasoft\Filterable\Foundation\Contracts\Sortable\Invokable;
use Kettasoft\Filterable\Foundation\Contracts\Sortable;

class CustomSort implements Invokable
{
    public function __invoke(Sortable $sort): Sortable
    {
        return $sort->allow(['id'])
            ->default('created_at', 'desc');
    }
}
```

Then use the class:

```php
PostFilter::create()->sorting(CustomSort::class);
```

---

## Sorting Aliases

You can define **aliases** for common or complex sort presets. These aliases can be referenced by name in the query string.

### Example:

```php
$sort->alias('recent', [['created_at', 'desc']]);
$sort->alias('popular', [['views', 'desc'], ['likes', 'desc']]);
```

Then in the URL:

```http
/posts?sort=recent
```

---

## Field Mapping

In cases where the request's sorting keys do not match your actual database column names, you can use the `map()` method to translate input fields to their corresponding database columns.

### Usage

```php
$sort->allow(['name', 'created'])
     ->map([
         'name' => 'user_name',
         'created' => 'created_at',
     ]);
```

Then, when a request like the following is made:

```http
/users?sort=-created,name
```

It will be translated internally to:

```sql
ORDER BY created_at DESC, user_name ASC
```

> ✅ This is useful when you want to expose clean or abstract field names via the API, while keeping internal database schema hidden or more flexible.

:::important Important Note
Make sure any field you map is also included in the `allow()` list. The mapping only affects column translation — it **does not** automatically validate allowed fields.
:::

✅ Correct

```php
$sort->allow(['created'])->map(['created' => 'created_at']);
```

⚠️ Incorrect

```php
$sort->map(['created' => 'created_at']); //  Incorrect unless 'created' is also allowed
```

---

## Multi-Sorting

When enabled (`multi_sort = true`), you can sort by multiple fields:

```http
?sort=name,-created_at
```

If disabled, only the first field is considered.

Use the `delimiter` option to control the field separator (default is a comma => ,).

---

## Sorting Direction

Direction is controlled via the `direction_map` config:

-   Prefixing a field with `-` implies descending order.
-   No prefix implies ascending.

Examples:

```http
?sort=-created_at   # DESC
?sort=name          # ASC
```

---

## Nulls Position

Control how `NULL` values are ordered using `nulls_position`:

-   `'first'`: Nulls appear before non-nulls.
-   `'last'`: Nulls appear after non-nulls.
-   `null`: Use database default behavior.

Example (if supported by DB driver):

```
ORDER BY created_at ASC NULLS LAST
```

---

## Customizing Instance Settings

In addition to configuring sorting behavior globally via the config file, you can override certain settings per instance when defining sorting logic — whether locally or globally.

This provides greater flexibility when different filters require different query keys or behaviors.

### Custom Sort Key

You can override the default sort key (`sort`) by calling `setSortKey()`:

```php
$sort->setSortKey('s');
```

> This will now expect sorting parameters like: /posts?s=title

---

## ⚠️ Warnings

:::danger Be careful with `$sort->allow(['*'])`
Using the wildcard `['*']` in the `allow()` method will enable **sorting by all available request fields** — including potentially unsafe or sensitive columns.
:::

```php
$sort->allow(['*']); // Not recommended
```

This effectively removes any restriction on what fields users can sort by, which can:

-   Allow unintended fields to be sorted (e.g., `password`, `token`, etc.).
-   Introduce performance issues if users sort on unindexed or non-optimized fields.
-   Expose internal schema or data patterns through error messages.

> ✅ **Recommended:** Always whitelist only the fields you explicitly want to allow.

```php
$sort->allow(['id', 'title', 'created_at']); // Safe and intentional
```

---

## Summary

-   Use `sorting()` to apply sorting to filters.
-   You can use closures or invokable classes.
-   Sorting supports multiple fields, default rules, aliases, and null handling.
-   All behavior is configurable via the `sorting` config section.
