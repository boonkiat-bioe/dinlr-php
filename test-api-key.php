<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client as HttpClient;

echo "Testing API Key Direct...\n";

$apiKey       = 'YOUR_ACTUAL_API_KEY'; // Put your real key here
$restaurantId = 'dinlr-b1';

$httpClient = new HttpClient([
    'base_uri'    => 'https://api.dinlr.com/v1',
    'headers'     => [
        'Authorization' => 'Bearer ' . $apiKey,
        'Accept'        => 'application/json',
    ],
    'http_errors' => false,
]);

echo "Making request to: /{$restaurantId}/onlineorder/restaurant\n";

$response = $httpClient->get("/{$restaurantId}/onlineorder/restaurant");

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getBody() . "\n";

if ($response->getStatusCode() === 401) {
    echo "\n❌ Invalid API Key\n";
} elseif ($response->getStatusCode() === 404) {
    echo "\n❌ Restaurant ID not found or not accessible\n";
} elseif ($response->getStatusCode() === 200) {
    echo "\n✅ Success! API Key and Restaurant ID are valid\n";
}
