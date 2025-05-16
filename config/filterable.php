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
        'invokeable' => [],

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
        'ruleset' => [],

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
    'paginate_limit' => env('PAGINATION_LIMIT_DEFAULT', 15)
];
