<?php
/**
 * Test configuration file for Dinlr PHP client library
 *
 * This configuration is used for running unit tests.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Dinlr API key for testing. This should be a test API key, not a
    | production key.
    |
    */
    'api_key'       => getenv('DINLR_TEST_API_KEY') ?: 'test_api_key',

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Dinlr API for testing. This may point to a sandbox
    | environment.
    |
    */
    'api_url'       => getenv('DINLR_TEST_API_URL') ?: 'https://api.dinlr.com/v1',

    /*
    |--------------------------------------------------------------------------
    | Restaurant ID
    |--------------------------------------------------------------------------
    |
    | Default restaurant ID to use for testing.
    |
    */
    'restaurant_id' => getenv('DINLR_TEST_RESTAURANT_ID') ?: 'dinlr-b1',

    /*
    |--------------------------------------------------------------------------
    | Test Timeout
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout in seconds for test requests.
    |
    */
    'timeout'       => (int) (getenv('DINLR_TEST_TIMEOUT') ?: 5),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debug information will be logged during tests.
    |
    */
    'debug'         => (bool) (getenv('DINLR_TEST_DEBUG') ?: true),

    /*
    |--------------------------------------------------------------------------
    | Test Data
    |--------------------------------------------------------------------------
    |
    | Sample data that can be used for testing various API endpoints.
    |
    */
    'test_data'     => [
        'customer' => [
            'first_name' => 'Test',
            'last_name'  => 'Customer',
            'email'      => 'test.customer@example.com',
            'phone'      => '+1234567890',
        ],
        'order'    => [
            'location' => 'test_location_id',
            'items'    => [
                [
                    'item' => 'test_item_id',
                    'qty'  => 1,
                ],
            ],
        ],
        'payment'  => [
            'payment' => 'test_payment_method_id',
            'amount'  => 100.00,
        ],
    ],
];
