<?php

if ( !empty( env( 'DB_CONNECTION' ) ) && !empty( env( 'DB_HOST' ) ) && !empty( env( 'DB_PORT' ) ) && !empty( env( 'DB_DATABASE' ) ) && !empty( env( 'DB_USERNAME' ) ) && !empty( env( 'DB_PASSWORD' ) ) && !empty( env( 'DB_ALT_CONNECTION' ) ) && !empty( env( 'DB_ALT_HOST' ) ) && !empty( env( 'DB_ALT_PORT' ) ) && !empty( env( 'DB_ALT_DATABASE' ) ) && !empty( env( 'DB_ALT_USERNAME' ) ) && !empty( env( 'DB_ALT_PASSWORD' ) ) )  {

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

      'default' => env( 'DB_DATABASE' ),

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

        env( 'DB_DATABASE' ) => [
                'driver'    => env('DB_CONNECTION'),
                'host'      => env('DB_HOST'),
                'port'      => env('DB_PORT'),
                'database'  => env('DB_DATABASE'),
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'strict'    => false
            ],
            env( 'DB_ALT_DATABASE' ) => [
                'driver'    => env('DB_ALT_CONNECTION'),
                'host'      => env('DB_ALT_HOST'),
                'port'      => env('DB_ALT_PORT'),
                'database'  => env('DB_ALT_DATABASE'),
                'username'  => env('DB_ALT_USERNAME'),
                'password'  => env('DB_ALT_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'strict'    => false
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

  ];

} else {
  Log::error("Please check that your .env file has values for all variables starting with DB_ and DB_ALT_");
}
