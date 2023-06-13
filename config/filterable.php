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
