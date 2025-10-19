# 📦 Installation

To install **Filterable**, simply use Composer to add it to your project:

```bash
composer require kettasoft/filterable
```

### **Service Provider Registration**

Add the following line to the **`providers`** array in **`config/app.php`**:

```php
'providers' => [

    ...

    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,

];
```

### **Publishing Configuration and Stubs**

After installation, you can publish the configuration file and stubs with the following commands:

```bash
php artisan vendor:publish --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" --tag="config"
php artisan vendor:publish --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" --tag="stubs"
```

These are the contents of the default config file that will be published:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Eloquent Filter Settings
    |--------------------------------------------------------------------------
    |
    | This is the namespace all you Eloquent Model Filters will reside
    |
    */
    'namespace' => 'App\\Http\\Filters',

    /*
    |--------------------------------------------------------------------------
    | Path of saving new filters
    |--------------------------------------------------------------------------
    |
    | This is the namespace all you Eloquent Model Filters will reside
    |
    */
    'save_filters_at' => app_path('Http/Filters'),

    /*
    |--------------------------------------------------------------------------
    | Default Request Key
    |--------------------------------------------------------------------------
    |
    | The query string key to look for filter inputs automatically from requests.
    | Example: /posts?filter[title]=test
    |
    */
    'filter_key' => 'filter',

    /*
    |--------------------------------------------------------------------------
    | Default Request Source.
    |--------------------------------------------------------------------------
    |
    | By default, filters will read query parameters from the request instance.
    | You can change the source here if you want to use another source (e.g. JSON body).
    | Options: 'query', 'input', 'json'
    |
    */
    'request_source' => 'query',

    /*
    |--------------------------------------------------------------------------
    | Filter Aliases
    |--------------------------------------------------------------------------
    |
    | Define short, human-friendly aliases for your filter classes.
    | These aliases allow you to reference filters using simple names
    | when building dynamic or automatic filter logic, instead of full class paths.
    |
    */
    'aliases' => Filterable::aliases([
        // 'users' => App\Http\Filters\UserFilter::class
    ]),

    /*
    |--------------------------------------------------------------------------
    | Default Filter Engine
    |--------------------------------------------------------------------------
    |
    | The filter engine that will be used by default when no engine is specified
    | explicitly. You can change it to any of the engines listed in the
    | "engines" section below.
    |
    */
    'default_engine' => 'invokable',

    /*
    |--------------------------------------------------------------------------
    | Filter Engines
    |--------------------------------------------------------------------------
    |
    | Define all available filter engines in your application. Each engine
    | contains its own options that control its behavior and logic.
    | You can create your own custom engines and register them here.
    |
    */
    'engines' => [
        /*
        |--------------------------------------------------------------------------
        | Invokable Filter Engine
        |--------------------------------------------------------------------------
        |
        | The Invokable Engine provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class.
        |
        */
        'invokable' => [
            /*
            |--------------------------------------------------------------------------
            | Egnore empty values
            |--------------------------------------------------------------------------
            |
            | If 'true' filters with null or empty string values will be ignored.
            |
            */
            'ignore_empty_values' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Tree Filter Engine
        |--------------------------------------------------------------------------
        |
        | This engine uses a tree-like structure to combine conditions using
        | logical operators (AND/OR). It's useful for building complex queries
        | with nested conditions.
        |
        */
        'tree' => [
            /*
            |--------------------------------------------------------------------------
            | Strict Mode
            |--------------------------------------------------------------------------
            |
            | When enabled, if any filter key is not allowed, the entire filtering process
            | will stop and throw exception. Otherwise, it will ignore unallowed filters.
            |
            */
            'strict' => true,

            /*
            |--------------------------------------------------------------------------
            | Allowed SQL Operators
            |--------------------------------------------------------------------------
            |
            | List of supported SQL operators you want to allow when parsing
            | the expressions.
            |
            */
            'allowed_operators' => [
                'eq' => '=',
                'neq' => '!=',
                'gt' => '>',
                'lt' => '<',
                'gte' => '>=',
                'lte' => '<=',
                'like' => 'like',
                'nlike' => 'not like',
                'in' => 'in',
                'nin' => 'not in',
                'null' => 'is null',
                'notnull' => 'is not null',
                'between' => 'between',
            ],

            /*
            |--------------------------------------------------------------------------
            | Default Operator
            |--------------------------------------------------------------------------
            |
            | Default operator when request dosen't has operator.
            |
            */
            'default_operator' => '=', // =

            /*
            |--------------------------------------------------------------------------
            | ignore empty values
            |--------------------------------------------------------------------------
            |
            | If 'true' filters with null or empty string values will be ignored.
            |
            */
            'ignore_empty_values' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Ruleset Filter Engine
        |--------------------------------------------------------------------------
        |
        | A simple engine that applies a simple queries independently. This
        | is great when your filters are not deeply nested or hierarchical.
        |
        */
        'ruleset' => [
            /*
            |--------------------------------------------------------------------------
            | Strict Mode
            |--------------------------------------------------------------------------
            |
            | When enabled, if any filter key is not allowed, the entire filtering process
            | will stop and throw exception. Otherwise, it will ignore unallowed filters.
            |
            */
            'strict' => true,

            /*
            |--------------------------------------------------------------------------
            | Allowed Fields
            |--------------------------------------------------------------------------
            |
            | Specify which fields are allowed to be filtered. Leave empty
            | to allow all fields.
            |
            */
            'allowed_fields' => [],

            /*
            |--------------------------------------------------------------------------
            | Allowed SQL Operators
            |--------------------------------------------------------------------------
            |
            | List of supported SQL operators you want to allow when parsing
            | the expressions.
            |
            */
            'allowed_operators' => [
                'eq' => '=',
                'neq' => '!=',
                'gt' => '>',
                'lt' => '<',
                'gte' => '>=',
                'lte' => '<=',
                'like' => 'like',
                'nlike' => 'not like',
                'in' => 'in',
                'nin' => 'not in',
                'null' => 'is null',
                'notnull' => 'is not null',
                'between' => 'between',
            ],

            /*
            |--------------------------------------------------------------------------
            | Default Operator
            |--------------------------------------------------------------------------
            |
            | Default operator when request dosen't has operator.
            |
            */
            'default_operator' => 'eq', // =
        ],

        /*
        |--------------------------------------------------------------------------
        | SQL Expression Filter Engine
        |--------------------------------------------------------------------------
        |
        | Converts filters into raw SQL expressions. Ideal when you need
        | fine-grained control over generated SQL queries.
        |
        */
        'expression' => [
            /*
            |--------------------------------------------------------------------------
            | ignore empty values
            |--------------------------------------------------------------------------
            |
            | If 'true' filters with null or empty string values will be ignored.
            |
            */
            'ignore_empty_values' => false,

            /*
            |--------------------------------------------------------------------------
            | Allowed SQL Operators
            |--------------------------------------------------------------------------
            |
            | List of supported SQL operators you want to allow when parsing
            | the expressions.
            |
            */
            'allowed_operators' => [
                'eq' => '=',
                'neq' => '!=',
                'gt' => '>',
                'lt' => '<',
                'gte' => '>=',
                'lte' => '<=',
                'like' => 'like',
                'nlike' => 'not like',
                'in' => 'in',
                'nin' => 'not in',
                'null' => 'is null',
                'notnull' => 'is not null',
                'between' => 'between',
            ],

            /*
            |--------------------------------------------------------------------------
            | Default Operator
            |--------------------------------------------------------------------------
            |
            | Default operator when request dosen't has operator.
            |
            */
            'default_operator' => 'eq',

            /*
            |--------------------------------------------------------------------------
            | Validate Columns
            |--------------------------------------------------------------------------
            |
            | Whether to check if a column exists in the schema before
            | building the SQL expression.
            |
            */
            'validate_columns' => true,

            /*
            |--------------------------------------------------------------------------
            | Allowed Fields
            |--------------------------------------------------------------------------
            |
            | Specify which fields are allowed to be filtered. Leave empty
            | to allow all fields.
            |
            */
            'allowed_fields' => [],

            /*
            |--------------------------------------------------------------------------
            | Strict Mode
            |--------------------------------------------------------------------------
            | If true, the package will throw an exception if a field
            | is not allowed in the allowed fields.
            */
            'strict' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom generator stub
    |--------------------------------------------------------------------------
    |
    | If you want to override the default stub this package provides
    | you can enter the path to your own at this point
    |
    */
    'generator' => [
        'stubs' => base_path('vendor/kettasoft/filterable/src/stubs/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Paginator Limit For `paginateFilter` and `simplePaginateFilter`
    |--------------------------------------------------------------------------
    |
    | Set paginate limit
    |
    */
    'paginate_limit' => env('PAGINATION_LIMIT_DEFAULT', 15),

    /*
    |--------------------------------------------------------------------------
    | Header Driven Filter Mode
    |--------------------------------------------------------------------------
    | Allows dynamically selecting the filter engine via HTTP headers.
    | When enabled, the package will check for the specified header and use
    | its value to determine which filter engine to apply for that request.
    |
    | This is useful when you need different filtering behavior for:
    | - Different client types (mobile/web)
    | - API versions
    | - Special request cases
    */
    'header_driven_mode' => [
        /*
        |--------------------------------------------------------------------------
        | Enable Header Driven Mode
        |--------------------------------------------------------------------------
        | When true, the package will check for the filter mode header
        | and attempt to use the specified engine if valid.
        |
        | Set to false to completely ignore the header.
        */
        'enabled' => false,

        /*
        |--------------------------------------------------------------------------
        | Filter Mode Header Name
        |--------------------------------------------------------------------------
        | The HTTP header name that will be checked for engine selection.
        |
        */
        'header_name' => 'X-Filter-Mode',

        /*
        |--------------------------------------------------------------------------
        | Available Engines Whitelist
        |--------------------------------------------------------------------------
        | List of engine names that can be specified in the header.
        | Empty array means all configured engines are allowed.
        |
        | Example: ['dynamic', 'tree'] would only allow these two engines
        | via header selection.
        */
        'allowed_engines' => [],

        /*
        |--------------------------------------------------------------------------
        | Engine Name Mapping
        |--------------------------------------------------------------------------
        | Maps header values to actual engine names.
        | Useful when you want to expose different names to clients.
        |
        | Example:
        | 'engine_map' => [
        |     'simple' => 'ruleset',
        |     'advanced' => 'dynamic',
        |     'full' => 'expression'
        | ]
        |
        | Header value 'simple' would use the 'ruleset' engine
        */
        'engine_map' => [],

        /*
        |--------------------------------------------------------------------------
        | Fallback Strategy
        |--------------------------------------------------------------------------
        | Determines behavior when an invalid engine is specified:
        |
        | 'default' - Silently falls back to default engine
        | 'error' - Returns 400 Bad Request response
        |
        | Note: Always validates against configured engines in 'engines' section.
        */
        'fallback_strategy' => 'default',
    ],
];


```

---

### **Step 1: Add the `Filterable` Trait to Your Model**

To enable filtering on your model, you need to include the `Filterable` trait in the model you want to apply filters on.

```php
<?php

use Kettasoft\Filterable\Filterable;

class Post extends Model
{
    use Filterable;
}
```

---

### **Step 2: Create a Custom Filter Class**

You can generate a custom filter class for your model by running the artisan command:

```bash
php artisan kettasoft:make-filter PostFilter --filters=title,status
```

This command will generate a filter class where you can define custom filter methods.

---
