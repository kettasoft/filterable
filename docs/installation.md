# 📦 Installation

To install **Filterable**, simply use Composer to add it to your project:

```bash
composer require kettasoft/filterable
```

### **Service Provider Registration**

For Laravel 5.5 and above, the service provider is automatically registered. For older versions, you'll need to register the service provider manually.

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
    | Default Filters Namespace.
    |--------------------------------------------------------------------------
    |
    | When using auto-discovery for filters (without manual injection) ,
    | this is the  namespace where your filter classes are located.
    |
    */
    'filter_namespace' => 'App\\Http\\Filters\\',

    /*
    |--------------------------------------------------------------------------
    | Automatically Register Filters
    |--------------------------------------------------------------------------
    | If enabled, the package will automatically resolve the filter class
    | based on the model name (e.g. Book => BookFilter).
    */
    'auto_register_filters' => false,

    /*
    |--------------------------------------------------------------------------
    | Auto Inject Request
    |--------------------------------------------------------------------------
    |
    | If true, the package will auto-inject the current request using the app container.
    | Set it to false if you want to manually inject the request in custom filters.
    |
    */
    'auto_inject_request' => true,

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
    | Default Filter Engine
    |--------------------------------------------------------------------------
    |
    | The filter engine that will be used by default when no engine is specified
    | explicitly. You can change it to any of the engines listed in the
    | "engines" section below.
    |
    */
    'default_engine' => 'dynamic',

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
        | Dynamic Methods Filter Engine
        |--------------------------------------------------------------------------
        |
        | The Dynamic Method Engine provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class.
        |
        */
        'dynamic' => [
            'description' => 'The Dynamic Method Engine provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class',
            'options' => [

                /*
                |--------------------------------------------------------------------------
                | Normalize Field Names
                |--------------------------------------------------------------------------
                |
                | Whether to automatically convert field names to lowercase
                | for consistency when parsing filters.
                |
                */
                'normalize_keys' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Tree Based Filter Engine
        |--------------------------------------------------------------------------
        |
        | This engine uses a tree-like structure to combine conditions using
        | logical operators (AND/OR). It's useful for building complex queries
        | with nested conditions.
        |
        */
        'tree' => [
            'description' => 'Logical tree structure using AND/OR to group nested conditions.',
            'options' => [

                /*
                |--------------------------------------------------------------------------
                | Default Logic Operator
                |--------------------------------------------------------------------------
                |
                | Determines how conditions are combined by default. Options:
                | "and" for intersection, "or" for union.
                |
                */
                'logic_operator' => 'and',

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
                | Tree Depth Limit
                |--------------------------------------------------------------------------
                |
                | Limits how deeply nested the filter tree can be. Set to null
                | to allow unlimited nesting.
                |
                */
                'depth_limit' => null,

                /*
                |--------------------------------------------------------------------------
                | Normalize Field Names
                |--------------------------------------------------------------------------
                |
                | Whether to automatically convert field names to lowercase
                | for consistency when parsing filters.
                |
                */
                'normalize_keys' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Rule Set Filter Engine
        |--------------------------------------------------------------------------
        |
        | A simple engine that applies a flat list of rules independently. This
        | is great when your filters are not deeply nested or hierarchical.
        |
        */
        'ruleset' => [
            'description' => 'Flat list of independent rules applied sequentially.',
            'options' => [

                /*
                |--------------------------------------------------------------------------
                | Strict Mode
                |--------------------------------------------------------------------------
                |
                | When enabled, if any rule fails, the entire filtering process
                | will stop and fail. Otherwise, it will continue with the rest.
                |
                */
                'strict_mode' => false,

                /*
                |--------------------------------------------------------------------------
                | Fail Silently
                |--------------------------------------------------------------------------
                |
                | If set to true, unsupported or invalid rules will be ignored
                | without throwing an error.
                |
                */
                'fail_silently' => true,

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
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Closure Pipeline Filter Engine
        |--------------------------------------------------------------------------
        |
        | Executes filters through a pipeline of closures. This gives you full
        | control over filter stages and behavior with middleware-like logic.
        |
        */
        'closure_pipeline' => [
            'description' => 'Filter execution as a sequence of Closures (pipeline style).',
            'options' => [

                /*
                |--------------------------------------------------------------------------
                | Middlewares
                |--------------------------------------------------------------------------
                |
                | An array of closure-based functions that are executed before
                | the filter logic. Useful for preprocessing or validation.
                |
                */
                'middlewares' => [],

                /*
                |--------------------------------------------------------------------------
                | Catch Exceptions
                |--------------------------------------------------------------------------
                |
                | Whether to catch and handle exceptions in each closure step
                | or let them bubble up.
                |
                */
                'catch_exceptions' => true,

                /*
                |--------------------------------------------------------------------------
                | Enable Logging
                |--------------------------------------------------------------------------
                |
                | Log each step and its outcome during filter execution.
                | Useful for debugging and tracking logic flow.
                |
                */
                'enable_logging' => false,
            ],
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
            'description' => 'Converts filters to raw SQL expressions for precision control.',
            'options' => [

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
                | Case-insensitive filtering
                |--------------------------------------------------------------------------
                |
                | Whether the 'like' operator should apply case-insensitive comparison by default.
                |
                */
                'case_insensitive_like' => true,

                /*
                |--------------------------------------------------------------------------
                | Quote Values
                |--------------------------------------------------------------------------
                |
                | Automatically wrap values in quotes during SQL generation.
                | Helps avoid syntax errors with string values.
                |
                */
                'quote_values' => true,

                /*
                |--------------------------------------------------------------------------
                | Expression Wrapper
                |--------------------------------------------------------------------------
                |
                | Format string used to wrap the final SQL expression.
                | For example: '(%s)' will wrap the entire condition in parentheses.
                |
                */
                'expression_wrapper' => '(%s)',

                /*
                |--------------------------------------------------------------------------
                | Throw on Invalid Filter
                |--------------------------------------------------------------------------
                | If true, the package will throw an exception if a field
                | is not allowed in the allowed fields.
                */
                'throw_on_invalid_fields' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum number of filterable fields allowed in a single request.
    |--------------------------------------------------------------------------
    |
    | This setting limits how many fields can be filtered simultaneously to:
    | - Prevent performance degradation from overly complex queries
    | - Mitigate potential DDoS attacks through filter bombing
    | - Maintain API stability and response times
    |
    | Accepted values:
    | - Positive integer (recommended 10-20 for most applications)
    | - 0 to disable limit (not recommended in production)
    |
    | When exceeded:
    | - Returns 422 Unprocessable Entity response
    | - Includes error message specifying the allowed limit
    |
    */
    'max_filterable_fields' => 15,

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
    | Define filters mapping.
    |--------------------------------------------------------------------------
    |
    | This is the namespace all you Eloquent Model Filters will reside
    |
    */
    'mapping' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Sanitizers
    |--------------------------------------------------------------------------
    |
    | Define sanitizers to apply to all incomming values before filtering.
    | You can enable/disable built-in sanitizers
    |
    */
    'global_sanitizers' => [
        'enable' => false,
        'defaults' => [
            'trim' => true,
            'strtolower' => true
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allow empty values
    |--------------------------------------------------------------------------
    |
    | If 'false' filters with null or empty string values will be ignored.
    |
    */
    'allow_empty_values' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Filters Behavior
    |--------------------------------------------------------------------------
    |
    | You can specify whether the default behavior when no filters are passed
    | should return all records or an empty query.
    |
    | Supported: "all", "none"
    |
    */
    'default_behavior' => 'all',

    /*
    |--------------------------------------------------------------------------
    | Throw on Invalid Filter
    |--------------------------------------------------------------------------
    | If true, the package will throw an exception if a requested filter
    | is not defined in the filters list.
    */
    'throw_on_invalid_filter' => false,

    /*
    |--------------------------------------------------------------------------
    | Log applied filters query.
    |--------------------------------------------------------------------------
    |
    | If true, all filters and their values will be logged queries using Laravel's logger.
    |
    */
    'log_queries' => false,

    /*
    |--------------------------------------------------------------------------
    | Path of saving new filters
    |--------------------------------------------------------------------------
    |
    | This is the namespace all you Eloquent Model Filters will reside
    |
    */
    'save_filters_at' => 'Http/Filters',

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
        'stub' => base_path('vendor/kettasoft/filterable/stubs/filter.stub'),
    ],

    'sanitizer' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Paginator Limit For `paginateFilter` and `simplePaginateFilter`
    |--------------------------------------------------------------------------
    |
    | Set paginate limit
    |
    */
    'paginate_limit' => null,

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

---

### **Step 2: Create a Custom Filter Class**

You can generate a custom filter class for your model by running the artisan command:

```bash
php artisan kettasoft:make-filter PostFilter --filters=title,status
```

This command will generate a filter class where you can define custom filter methods.

---
