<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix' => '',
        ],

        // _SLAVE

        'mysql' => [
            'driver' => 'mysql',
            'read' => [
                ['host' => env('DB_SLAVE_HOST', 'localhost')],
                ['host' => env('DB_HOST', 'localhost')],
                ['host' => env('DB_SLAVE_HOST', 'localhost')]
            ],
            'write' => [
                'host' => env('DB_HOST', 'localhost'),
            ],
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'port' => env('DB_PORT', 3306),
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'seec_user' => [
            'driver' => 'mysql',
            'read' => [
                ['host' => env('SEEC_USER_DB_SLAVE_HOST', 'localhost')],
                ['host' => env('SEEC_USER_DB_HOST', 'localhost')]
            ],
            'write' => [
                'host' => env('SEEC_USER_DB_HOST', 'localhost'),
            ],
            'database' => env('SEEC_USER_DB_DATABASE', 'forge'),
            'username' => env('SEEC_USER_DB_USERNAME', 'forge'),
            'password' => env('SEEC_USER_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'port' => env('SEEC_USER_DB_PORT', 3306),
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'seec_shop' => [
            'driver' => 'mysql',
            'read' => [
                ['host' => env('SEEC_SHOP_DB_HOST', 'localhost')],
                ['host' => env('SEEC_SHOP_DB_SLAVE_HOST', 'localhost')]
            ],
            'write' => [
                'host' => env('SEEC_SHOP_DB_HOST', 'localhost'),
            ],

            'database' => env('SEEC_SHOP_DB_DATABASE', 'forge'),
            'username' => env('SEEC_SHOP_DB_USERNAME', 'forge'),
            'password' => env('SEEC_SHOP_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'port' => env('SEEC_SHOP_DB_PORT', 3306),
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

        'message' => [
            'host' => env('REDIS_MESSAGE_HOST', 'localhost'),
            'password' => env('REDIS_MESSAGE_PASSWORD', null),
            'port' => env('REDIS_MESSAGE_PORT', 6379),
            'database' => 0,
        ],

        'ad' => [
            'host' => env('REDIS_AD_HOST', 'localhost'),
            'password' => env('REDIS_AD_PASSWORD', null),
            'port' => env('REDIS_AD_PORT', 6379),
            'database' => 0,
        ],

    ],

];
