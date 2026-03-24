---
title: Filter Any Eloquent Model Without Traits
description: Use the static `for()` method to apply filters to any Eloquent model without requiring the `HasFilterable` trait or a `$filterable` property.
tags:
  - features
  - for-static-method
  - trait-free-filtering
  - filterable-for
  - static-factory
  - auto-binding-alternative
---

# Trait-Free Filtering with `Filterable::for()`

## Overview

`Filterable::for()` is a static factory method that lets you apply filters to **any Eloquent model** without requiring the model to use the `HasFilterable` trait or define a `$filterable` property.

It is the recommended approach when you don't want to (or can't) modify the model class.

---

## The Problem It Solves

The traditional way to use Filterable requires the model to include the `HasFilterable` trait:

```php
// ❌ Traditional approach — model must use the trait
class Post extends Model
{
    use HasFilterable;
    protected $filterable = PostFilter::class;
}

// Then in a controller:
Post::filter()->get();
```

This couples the model to the filtering layer. With `Filterable::for()` you no longer need the trait at all.

---

## Basic Usage

```php
use Kettasoft\Filterable\Filterable;
use App\Models\Post;

// Using the base Filterable class
Filterable::for(Post::class)->apply()->get();

// Or using your own filter subclass
PostFilter::for(Post::class)->apply()->get();
```

---

## Method Signature

```php
public static function for(
    \Illuminate\Database\Eloquent\Model|string $model,
    \Illuminate\Http\Request|null $request = null
): static
```

| Parameter  | Type            | Description                                                          |
| ---------- | --------------- | -------------------------------------------------------------------- |
| `$model`   | `Model\|string` | Model class name or instance to filter.                              |
| `$request` | `Request\|null` | Optional. Injects a custom request; defaults to the current request. |

**Returns:** a new `static` Filterable instance with the model bound, ready to chain further configuration or call `->apply()`.

---

## Examples

### Controller — basic

```php
use Kettasoft\Filterable\Filterable;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        return Filterable::for(Post::class)
            ->apply()
            ->paginate();
    }
}
```

### Using your own filter subclass

```php
use App\Filters\PostFilter;
use App\Models\Post;

$posts = PostFilter::for(Post::class)->apply()->get();
```

### Passing a model instance

```php
$post = new Post;

$result = Filterable::for($post)->apply()->get();
```

### Injecting a custom request

```php
use Illuminate\Http\Request;

$request = Request::create('/', 'GET', ['status' => 'active']);

$posts = PostFilter::for(Post::class, $request)->apply()->get();
```

### Chaining additional configuration

`for()` returns the same Filterable instance, so all fluent methods are still available:

```php
PostFilter::for(Post::class)
    ->ignoreEmptyValues()
    ->strict()
    ->setAllowedFields(['title', 'status', 'author_id'])
    ->apply()
    ->paginate(15);
```

### Conditional logic

```php
PostFilter::for(Post::class)
    ->when($request->boolean('strict'), fn ($f) => $f->strict())
    ->unless($isAdmin, fn ($f) => $f->setAllowedFields(['title', 'status']))
    ->apply()
    ->get();
```

---

## Comparison: `HasFilterable` vs `Filterable::for()`

|                                 | `HasFilterable` trait | `Filterable::for()` |
| ------------------------------- | --------------------- | ------------------- |
| Requires trait on model         | ✅ Yes                | ❌ No               |
| Requires `$filterable` property | ✅ Yes                | ❌ No               |
| Works on third-party models     | ❌ No                 | ✅ Yes              |
| Fluent chaining support         | ✅ Yes                | ✅ Yes              |
| Custom request injection        | ❌ Not directly       | ✅ Yes              |
| Works with filter subclasses    | ✅ Yes                | ✅ Yes              |

---

## Notes

- `Filterable::for()` internally calls `setModel()` on a freshly created instance — all engines, sorting, caching, and lifecycle hooks work exactly the same.
- The method is **inherited by subclasses**, so `MyFilter::for(Post::class)` creates an instance of `MyFilter`, not the base `Filterable`.
- If you still prefer the scoped approach (`Post::filter()`), keep using `HasFilterable`. Both approaches coexist without conflict.

---

## See Also

- [Auto Binding](/features/auto-binding) — bind a default filter class directly on the model
- [Filter Aliases](/features/aliasing) — resolve filters by short alias names
- [Conditional Logic](/features/conditional-logic) — `when()` / `unless()` helpers
- [Invoker](/execution/invoker) — the object returned by `->apply()`
