# Authorization

The filter engine includes a lightweight authorization mechanism that determines whether a filter should be executed or not, based on custom logic defined by the developer.

## How It Works

To authorize a filter before it's applied, you can define a method named `authorize` inside the closure object (if you're using a class-based closure or invokable object). This method should return a boolean:

- If authorize() returns true, the filter will be applied normally.
- If authorize() returns false, the engine will not apply the filter and will throw an `AuthorizationException`.

This allows you to restrict filter usage based on roles, permissions, user context, etc.

## Example

```php
use Illuminate\Auth\Access\AuthorizationException;

class AdminOnlyFilter
{
  protected $filters = ['name'];

  public function name($value)
  {
    return $this->builder->where('name', $value);
  }

  public function authorize(): bool
  {
    return auth()->user()?->isSuperAdmin() ?? false;
  }
}
```

**Usage:**

```php
$users = User::filter(AdminOnlyFilter::class)->get();
```

If the logged-in user is not a super admin, the filter will not be executed and an exception will be thrown.

## Handling Authorization Failures.

By default, if authorize() returns false, the engine throws a **`FilterAuthorizationException`**. You can catch it and customize its behavior globally or per request.

**Example**:

```php
try {
  $users = User::filter(AdminOnlyFilter::class)->get();
} catch(FilterAuthorizationException $e) {
  abort(403, 'You are not authorized to use this filter.');
}
```

## Use Cases

- Role-based access control (RBAC).
- Conditional filtering for admins only.
- Hiding sensitive filters from regular users.
- Protecting business-critical query operations.
