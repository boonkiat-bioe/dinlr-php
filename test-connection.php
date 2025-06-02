<?php
require 'vendor/autoload.php';

use Nava\Dinlr\Client;

// Test with your actual credentials
$config = [
    'api_key'       => 'DINLR_TEST_API_KEY',
    'restaurant_id' => 'dinlr-b1',
    'api_url'       => 'https://api.dinlr.com/v1',
];

echo "Testing API Connection...\n";
echo "API URL: " . $config['api_url'] . "\n";
echo "Restaurant ID: " . $config['restaurant_id'] . "\n";
echo "API Key: " . substr($config['api_key'], 0, 10) . "...\n\n";

$client = new Client($config);

// Test basic connection
try {
    echo "1. Testing restaurant endpoint...\n";
    $restaurant = $client->restaurant()->get();
    echo "✅ Success! Restaurant: " . $restaurant->getName() . "\n\n";

    echo "2. Testing locations endpoint...\n";
    $locations = $client->locations()->list();
    echo "✅ Success! Found " . count($locations) . " locations\n";

    if (count($locations) > 0) {
        $location = $locations->first();
        echo "   First location: " . $location->getName() . "\n\n";
    }

} catch (\Nava\Dinlr\Exception\ApiException $e) {
    echo "❌ API Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";

    if ($e->getCode() == 404) {
        echo "   Possible causes:\n";
        echo "   - Invalid restaurant_id\n";
        echo "   - API endpoints not available for your plan\n";
    } elseif ($e->getCode() == 401) {
        echo "   Possible causes:\n";
        echo "   - Invalid API key\n";
        echo "   - API key not authorized for this restaurant\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
