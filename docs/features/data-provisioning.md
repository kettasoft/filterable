# Data Provisioning

> 🚀 Introduced in **v2.7.0**

The **Data Provisioning** feature allows you to share and access contextual data across all `Filterable` instances.  
It provides a simple way to feed global data — such as the authenticated user, current environment, or request context — into the filtering system.

## Overview

By default, every `Filterable` instance is isolated.  
However, sometimes filters need to depend on shared context (e.g., the current user or request data).

Using the **Data Provisioning** feature, you can inject this context globally so it’s available in all filters.

---

## Usage

### 1. Providing Shared Data

To provide shared data to all `Filterable` instances, you can use the `Filterable::provide()` method. This method accepts an array of **key-value** pairs that represent the data you want to share.

```php
use Kettasoft\Filterable\Filterable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Filterable::provide([
            "user" => auth()->user(),
            "environment" => app()->environment(),
        ]);
    }
}
```

### 2. Accessing Provided Data

Once you have provided shared data, you can access it from any `Filterable` instance using the `provided()` method.

```php
$filterable = Filterable::create();

$user = $filterable->provided("user");
$environment = $filterable->provided("environment");
```

If your filters depend on the current user’s role or permissions,
they can directly access it via `$this->provided('user')`.

### 3. Checking for Provided Keys

You can check if a specific key has been provided using the `hasProvided()` method.

```php
$filterable = Filterable::create();

if ($filterable->hasProvided("user")) {
    // Do something with the user
}
```

If the key exists, you can safely retrieve its value using the `provided()` method.

---

## Methods Reference

| Method                          | Description                                | Example                                     |
| ------------------------------- | ------------------------------------------ | ------------------------------------------- |
| `provide(array $data)`          | Feed data into the filterable context.     | `$filterable->provide(['key' => 'value']);` |
| `provided(?string $key = null)` | Retrieve one or all provided data items.   | `$filterable->provided('user');`            |
| `hasProvided(string $key)`      | Check if a specific key has been provided. | `$filterable->hasProvided('user');`         |

---

## Benefits

-   Eliminates repetitive dependency passing.
-   Improves filter flexibility and reusability.
-   Makes context-aware filtering simpler and cleaner.
-   Fully backward-compatible.

---

## Conclusion

The **Data Provisioning** feature provides a powerful yet simple way to manage shared context across your filtering logic.  
By centralizing data like the authenticated user, environment details, or request information, you keep your filters clean, decoupled, and easy to test.

Use this feature whenever your filters depend on external context — it ensures your filtering system remains **flexible, expressive, and developer-friendly.**
