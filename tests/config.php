<?php return array (
  'api_key' => 'a07e4566c79e644da0a345f481a3e9ae',
  'api_url' => 'https://api.dinlr.com/v1',
  'restaurant_id' => 'f4dd56dd-8d7e-4485-8d7d-a6ab32251954',
  'timeout' => 5,
  'debug' => true,
  'test_data' => 
  array (
    'customer' => 
    array (
      'first_name' => 'Test',
      'last_name' => 'Customer',
      'email' => 'test.customer@example.com',
      'phone' => '+1234567890',
    ),
    'order' => 
    array (
      'location' => 'test_location_id',
      'items' => 
      array (
        0 => 
        array (
          'item' => 'test_item_id',
          'qty' => 1,
        ),
      ),
    ),
    'payment' => 
    array (
      'payment' => 'test_payment_method_id',
      'amount' => 100.0,
    ),
  ),
  'client_id' => 'LBUEEETXDVCPYIIPMTVGDLEPLDCVSMIC',
  'client_secret' => 'RHLPJRVFJJDWUFWOLGEPXCVIHTTDJEQG',
  'redirect_uri' => 'https://dinlr.my/dinlrauthorize',
  'test_oauth_data' => 
  array (
    'state' => 'fromNava',
    'callback' => 
    array (
      'code' => '16e1a165cfa443109fe20d34f1c57c17',
      'restaurant_id' => 'f4dd56dd-8d7e-4485-8d7d-a6ab32251954',
    ),
    'token_response' => 
    array (
      'access_token' => 'a07e4566c79e644da0a345f481a3e9ae',
      'refresh_token' => '20eae0fdad5f450f76b4f57d2195f592',
      'expires_in' => 1209600,
      'token_type' => 'bearer',
    ),
  ),
);