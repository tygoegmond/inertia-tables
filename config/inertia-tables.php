<?php

// config for Egmond/InertiaTables
return [
    /*
    |--------------------------------------------------------------------------
    | Default Pagination
    |--------------------------------------------------------------------------
    |
    | This value controls the default number of items per page for tables.
    | You can override this on a per-table basis using the paginate() method.
    |
    */
    'default_per_page' => 25,

    /*
    |--------------------------------------------------------------------------
    | Maximum Per Page
    |--------------------------------------------------------------------------
    |
    | This value controls the maximum number of items that can be displayed
    | per page. This prevents performance issues with very large result sets.
    |
    */
    'max_per_page' => 100,

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for global search functionality.
    |
    */
    'search' => [
        'min_length' => 2,
        'placeholder' => 'Search...',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching table configurations and results.
    |
    */
    'cache' => [
        'enabled' => false,
        'ttl' => 3600, // 1 hour
        'prefix' => 'inertia_tables',
    ],
];
