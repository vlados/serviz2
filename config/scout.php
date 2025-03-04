<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => false,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the records later.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'index-settings' => [
            // 'users' => [
            //     'searchableAttributes' => ['id', 'name', 'email'],
            //     'attributesForFaceting'=> ['filterOnly(email)'],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://www.meilisearch.com/docs/learn/configuration/instance_options#all-instance-options
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            // 'users' => [
            //     'filterableAttributes'=> ['id', 'name', 'email'],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Typesense Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Typesense settings. Typesense is an open
    | source search engine using minimal configuration. Below, you will
    | state the host, key, and schema configuration for the instance.
    |
    */

    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'nearest_node' => [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'path' => env('TYPESENSE_PATH', ''),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
            'healthcheck_interval_seconds' => env('TYPESENSE_HEALTHCHECK_INTERVAL_SECONDS', 30),
            'num_retries' => env('TYPESENSE_NUM_RETRIES', 3),
            'retry_interval_seconds' => env('TYPESENSE_RETRY_INTERVAL_SECONDS', 1),
        ],
        // 'max_total_results' => env('TYPESENSE_MAX_TOTAL_RESULTS', 1000),
        'model-settings' => [
            \App\Models\ServiceOrder::class => [
                'collection-schema' => [
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'facet' => false,
                        ],
                        [
                            'name' => 'order_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'customer_name',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'customer_name_latin',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'customer_name_bg',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'customer_phone',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'scooter_model',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'scooter_serial_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'problem_description',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'problem_description_latin',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'problem_description_bg',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'work_performed',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'work_performed_latin',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'work_performed_bg',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'status',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'payment_status',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'price',
                            'type' => 'float',
                        ],
                        [
                            'name' => 'created_at',
                            'type' => 'int64',
                        ],
                    ],
                    'default_sorting_field' => 'created_at',
                    'symbols_to_index' => ['*'],
                    'token_separators' => [' ', '-', '_'],
                ],
                'search-parameters' => [
                    'query_by' => 'order_number,customer_name,customer_name_latin,customer_name_bg,customer_phone,scooter_model,scooter_serial_number,problem_description,problem_description_latin,problem_description_bg,work_performed,work_performed_latin,work_performed_bg,status,payment_status',
                    'prefix' => true,
                    'infix' => true,
                    'typo_tokens_threshold' => 1
                ],
            ],
            \App\Models\Customer::class => [
                'collection-schema' => [
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'facet' => false,
                        ],
                        [
                            'name' => 'name',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'name_latin',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'name_bg',
                            'type' => 'string',
                            'infix' => true,
                        ],
                        [
                            'name' => 'phone',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'email',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'address',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'notes',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'scooters_count',
                            'type' => 'int32',
                        ],
                        [
                            'name' => 'service_orders_count',
                            'type' => 'int32',
                        ],
                        [
                            'name' => 'created_at',
                            'type' => 'int64',
                        ],
                    ],
                    'default_sorting_field' => 'created_at',
                    'symbols_to_index' => ['*'],
                    'token_separators' => [' ', '-', '_'],
                ],
                'search-parameters' => [
                    'query_by' => 'name,name_latin,name_bg,phone,email,address,notes',
                    'prefix' => true,
                    'infix' => true,
                    'typo_tokens_threshold' => 1
                ],
            ],
            \App\Models\Scooter::class => [
                'collection-schema' => [
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'facet' => false,
                        ],
                        [
                            'name' => 'model',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'serial_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'status',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'customer_name',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'max_speed',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'battery_capacity',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'weight',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'created_at',
                            'type' => 'int64',
                        ],
                    ],
                    'default_sorting_field' => 'created_at',
                ],
                'search-parameters' => [
                    'query_by' => 'model,serial_number,status,customer_name,max_speed,battery_capacity,weight',
                    'enable_transliteration' => true
                ],
            ],
            \App\Models\SparePart::class => [
                'collection-schema' => [
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'facet' => false,
                        ],
                        [
                            'name' => 'name',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'part_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'description',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'stock_quantity',
                            'type' => 'int32',
                        ],
                        [
                            'name' => 'purchase_price',
                            'type' => 'float',
                        ],
                        [
                            'name' => 'selling_price',
                            'type' => 'float',
                        ],
                        [
                            'name' => 'is_active',
                            'type' => 'bool',
                        ],
                        [
                            'name' => 'created_at',
                            'type' => 'int64',
                        ],
                    ],
                    'default_sorting_field' => 'created_at',
                ],
                'search-parameters' => [
                    'query_by' => 'name,part_number,description',
                    'enable_transliteration' => true
                ],
            ],
            \App\Models\Payment::class => [
                'collection-schema' => [
                    'fields' => [
                        [
                            'name' => 'id',
                            'type' => 'string',
                            'facet' => false,
                        ],
                        [
                            'name' => 'amount',
                            'type' => 'float',
                        ],
                        [
                            'name' => 'payment_method',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'reference_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'notes',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'service_order_number',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'customer_name',
                            'type' => 'string',
                        ],
                        [
                            'name' => 'payment_date',
                            'type' => 'int64',
                        ],
                        [
                            'name' => 'created_at',
                            'type' => 'int64',
                        ],
                    ],
                    'default_sorting_field' => 'created_at',
                ],
                'search-parameters' => [
                    'query_by' => 'payment_method,reference_number,notes,service_order_number,customer_name',
                    'enable_transliteration' => true
                ],
            ],
        ],
    ],

];
