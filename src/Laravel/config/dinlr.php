<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dinlr API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key used to authenticate with the Dinlr API.
    |
    */
    'api_key'       => env('DINLR_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Dinlr API URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Dinlr API.
    |
    */
    'api_url'       => env('DINLR_API_URL', 'https://api.dinlr.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Restaurant ID
    |--------------------------------------------------------------------------
    |
    | The default restaurant ID to use if none is provided.
    |
    */
    'restaurant_id' => env('DINLR_RESTAURANT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for API requests in seconds.
    |
    */
    'timeout'       => env('DINLR_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debug information will be logged.
    |
    */
    'debug'         => env('DINLR_DEBUG', false),
];
