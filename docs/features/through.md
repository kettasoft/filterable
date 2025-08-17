# Apply Custom Query Callbacks using through()

## Overview

The `through()` method allows you to apply an array of custom query callbacks to the Eloquent builder instance within the `Filterable` class.

This gives you a powerful way to manipulate queries using closures (similar to pipelines), before or after applying filters — enabling advanced use cases such as chaining global conditions, joins, or even reordering logic externally.

### 🧪 Usage

```php
use Kettasoft\Filterable\Filterable;
use App\Models\Post;

$filter = Filterable::create()->setBuilder(Post::query());

$results = $filter->through([
    fn ($builder) => $builder->where('status', 'published'),
    fn ($builder) => $builder->orderByDesc('created_at'),
]);

$posts = $results->apply()->get();
```

You can also chain with other Filterable methods:

```php
$results = Filterable::create()
    ->setBuilder(Post::query())
    ->through([
        fn ($builder) => $builder->where('is_active', true),
    ])
    ->apply();
```

### ⚠️ Notes

-   Every item in the array passed to through() must be a valid callable.
-   If a non-callable value is passed, an InvalidArgumentException will be thrown.
-   Callbacks receive the query builder as the only argument and must return the modified builder.
-   This method is chainable and returns the Filterable instance.

### 💡 Benefits

-   🔄 Adds a flexible, composable way to extend queries externally.
-   🧪 Great for injecting reusable query logic without modifying filters.
-   🚫 Prevents tight coupling between filter logic and query logic.
-   ✅ Compatible with both eager and late filter application (apply()).
-   🧱 Clean separation of filtering rules and additional query logic.
