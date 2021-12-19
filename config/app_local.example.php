<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */

use Cake\Http\Exception\MissingControllerException;
use Cake\Http\Middleware\CsrfProtectionMiddleware;

return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * The locale of the application
     *
     * Currently available translations:
     * - en_US
     * - de_CH
     */
    'App' => [
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'de_CH'),
    ],

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', '__SALT__'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306 ),

            'username' => env('DB_USERNAME' ),
            'password' => env( 'DB_PASSWORD' ),

            'database' => env( 'DB_DATABASE' ),
            /*
             * If not using the default 'public' schema with the PostgreSQL driver
             * set it here.
             */
            //'schema' => 'myapp',

            /*
             * You can use a DSN string to set the entire configuration
             */
            'url' => env('DATABASE_URL', null),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'host' => env('DB_TEST_HOST', 'localhost'),
            'port' => env('DB_TEST_PORT', 3306 ),
            'username' => env('DB_TEST_USERNAME' ),
            'password' => env( 'DB_TEST_PASSWORD' ),
            'database' => env( 'DB_TEST_DATABASE' ),
            //'schema' => 'myapp',
            'url' => env('DATABASE_TEST_URL', null),
        ],
    ],

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'host' => 'localhost',
            'port' => 25,
            'username' => null,
            'password' => null,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    /*
     * Do not log missing controller exceptions as they appear on every call to
     * a non existing route by any visitor
     */
    'Error' => [
        'skipLog' => [MissingControllerException::class, CsrfProtectionMiddleware::class],
    ],
];
