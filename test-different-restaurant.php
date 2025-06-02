<?php
require 'vendor/autoload.php';

use Nava\Dinlr\Client;

// Test with different restaurant IDs
$testRestaurants = [
    'demo',
    'test',
    'sandbox',
    'example',
    'dinlr-b1',
];

$config = [
    'api_key' => 'DINLR_TEST_API_KEY', // Use your real key here
    'api_url' => 'https://api.dinlr.com/v1',
];

foreach ($testRestaurants as $restaurantId) {
    echo "Testing restaurant ID: {$restaurantId}\n";
    echo "Full URL: https://api.dinlr.com/v1/{$restaurantId}/onlineorder/restaurant\n";

    $config['restaurant_id'] = $restaurantId;
    $client                  = new Client($config);

    try {
        $restaurant = $client->restaurant()->get();
        echo "✅ SUCCESS! Restaurant: " . $restaurant->getName() . "\n\n";
        break; // Found working restaurant
    } catch (\Nava\Dinlr\Exception\ApiException $e) {
        echo "❌ Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n\n";
    }
}
