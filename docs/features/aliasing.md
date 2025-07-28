# Filter Aliases

## Overview

Filter Aliases allow you to assign short or contextual names to filter classes, enabling reusable logic with cleaner naming in requests or controller logic.

### ⚙️ Setup

In your `config/filterable.php`, define aliases like so:

```php
'aliases' => Filterable::aliases([
    'active_users' => App\Filters\ActiveUserFilter::class,
    'vip' => App\Filters\VipUserFilter::class,
]),
```

### 🧪 Usage

```php
User::filter('active_users')->get();
```

The appropriate filter classes will be resolved and applied automatically.

### 💡 Benefits

-   Cleaner API interface.
-   Reusability of filter classes across contexts.
-   Improves frontend-backend consistency.

### ⚠️ Notes

-   Alias names must be unique.
-   If an alias and real filter share the same key, the real filter will be prioritized.
