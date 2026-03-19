---
title: Add Method to Filter
description: Add a new filter method to an existing Filterable class using filterable:add-method. Specify method name and insertion point without editing the file manually.
tags:
    - cli
    - artisan
    - code-generation
    - filter-method
    - stub
---

## **Purpose**

The `filterable:add-method` command injects a new filter method into an existing Filterable class file.
It renders the method from the `method.stub` template, inserts it at the correct position in the source file,
and automatically registers the key in the class's `$filters` array — all without you touching the file manually.

## Usage

```bash
php artisan filterable:add-method {filter} --name={method} --after={existingMethod}
```

## Arguments

| Argument | Description                                                                        | Example                                                    |
| -------- | ---------------------------------------------------------------------------------- | ---------------------------------------------------------- |
| `filter` | The Filterable class name or fully qualified class name (FQCN) you want to modify. | `php artisan filterable:add-method PostFilter --name=type` |

## Options

| Option    | Description                                                                                      | Example           |
| --------- | ------------------------------------------------------------------------------------------------ | ----------------- |
| `--name`  | **(Required)** The name of the new filter method to add. Must be a valid PHP identifier.         | `--name=isActive` |
| `--after` | **(Optional)** Insert the new method immediately after this existing method. Fails if not found. | `--after=status`  |

## Examples

### Add a method at the end of the class

```bash
php artisan filterable:add-method PostFilter --name=publishedAt
```

**Output:**

```
✅ Method 'publishedAt' added to 'App\Http\Filters\PostFilter' successfully.
```

The generated method is appended before the class's closing `}`:

```php
/**
 * Filter the query by a given publishedAt.
 *
 * @param Payload $payload
 * @return Builder
 */
public function publishedAt(Payload $payload)
{
    if ($payload->value) {
        return $this->builder->where('publishedAt', $payload->operator, $payload);
    }

    return $this->builder;
}
```

---

### Insert after a specific method

```bash
php artisan filterable:add-method PostFilter --name=category --after=status
```

**Output:**

```
✅ Method 'category' added to 'App\Http\Filters\PostFilter' successfully.
   Inserted after method: status
```

The new method is placed immediately after the closing `}` of the `status` method.

---

### Using a fully qualified class name

```bash
php artisan filterable:add-method "App\Http\Filters\PostFilter" --name=slug
```

---

## How It Works

1. **Resolves the filter class** from the given name — tries the exact input, then `App\Filters\{name}`, then the configured namespace (`filterable.namespace`).
2. **Validates the method name** — must match `/^[a-zA-Z_][a-zA-Z0-9_]*$/`. Invalid names cause an immediate failure.
3. **Guards against duplicates** — if a method with the same name already exists, the command warns and exits cleanly without modifying the file.
4. **Renders the stub** — uses `method.stub` via `Stub::create('method.stub', ['NAME' => $name])->render()`.
5. **Inserts the method**:
    - With `--after`: walks the source file, finds the target method's closing `}` via brace-depth tracking, and inserts immediately after it.
    - Without `--after`: inserts before the final closing `}` of the class.
6. **Updates `$filters`** — appends the new key to the `$filters = [...]` array, preserving formatting (single-line or multi-line).
7. **Writes the file back** to disk.

---

## Validation Rules

| Rule                  | Behavior                                                           |
| --------------------- | ------------------------------------------------------------------ |
| `--name` is required  | Exits with error if omitted or empty.                              |
| Valid PHP identifier  | Must match `/^[a-zA-Z_][a-zA-Z0-9_]*$/` — no digits as first char. |
| Filter class exists   | Exits with error if the class cannot be resolved.                  |
| Method already exists | Emits a warning and exits with `0` (no file modification).         |
| `--after` not found   | Exits with error without modifying the file.                       |

---

## Error Examples

```bash
# Missing --name
php artisan filterable:add-method PostFilter
# ❌ The --name option is required.

# Invalid method name
php artisan filterable:add-method PostFilter --name=1invalid
# ❌ Invalid method name: '1invalid'. Only letters, digits, and underscores are allowed, and it must not start with a digit.

# Class not found
php artisan filterable:add-method GhostFilter --name=title
# ❌ Filter class 'GhostFilter' could not be found.

# --after target missing
php artisan filterable:add-method PostFilter --name=slug --after=nonExistent
# ❌ Method 'nonExistent' not found in 'App\Http\Filters\PostFilter'. Cannot insert after it.

# Duplicate method
php artisan filterable:add-method PostFilter --name=status
# ⚠️  Method 'status' already exists in 'App\Http\Filters\PostFilter'. Skipping.
```

---

## Generated Method Template

The command uses `stubs/method.stub` to render each method:

```php
/**
 * Filter the query by a given $$NAME$$.
 *
 * @param Payload $payload
 * @return Builder
 */
public function $$NAME$$(Payload $payload)
{
    if ($payload->value) {
        return $this->builder->where('$$NAME$$', $payload->operator, $payload);
    }

    return $this->builder;
}
```

`$$NAME$$` is replaced with the value passed to `--name`. You can override the stub path via `filterable.generator.stubs` in `config/filterable.php`.

---

## Typical Workflow

```bash
# 1. Create the filter class
php artisan filterable:make-filter PostFilter --filters=title,status

# 2. Add more methods later without editing the file manually
php artisan filterable:add-method PostFilter --name=publishedAt --after=status
php artisan filterable:add-method PostFilter --name=category --after=publishedAt

# 3. Verify the result
php artisan filterable:inspect PostFilter
```

---

## Notes

- The `--after` insertion uses **brace-depth tracking**, so it correctly handles methods with nested closures or complex bodies.
- The `$filters` array updater preserves your existing formatting — it will not reformat the array.
- The stub path is resolved from `config('filterable.generator.stubs')`, so custom stubs are respected automatically.
- Works with any filter class that lives in a file reachable via `ReflectionClass::getFileName()`.

---

## Related Commands

| Command                  | Description                                            |
| ------------------------ | ------------------------------------------------------ |
| `filterable:make-filter` | Create a new Filterable class from scratch.            |
| `filterable:inspect`     | Inspect the configuration of an existing filter.       |
| `filterable:list`        | List all registered Filterable classes in the project. |
