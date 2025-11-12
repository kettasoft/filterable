# Lifecycle Hooks

The `Filterable` base class provides two lifecycle hooks that let you modify the query builder **before** and **after** filters are applied.

These hooks are **optional** and can be defined directly inside your filter class.

---

#### **`initially()`**

Runs **before any filters are executed**.
It’s perfect for setting up default query conditions or preparing the builder.

```php
use Kettasoft\Filterable\Filterable;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filterable
{
    protected function initially(Builder $builder): void
    {
        // Example: Apply a global condition before filtering
        $builder->where('is_active', true);
    }
}
```

---

#### **`finally()`**

Runs **after all filters have been processed**.
It allows you to finalize or clean up your query logic.

```php
protected function finally(Builder $builder): void
{
    // Example: Apply a default sort order
    if (! $builder->getQuery()->orders) {
        $builder->orderBy('created_at', 'desc');
    }
}
```

---

#### ⚙️ How It Works

-   `initially()` is invoked **right before** any filter method runs.
-   `finally()` is called **after** all filter methods have finished.
-   Both are **optional** — if not defined, they’re skipped automatically.
-   They share the same `$builder` instance used by the engine, so any change persists through the filtering process.

---

#### 🪄 CLI Integration

When generating a new filter using the Artisan command:

```bash
php artisan make:filter ProductFilter
```

Both `initially()` and `finally()` methods are automatically added to the stub file, ready for customization.

---

#### 💡 Practical Use Cases

-   Use `initially()` to:

    -   Apply global constraints (`is_active = true`, `tenant_id = currentTenant()`).
    -   Add necessary joins or eager loads before filters run.

-   Use `finally()` to:

    -   Apply ordering or limits.
    -   Clean up relations or add post-filter transformations.
