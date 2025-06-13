<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dinlr OAuth Client ID
    |--------------------------------------------------------------------------
    |
    | This is the client ID provided when you registered your application with
    | Dinlr's partner platform.
    |
    */
    'client_id'     => env('DINLR_CLIENT_ID', 'LBUEEETXDVCPYIIPMTVGDLEPLDCVSMIC'),

    /*
    |--------------------------------------------------------------------------
    | Dinlr OAuth Client Secret
    |--------------------------------------------------------------------------
    |
    | This is the client secret provided when you registered your application
    | with Dinlr's partner platform.
    |
    */
    'client_secret' => env('DINLR_CLIENT_SECRET', 'RHLPJRVFJJDWUFWOLGEPXCVIHTTDJEQG'),

    /*
    |--------------------------------------------------------------------------
    | Dinlr OAuth Redirect URI
    |--------------------------------------------------------------------------
    |
    | This is the redirect URI you registered with your Dinlr application.
    | This must exactly match one of the redirect URIs you registered.
    |
    */
    'redirect_uri'  => env('DINLR_REDIRECT_URI', 'https://yins.3b.my/dinlrauthorize'),

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
    | Access Token
    |--------------------------------------------------------------------------
    |
    | The OAuth access token. This will be set automatically when the OAuth
    | flow is completed. You should not need to set this manually.
    |
    */
    'access_token'  => env('DINLR_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    |
    | The OAuth refresh token. This will be set automatically when the OAuth
    | flow is completed. You should not need to set this manually.
    |
    */
    'refresh_token' => env('DINLR_REFRESH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | The timestamp when the access token expires. This will be set
    | automatically when the OAuth flow is completed. You should not need
    | to set this manually.
    |
    */
    'expires_at'    => env('DINLR_TOKEN_EXPIRES_AT'),

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
