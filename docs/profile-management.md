# Profile Management

The Filter Profiles feature allows you to dynamically apply preconfigured filtering behaviors or settings based on context — such as user role, environment, or custom conditions.
Profiles act as reusable configurations for your filters, improving maintainability and flexibility.

## Introduction

A **Filterable** Profile defines how a `Filterable` instance should behave in a specific context.

Instead of writing conditional logic inside filters, you can encapsulate environment- or role-specific settings into separate, reusable profiles.

### Example:

You can have different filtering rules for:

-   Admin users (see all data)
-   Regular users (see limited data)
-   Guest users (see only public data)
-   Managers (department-specific filters)

## Defining Profiles

Profiles can be defined as:

-   A dedicated class implementing `FilterableProfile` interface
-   A callable function
-   A string reference from your config file

### Example: Using a Profile Class

```php
use Kettasoft\Filterable\Contracts\FilterableProfile;
use Kettasoft\Filterable\Filterable;

class AdminProfile implements FilterableProfile
{
    public function __invoke(Filterable $filterable): void
    {
        return $filterable
            ->allowedFilters(['*']) // Allow all filters
            ->strict(); // Enforce strict filtering
    }
}
```

## Using Profiles

Apply a profile directly to a filter instance:

```php
PostFilter::create()
    ->useProfile(AdminProfile::class)
    ->apply($builder)
    ->get();
```

Or use a callable:

```php
PostFilter::create()
    ->useProfile(function (Filterable $filterable) {
        return $filterable
            ->allowedFilters(['status', 'category'])
            ->defaultSort('created_at');
    })
    ->apply($builder)
    ->get();
```

Or use a profile key registered in your configuration:

```php
// config/filterable.php
'profiles' => [
    'admin' => App\FilterProfiles\AdminProfile::class,
    'user' => App\FilterProfiles\UserProfile::class,
],
```

Then apply it:

```php
PostFilter::create()
    ->useProfile('admin')
    ->apply($builder)
    ->get();
```

## Configuration

In your `config/filterable.php`:

```php
'profiles' => [
    'admin' => App\FilterProfiles\AdminProfile::class,
    'user'  => App\FilterProfiles\UserProfile::class,
    'guest' => fn($filterable) => $filterable->strict(),
],
```

Each entry can be:

-   A class name implementing `FilterableProfile`
-   A callable function
-   A class name string

## Use Cases

-   **Role-Based Filtering**: Different profiles for different user roles.
-   **Environment-Specific Behavior**: Different profiles for development, staging, and production.

### 1. Role-based Filtering

```php
$user = auth()->user();

$filter = PostFilter::create()
    ->useProfile(match ($user->role) {
        'admin' => 'admin',
        'manager' => 'manager',
        default => 'user',
    })
    ->apply($builder);
```

### 2. Environment-specific Behavior

```php
$environment = app()->environment('production');
PostFilter::create()
    ->useProfile($environment ? 'production' : 'debug')
    ->get();
```

## API Reference

### `useProfile(FilterableProfile|callable|string $profile): static`

Apply a filter profile to the current filterable instance.

-   **Parameters**:
    -   `FilterableProfile|callable|string $profile`: The profile to apply.
-   **Returns**: `static` - The current filterable instance.

Example:

```php
PostFilter::create()->useProfile(AdminProfile::class)->apply($builder)->get();
```

## Conclusion

Filter Profiles enhance the flexibility and maintainability of your filtering logic by encapsulating context-specific behaviors into reusable configurations. By leveraging profiles, you can easily adapt filtering rules based on user roles, environments, or other conditions without cluttering your filter implementations.
