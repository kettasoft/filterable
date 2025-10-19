<?php

use Kettasoft\Filterable\Filterable;

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
    | Filter Profiles
    |--------------------------------------------------------------------------
    | Configure filter profiles for different contexts or models.
    | Each profile maps to a class that implements the FilterableProfile interface.
    | Profiles allow you to encapsulate complex filtering logic and reuse it
    | across different parts of your application.
    */
    'profiles' => [
        // 'users' => 'App\Http\Filters\Users\Profiles\UserFilterProfile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    |
    | Configure the sorting behavior for filterable queries.
    | You can control which fields are allowed, define default sorting,
    | set aliases (presets), and customize how multiple sorts are handled.
    |
    */
    'sorting' => [

        /*
        |--------------------------------------------------------------------------
        | Default Request Key
        |--------------------------------------------------------------------------
        |
        | The query string key to look for filter inputs automatically from requests.
        | Example: /posts?sort=-created_at,name
        |
        */
        'sort_key' => 'sort',

        /*
        |--------------------------------------------------------------------------
        | Allowed Fields
        |--------------------------------------------------------------------------
        |
        | Define which fields are allowed for sorting.
        | Example: ['id', 'name', 'created_at']
        |
        */
        'allowed' => [],

        /*
        |--------------------------------------------------------------------------
        | Default Sorting
        |--------------------------------------------------------------------------
        |
        | Define a default sorting order if none is provided by the request.
        | Format: ['field', 'direction']
        | Example: ['created_at', 'desc']
        |
        */
        'default' => null,

        /*
        |--------------------------------------------------------------------------
        | Aliases
        |--------------------------------------------------------------------------
        |
        | Define shortcuts (aliases) for common sorting orders.
        | Example:
        | 'aliases' => [
        |     'recent' => [['created_at', 'desc']],
        |     'popular' => [['views', 'desc'], ['likes', 'desc']],
        | ],
        |
        */
        'aliases' => [],

        /*
        |--------------------------------------------------------------------------
        | Multi-Sorting
        |--------------------------------------------------------------------------
        |
        | Enable or disable multiple sorting fields in the same request.
        |
        */
        'multi_sort' => true,

        /*
        |--------------------------------------------------------------------------
        | Delimiter
        |--------------------------------------------------------------------------
        |
        | The delimiter used to separate multiple sorting fields in a request.
        | Example: ?sort=name,-created_at
        | With delimiter = ',' → "name,-created_at"
        |
        */
        'delimiter' => ',',

        /*
        |--------------------------------------------------------------------------
        | Direction Map
        |--------------------------------------------------------------------------
        |
        | Define how sorting directions are interpreted.
        | Example:
        | '-' prefix means descending, no prefix = ascending.
        |
        */
        'direction_map' => [
            'asc' => 'asc',
            'desc' => 'desc',
            'prefix' => '-', // "-field" = desc
        ],

        /*
        |--------------------------------------------------------------------------
        | Nulls Position
        |--------------------------------------------------------------------------
        |
        | Decide how to handle NULL values in sorting.
        | Supported: 'first', 'last', or null (database default).
        |
        */
        'nulls_position' => null,
    ],

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

            /*
            |--------------------------------------------------------------------------
            | Normalize Field Names
            |--------------------------------------------------------------------------
            |
            | Whether to automatically convert field names to lowercase
            | for consistency when parsing filters.
            |
            */
            'normalize_keys' => false,
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
            'default_operator' => 'eq', // =

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
            | Normalize Field Names
            |--------------------------------------------------------------------------
            |
            | Whether to automatically convert field names to lowercase
            | for consistency when parsing filters.
            |
            */
            'normalize_keys' => false,
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
            | Egnore empty values
            |--------------------------------------------------------------------------
            |
            | If 'true' filters with null or empty string values will be ignored.
            |
            */
            'ignore_empty_values' => false,

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

            /*
            |--------------------------------------------------------------------------
            | Normalize Field Names
            |--------------------------------------------------------------------------
            |
            | Whether to automatically convert field names to lowercase
            | for consistency when parsing filters.
            |
            */
            'normalize_keys' => false,
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
            'strict' => true,

            /*
            |--------------------------------------------------------------------------
            | Normalize Field Names
            |--------------------------------------------------------------------------
            |
            | Whether to automatically convert field names to lowercase
            | for consistency when parsing filters.
            |
            */
            'normalize_keys' => false,
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

    'profiler' => [

        /*
        |--------------------------------------------------------------------------
        | Enable or Disable Query Profiler
        |--------------------------------------------------------------------------
        |
        | This option allows you to enable or disable the query profiler system.
        | You may want to disable it in production or when running background jobs.
        |
        */
        'enabled' => env('FILTERABLE_PROFILER_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Log Queries or Store in Database
        |--------------------------------------------------------------------------
        |
        | Determines how query profiling data will be stored.
        | Supported: "log", "database", "none"
        |
        */
        'store' => env('FILTERABLE_PROFILER_STORE', 'log'),

        /*
        |--------------------------------------------------------------------------
        | Minimum Execution Time Threshold (ms)
        |--------------------------------------------------------------------------
        |
        | Queries that execute faster than this threshold will not be stored.
        | This helps avoid logging trivial queries.
        |
        */
        'slow_query_threshold' => env('FILTERABLE_PROFILER_MIN_TIME', 1.0),

        /*
        |--------------------------------------------------------------------------
        | Sampling Percentage
        |--------------------------------------------------------------------------
        |
        | To reduce overhead, you can profile only X% of the requests randomly.
        | For example, 10 means 10% of calls will be stored.
        |
        */
        'sampling' => env('FILTERABLE_PROFILER_SAMPLING', 100),

        /*
        |--------------------------------------------------------------------------
        | Database Table Name
        |--------------------------------------------------------------------------
        |
        | If using "database" as a storage method, this is the table where
        | query profiles will be stored.
        |
        */
        'table' => 'query_profiles',

        /*
        |--------------------------------------------------------------------------
        | Log Channel
        |--------------------------------------------------------------------------
        |
        | If using "log" as a storage method, this is the log channel used.
        |
        */
        'log_channel' => env('FILTERABLE_PROFILER_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event System
    |--------------------------------------------------------------------------
    |
    | Configure the filterable event system, which allows you to listen to
    | lifecycle events during filtering operations.
    |
    */
    'events' => [

        /*
        |--------------------------------------------------------------------------
        | Enable or Disable Event System
        |--------------------------------------------------------------------------
        |
        | This option allows you to enable or disable the event system globally.
        | When disabled, no event listeners or observers will be triggered,
        | which can improve performance if you don't need event functionality.
        |
        | Available events:
        | - filterable.initializing: When a new Filterable instance is created
        | - filterable.resolved: After resolving engine and request data
        | - filterable.applied: After filters are executed successfully
        | - filterable.failed: If any exception occurs during apply
        | - filterable.finished: At the end of filtering lifecycle
        |
        */
        'enabled' => env('FILTERABLE_EVENTS_ENABLED', true),
    ],
];
