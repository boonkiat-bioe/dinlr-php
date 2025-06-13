<?php
/**
 * Test configuration file for Dinlr PHP client library OAuth
 *
 * This configuration is used for running OAuth unit tests.
 */

// Get the base configuration
$config = require __DIR__ . '/config.php';

// Add OAuth-specific configuration
return array_merge($config, [
    /*
    |--------------------------------------------------------------------------
    | OAuth Client ID
    |--------------------------------------------------------------------------
    |
    | Your Dinlr OAuth client ID for testing.
    |
    */
    'client_id'       => getenv('DINLR_TEST_CLIENT_ID') ?: 'LBUEEETXDVCPYIIPMTVGDLEPLDCVSMIC',

    /*
    |--------------------------------------------------------------------------
    | OAuth Client Secret
    |--------------------------------------------------------------------------
    |
    | Your Dinlr OAuth client secret for testing.
    |
    */
    'client_secret'   => getenv('DINLR_TEST_CLIENT_SECRET') ?: 'RHLPJRVFJJDWUFWOLGEPXCVIHTTDJEQG',

    /*
    |--------------------------------------------------------------------------
    | OAuth Redirect URI
    |--------------------------------------------------------------------------
    |
    | The redirect URI for OAuth testing.
    |
    */
    'redirect_uri'    => getenv('DINLR_TEST_REDIRECT_URI') ?: 'https://dinlr.my/dinlrauthorize',

    /*
    |--------------------------------------------------------------------------
    | Test OAuth Data
    |--------------------------------------------------------------------------
    |
    | Sample data that can be used for testing OAuth workflow.
    |
    */
    'test_oauth_data' => [
        'state'          => 'fromNava',
        'callback'       => [
            'code'          => 'test_auth_code',
            'restaurant_id' => 'test_restaurant_id',
        ],
        'token_response' => [
            'access_token'  => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in'    => 1209600,
            'token_type'    => 'bearer',
        ],
    ],
]);
