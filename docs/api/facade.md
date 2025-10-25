# Filterable Facade

The Filterable package provides a facade for easier access to the Filterable functionality. The facade allows you to use the Filterable methods statically without needing to instantiate the class.

## Installation

### 1. Register the Facade

Add the facade alias to your `config/app.php` file in the `aliases` array:

```php
'aliases' => [
    // ... other aliases
    'Filterable' => Kettasoft\Filterable\Facades\Filterable::class,
],
```

### 2. Service Provider Registration

The `FilterableServiceProvider` is already configured to register the necessary bindings for the facade. Make sure it's registered in your `config/app.php`:

```php
'providers' => [
    // ... other providers
    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,
],
```

## Usage

Once the facade is registered, you can use all Filterable methods statically:

### Basic Usage

```php
use Filterable;

// Create a new filterable instance
$filterable = Filterable::create();

// Apply filters to a query builder
$results = Filterable::create()
    ->setModel(User::class)
    ->apply($query);

// Use with custom request
$filterable = Filterable::withRequest($customRequest);
```

### Configuration Methods

```php
// Set allowed fields
Filterable::create()->setAllowedFields(['name', 'email', 'created_at']);

// Set model
Filterable::create()->setModel(User::class);

// Enable strict mode
Filterable::create()->strict();

// Set allowed operators
Filterable::create()->allowdOperators(['=', '!=', 'like', 'in']);
```

### Data and Request Management

```php
// Set custom data
Filterable::create()->setData(['name' => 'John', 'email' => 'john@example.com']);

// Set request source
Filterable::create()->setSource('json'); // 'query', 'input', or 'json'

// Get current data
$data = Filterable::create()->getData();
```

### Conditional Logic

```php
// Apply conditions
Filterable::create()
    ->when($isAdmin, function ($filterable) {
        return $filterable->setAllowedFields(['*']);
    })
    ->when($isGuest, function ($filterable) {
        return $filterable->strict();
    });
```

### Pipeline Processing

```php
// Use custom pipes
Filterable::create()->through([
    function ($builder, $filterable) {
        return $builder->where('active', true);
    },
    function ($builder, $filterable) {
        return $builder->orderBy('created_at', 'desc');
    }
]);
```

### Sorting

```php
// Add sorting for specific filterable classes
Filterable::addSorting(UserFilter::class, function ($sorter, $request) {
    return $sorter->sort('name')->sort('created_at', 'desc');
});

// Define sorting for current instance
Filterable::create()->sorting(function ($sorter) {
    return $sorter->sort('name')->sort('email');
});
```

### Sanitization

```php
// Set sanitizers
Filterable::create()->setSanitizers([
    TrimSanitizer::class,
    StripTagsSanitizer::class
]);

// Disable sanitizers
Filterable::create()->withoutSanitizers();
```

### Engine Configuration

```php
// Use specific engine
Filterable::create()->useEngine('expression'); // or 'tree', 'ruleset', etc.

// Enable header-driven mode
Filterable::create()->withHeaderDrivenMode([
    'header_name' => 'X-Filter-Engine',
    'default' => 'expression'
]);
```

### SQL Export

```php
// Get SQL representation
$sql = Filterable::create()
    ->setModel(User::class)
    ->toSql();

// Get SQL with bindings
$sqlWithBindings = Filterable::create()
    ->setModel(User::class)
    ->toSql(null, true);
```

## Available Methods

The facade provides access to all public methods of the Filterable class, organized into the following categories:

### Static Factory Methods

-   `create()` - Create new Filterable instance
-   `withRequest()` - Create new Filterable instance with custom Request

### Core Filtering Methods

-   `apply()` - Apply all filters
-   `filter()` - Alias for apply method
-   `getResources()` - Get Resources instance
-   `settings()` - Get FilterableSettings instance

### Model Configuration

-   `setModel()` - Set model
-   `getModel()` - Get model
-   `getModelInstance()` - Get model instance object

### Field & Operator Configuration

-   `setAllowedFields()` - Define allowed fields for filtering
-   `getAllowedFields()` - Get allowed fields
-   `allowdOperators()` - Set allowed operators
-   `getAllowedOperators()` - Get allowed operators

### Mode Configuration

-   `strict()` - Enable strict mode
-   `permissive()` - Enable permissive mode
-   `isStrict()` - Check if filter has strict mode

### Request & Data Management

-   `setData()` - Set manual data injection
-   `getData()` - Get current data
-   `setSource()` - Set request source
-   `get()` - Retrieve input item from request

And many more methods for advanced configuration and customization.

## Examples

### Complete Example

```php
use Filterable;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = Filterable::create()
            ->setModel(User::class)
            ->setAllowedFields(['name', 'email', 'created_at'])
            ->allowdOperators(['=', '!=', 'like', 'in', 'between'])
            ->strict()
            ->ignoreEmptyValues()
            ->when($request->user()->isAdmin(), function ($filterable) {
                return $filterable->setAllowedFields(['*']);
            })
            ->apply(User::query())
            ->paginate();

        return response()->json($users);
    }
}
```

This facade provides a clean, expressive API for working with the Filterable package while maintaining all the functionality of the underlying class.
