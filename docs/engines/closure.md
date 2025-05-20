# **Closure-Based Filter Engine**

The **Closure Pipeline Filter Engine** allows you to apply dynamic query filters to Eloquent models using closures. This approach provides a flexible, clean, and testable way to filter data based on request input or custom conditions.

::: danger Important Node
The **Closure Pipeline Engine** dose **not** og through any [sanitization](sanitization) or [validation](validation) processes.
Ensuer that you manually handle input validation and security checks when using closures in filter logic to avoid unexpected behavior or security vulnerabilities.
:::

### Basic Usage

You can call the `filter()` method on an Eloquent model and pass an array of closures keyed by filter names:

```php
User::filter([
    'id' => fn ($query) => $query->where('id', request('id')),
    'status' => fn ($query) => $query->where('status', request('status')),
])->get();
```

Each closure receives the query builder instance and applies a filter condition. This pattern is particularly powerful when combined with conditional logic.

---

### How It Works

1. The engine accepts an array of closures.
2. It loops through the defined keys.
3. If the request contains a matching key, the corresponding closure is executed.
4. Closures can access any external scope, including request() or service dependencies.

### Conditional Filters

You can make filters conditional by checking the input inside the closure:

```php
'name' => fn ($query) => request()->filled('name')
    ? $query->where('name', 'like', '%' . request('name') . '%')
    : $query,
```

---

### Composable Filters

You can extract filters to named closures or dedicated classes for reuse:

```php
$filters = [
    'email' => EmailFilter::make(),
    'active' => fn ($query) => $query->where('active', true),
];

User::filter($filters)->get();
```

Where `EmailFilter::make()` returns a closure:

```php
class EmailFilter
{
    public static function make()
    {
        return fn ($query) => $query->where('email', request('email'));
    }
}

```

---

### Full Example

```php
$filters = [
    'id' => fn ($q) => $q->where('id', request('id')),
    'email' => fn ($q) => $q->where('email', 'like', '%' . request('email') . '%'),
    'created_from' => fn ($q) => $q->whereDate('created_at', '>=', request('created_from')),
    'created_to' => fn ($q) => $q->whereDate('created_at', '<=', request('created_to')),
];

$users = User::filter($filters)->paginate(15);
```

---

### Notes

- Keys in the filter array should correspond to expected request inputs.
- You can combine this engine with query macro or trait to apply it globally.
- Works seamlessly with Laravel pagination, sorting, and scopes.

---
