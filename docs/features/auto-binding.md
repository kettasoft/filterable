# Auto Binding Filter Class in Models

You can now define a default filter class directly inside your model by setting the `$filterable` property.

This allows the `filter()` method to automatically resolve the corresponding filter class without having to explicitly pass it.

## ✨ Usage

Inside your Eloquent model, define a `$filterable` property and assign the class name of your filter:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kettasoft\Filterable\Traits\HasFilterable;

class User extends Model
{
    use HasFilterable;
    protected $filterable = \App\Http\Filters\UserFilter::class;
}
```

Then, when calling the filter() method, you no longer need to provide the filter class manually:

```php
$users = User::filter()->get();
```

The package will automatically resolve and use the UserFilter class defined in the model.

## 🧠 Notes

-   If no $filterable is set on the model, you will still need to pass the filter class manually to filter().
-   This feature works nicely with your existing filter engines and aliases.

## ✅ Benefits

-   Cleaner, shorter code.
-   Better encapsulation.
-   Each model manages its own filtering logic.
