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
    'namespace' => 'App\\Http\\Filters\\',

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
    | Default Filter Engine
    |--------------------------------------------------------------------------
    |
    | The filter engine that will be used by default when no engine is specified
    | explicitly. You can change it to any of the engines listed in the
    | "engines" section below.
    |
    */
    'default_engine' => 'invokeable',

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
        | Invokeable Filter Engine
        |--------------------------------------------------------------------------
        |
        | The Invokeable Engine provides a powerful way to dynamically map incomming reuqest parameters to corresponding methods in a filter class.
        |
        */
        'invokeable' => [
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
        'tree' => [],

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
        'expression' => []
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
        'stub' => base_path('vendor/kettasoft/filterable/src/stubs/filter.stub'),
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
