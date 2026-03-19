---
title: Lint Filter
description: Lint Filterable classes for configuration issues using filterable:lint. Detect orphaned keys, missing methods, bad annotations, and naming mismatches automatically.
tags:
    - cli
    - artisan
    - lint
    - debugging
    - code-quality
---

# Lint Filter

The `filterable:lint` command statically analyses your Filterable classes and reports configuration issues before they cause silent failures at runtime.
It checks method registration, naming conventions, annotation correctness, validation rule alignment, sanitizer alignment, and core method conflicts.

---

## Usage

```bash
# Lint a single filter
php artisan filterable:lint PostFilter

# Lint using a fully qualified class name
php artisan filterable:lint "App\Http\Filters\PostFilter"

# Lint all filters in app/Http/Filters
php artisan filterable:lint

# Fail on warnings too (useful in CI pipelines)
php artisan filterable:lint --strict
```

---

## Arguments

| Argument | Description                                                                 | Required |
| -------- | --------------------------------------------------------------------------- | -------- |
| `filter` | Filter class name or FQCN to lint. Omit to lint **all** discovered filters. | No       |

---

## Options

| Option     | Description                                                                           |
| ---------- | ------------------------------------------------------------------------------------- |
| `--strict` | Exit with a non-zero code even if only warnings are found. Useful for CI enforcement. |

---

## Exit Codes

| Condition                          | Exit code |
| ---------------------------------- | --------- |
| No issues found                    | `0`       |
| Warnings only (without `--strict`) | `0`       |
| Warnings only (with `--strict`)    | `1`       |
| One or more errors found           | `1`       |

---

## Example Output

```
 PostFilter (App\Http\Filters\PostFilter)
────────────────────────────────────────────────────────────────────────
  ✖ [L003] Filter key 'ghost' is registered in $filters but has no
           corresponding public method 'ghost()'.
  ⚠ [L004] Method 'hidden()' accepts a Payload but is not reachable
           from any key in $filters — it will never be called.
  ⚠ [L011] Filter key 'user_id' has a method named 'user_id()' but
           the engine resolves it to 'userId()'. Rename the method to
           'userId()' or the filter will never execute.

 1 error, 2 warnings found across 1 class.
```

---

## Diagnostic Codes

### ❌ Errors — always fail the command

---

#### `L001` — Class cannot be linted

Fired when the given class does not exist, does not extend `Filterable`, or throws an exception during instantiation.

```
✖ [L001] Failed to instantiate class — RuntimeException: Intentional failure.
```

**Fix:** Check the class constructor for missing dependencies, bad configuration, or runtime exceptions.

---

#### `L003` — Orphaned filter key

A key is registered in `$filters` but:

- has no corresponding public method, **or**
- its method exists but the first parameter is not type-hinted as `Payload`.

```php
// ❌ key 'status' has no method
protected $filters = ['status'];

// ❌ method exists but wrong param type
public function status(string $value) { ... }

// ✅ correct
public function status(Payload $payload) { ... }
```

::: tip Key → Method Resolution
`$filters` keys are converted to method names using `Str::camel()`, mirroring the Invokable engine:

- `status` → `status()`
- `user_id` → `userId()`
- `created_at` → `createdAt()`
  :::

---

#### `L005` — Annotation references a missing or invalid class

Fired for:

- `#[Cast('SomeClass')]` where `SomeClass` does not exist (built-in types like `int`, `string`, `bool` are allowed).
- `#[Authorize('SomeClass')]` where `SomeClass` does not exist or does not implement the `Authorizable` contract.

```php
// ❌ cast type class not found
#[Cast('App\Casts\NonExistentCast')]
public function price(Payload $payload) { ... }

// ❌ authorizer does not implement Authorizable
#[Authorize(SomeClass::class)]
public function status(Payload $payload) { ... }
```

---

#### `L010` — Filter key conflicts with a core Filterable method

A `$filters` key resolves to a method name that already exists on the base `Filterable` class (e.g. `validate`, `apply`, `rules`).

```php
// ❌ 'validate' conflicts with Filterable::validate()
protected $filters = ['validate'];
```

---

### ⚠️ Warnings — fail only with `--strict`

---

#### `L002` — Empty `$filters` array

The `$filters` array is empty, meaning no filter methods will ever be executed.

```php
// ⚠ nothing will run
protected $filters = [];
```

---

#### `L004` — Payload method not reachable from `$filters`

A public method type-hints `Payload` as its first parameter — a strong signal it is intended to be a filter method — but no key in `$filters` resolves to it.

```php
protected $filters = ['title'];

// ⚠ 'hidden' is never called
public function hidden(Payload $payload) { ... }
```

**Fix:** Either add `'hidden'` to `$filters`, or remove the method if it is no longer needed.

---

#### `L006` — `#[Scope]` references a missing model scope

The scope name passed to `#[Scope('x')]` does not correspond to a `scopeX()` method on the filter's registered model.

```php
// ⚠ scopePublished() not found on Post model
#[Scope('published')]
public function status(Payload $payload) { ... }
```

---

#### `L007` — Annotation on a method not in `$filters`

An annotation (`#[Cast]`, `#[Authorize]`, `#[Scope]`, etc.) is placed on a method that is not reachable from any `$filters` key — so the annotation will never run.

```php
protected $filters = [];

// ⚠ annotation has no effect — method not in $filters
#[Cast('int')]
public function price(Payload $payload) { ... }
```

---

#### `L008` — Validation rule for a key not in `$filters`

The `rules()` method defines a validation rule for a field that is not registered in `$filters` — so the rule will never be enforced.

```php
protected $filters = ['title'];

public function rules(): array {
    return [
        'ghost' => 'required|string', // ⚠ 'ghost' not in $filters
    ];
}
```

---

#### `L009` — Sanitizer for a key not in `$filters`

The `$sanitizers` array defines a sanitizer for a field that is not in `$filters` — so the sanitizer will never run.

```php
protected $filters  = ['title'];
protected $sanitizers = [
    'ghost' => 'trim', // ⚠ 'ghost' not in $filters
];
```

---

#### `L011` — Method named after raw key instead of camelCase equivalent

A method exists using the raw `$filters` key name (e.g. `user_id()`) instead of its camelCase equivalent (`userId()`). The Invokable engine resolves keys via `Str::camel()`, so `user_id()` will **never be called**.

```php
protected $filters = ['user_id'];

// ⚠ engine will look for userId(), not user_id()
public function user_id(Payload $payload) { ... }

// ✅ correct
public function userId(Payload $payload) { ... }
```

---

## CI/CD Integration

Use `--strict` to enforce zero-warning quality in pipelines:

```yaml
# .github/workflows/ci.yml
- name: Lint Filterable classes
  run: php artisan filterable:lint --strict
```

Or lint a specific filter in a pre-commit hook:

```bash
php artisan filterable:lint PostFilter --strict
```

---

## Typical Workflow

```bash
# 1. Create a new filter
php artisan filterable:make-filter PostFilter --filters=title,status,user_id

# 2. Lint immediately to catch any issues
php artisan filterable:lint PostFilter

# 3. Inspect the full configuration
php artisan filterable:inspect PostFilter
```

---

## Related Commands

| Command                  | Description                                                  |
| ------------------------ | ------------------------------------------------------------ |
| `filterable:inspect`     | Inspect the full runtime configuration of a single filter.   |
| `filterable:list`        | List all registered Filterable classes with a summary table. |
| `filterable:add-method`  | Add a new filter method to an existing class.                |
| `filterable:make-filter` | Scaffold a new Filterable class from scratch.                |
