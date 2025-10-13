# Conditional logic with `when` and `unless`

## Overview

The `when()` and `unless()` helpers let you conditionally modify a Filterable instance based on a boolean expression.
They provide a fluent, chainable alternative to verbose if/else statements. Both methods modify the current instance and return `$this`, making them ideal for method chaining.

## API

```php
public function when(bool $condition, callable $callback): static
public function unless(bool $condition, callable $callback): static

// The callback receives the current instance:
// function (\Kettasoft\Filterable\Filterable $filterable): void
```

## Basic usage

### `when()`

Executes the callback only when the condition is true.

```php
use Kettasoft\Filterable\Filterable;

$filter = Filterable::create()
    ->when($isAdmin, function (Filterable $f) {
        $f->setAllowedFields(['email', 'role']);
    })
    ->when($request->boolean('strict'), fn ($f) => $f->strict());
```

### `unless()`

Executes the callback only when the condition is false (inverse of `when`).

```php
use Kettasoft\Filterable\Filterable;

$filter = Filterable::create()
    ->unless($isAdmin, function (Filterable $f) {
        // Non-admins get a restricted set of fields
        $f->setAllowedFields(['name']);
    })
    ->unless($request->boolean('include_inactive'), function ($f) {
        // Apply additional constraints when a flag is NOT set
        $f->through([
            fn ($builder) => $builder->where('active', true),
        ]);
    });
```

You can use short arrow functions for compact callbacks:

```php
Filterable::create()->when($flag, fn ($f) => $f->permissive());
```

## Nesting and composition

`when()` and `unless()` can be freely nested and combined.

```php
Filterable::create()
    ->when($isAdmin, function ($f) {
        $f->setAllowedFields(['*']);

        $f->unless($isReadonlyMode, function ($f) {
            $f->sorting(fn ($s) => $s->default('created_at', 'desc'));

            $f->when(true, fn ($f) => $f->ignoreEmptyValues());
        });
    })
    ->unless($isGuest, fn ($f) => $f->strict());
```

## Real-world examples

### Role-based field access

```php
$filter = Filterable::create()
    ->when($user->isAdmin(), fn ($f) => $f->setAllowedFields(['*']))
    ->unless($user->isAdmin(), fn ($f) => $f->setAllowedFields(['name', 'email']))
    ->apply();
```

### Feature flag toggles

```php
$filter = Filterable::create()
    ->when(config('features.relaxed_filtering'), fn ($f) => $f->permissive())
    ->unless(config('features.allow_all_ops'), fn ($f) => $f->allowdOperators(['=', '!=', 'like']));
```

## Behavior

-   `when($condition, $callback)` runs the callback only when `$condition === true`.
-   `unless($condition, $callback)` runs the callback only when `$condition === false`.
-   In both cases, the same Filterable instance is returned (chainable, mutating API).
-   The callback receives the current instance and may call any configuration methods.
-   If the condition does not match, the callback is not executed.

## Tips

-   Prefer `unless($cond)` when expressing negative intent (reads naturally).
-   Combine with `through()` for custom query tweaks that run only when needed.
-   Closures are evaluated lazily—only when their conditions match.
-   Keep callbacks small and focused for readability and testability.

## See also

-   API reference: [`api/filterable.md`](/api/filterable.md#flow-control) (methods: `when`, `unless`, `through`)
