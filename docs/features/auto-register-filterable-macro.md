# Auto Register Filterable Macro

## Overview

By default, to use the **`filter()`** macro on Eloquent models, you must use the HasFilterable trait in each model. However, if you prefer to automatically register the **`filter()`** method on all Eloquent builders without modifying individual models, you can use the **`AutoRegisterFilterableServiceProvider`**.

### ✅ How to Use

1. Register the Service Provider
   Open your `config/app.php` and manually register the service provider:

```php
'providers' => [
    // Other service providers...
    Kettasoft\Filterable\Providers\AutoRegisterFilterableServiceProvider::class,
],
```

2. Remove the Trait (Optional)
   You can now remove the HasFilterable trait from your Eloquent models:

```php
class User extends Model
{
    // No need for HasFilterable trait
}
```

3. Use the Filter Method
   Use the `filter()` macro like this, even without using the trait:

```php
$users = User::filter(UserFilter::class)->get();
```

Or if the model defines the $filterable property:

```php
class User extends Model
{
    protected $filterable = \App\Filters\UserFilter::class;
}

$users = User::filter()->get();
```

### ⚠️ When Should You Use It?

-   ✅ You want zero setup per model.
-   ✅ You prefer centralized control over all query filtering.
-   ❌ You want explicit opt-in per model using HasFilterable.
