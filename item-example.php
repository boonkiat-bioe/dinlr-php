<?php
require 'vendor/autoload.php';

use Nava\Dinlr\Client;
use Nava\Dinlr\Exception\ApiException;

// Configuration
$config = [
    'api_key'       => 'YOUR_API_KEY',
    'restaurant_id' => 'dinlr-b1',
    // Optional: Set custom API URL if not using the default
    // 'api_url' => 'https://api.dinlr.com/v1',
];

try {
    // Initialize the client
    $client = new Client($config);

    echo "Connected to Dinlr API\n";
    echo "Restaurant: " . $client->restaurant()->get()->getName() . "\n\n";

    // Get the first location ID
    $locations = $client->locations()->list();
    if (count($locations) === 0) {
        throw new \Exception("No locations found for this restaurant");
    }

    $locationId = $locations->first()->getId();
    echo "Using location: " . $locations->first()->getName() . " (ID: $locationId)\n\n";

    // Get all items for this location
    echo "Fetching menu items...\n";
    $items = $client->items()->list($locationId);
    echo "Found " . count($items) . " items\n\n";

    // Display items
    foreach ($items as $index => $item) {
        if ($index >= 5) {
            echo "... and " . (count($items) - 5) . " more items\n\n";
            break;
        }

        echo "Item: " . $item->getName() . "\n";
        echo "  ID: " . $item->getId() . "\n";
        echo "  Description: " . ($item->getDescription() ?: 'N/A') . "\n";
        echo "  Variants: " . count($item->getVariants()) . "\n";

        // Display first variant if available
        $variants = $item->getVariants();
        if (count($variants) > 0) {
            echo "    - " . $variants[0]['name'] . ": " .
            ($variants[0]['price'] ?? 'N/A') . " " .
            $client->restaurant()->get()->getCurrency() . "\n";
        }

        // Display modifiers if available
        $modifiers = $item->getModifiers();
        if (count($modifiers) > 0) {
            echo "  Modifiers: " . count($modifiers) . "\n";

            // Fetch full modifier details for the first modifier
            if (isset($modifiers[0]['id'])) {
                try {
                    $modifier = $client->modifiers()->get($modifiers[0]['id']);
                    echo "    - " . $modifier->getName() . " (Options: " .
                    count($modifier->getModifierOptions()) . ")\n";
                } catch (ApiException $e) {
                    echo "    - Could not fetch modifier details\n";
                }
            }
        }

        echo "\n";
    }

    // Get all categories
    echo "Fetching categories...\n";
    $categories = $client->categories()->list();
    echo "Found " . count($categories) . " categories\n\n";

    // Display categories in a hierarchical format
    $topLevelCategories = [];
    $subCategories      = [];

    foreach ($categories as $category) {
        if ($category->isTopLevel()) {
            $topLevelCategories[$category->getId()] = $category;
        } else {
            $subCategories[$category->getParentCategory()][] = $category;
        }
    }

    foreach ($topLevelCategories as $categoryId => $category) {
        echo "Category: " . $category->getName() . "\n";

        if (isset($subCategories[$categoryId])) {
            foreach ($subCategories[$categoryId] as $subCategory) {
                echo "  - " . $subCategory->getName() . "\n";
            }
        }

        echo "\n";
    }

    // Get all modifiers
    echo "Fetching modifiers...\n";
    $modifiers = $client->modifiers()->list($locationId);
    echo "Found " . count($modifiers) . " modifiers\n\n";

    // Display first few modifiers
    foreach ($modifiers as $index => $modifier) {
        if ($index >= 3) {
            echo "... and " . (count($modifiers) - 3) . " more modifiers\n";
            break;
        }

        echo "Modifier: " . $modifier->getName() . "\n";
        echo "  Required: " . ($modifier->isRequired() ? "Yes" : "No") . "\n";
        echo "  Max Selection: " . ($modifier->getMaxSelection() ?: "Unlimited") . "\n";

        $options = $modifier->getModifierOptions();
        echo "  Options: " . count($options) . "\n";

        foreach ($options as $optIndex => $option) {
            if ($optIndex >= 3) {
                echo "    ... and " . (count($options) - 3) . " more options\n";
                break;
            }

            echo "    - " . $option['name'] . ": " .
                ($option['price'] ? $option['price'] . " " . $client->restaurant()->get()->getCurrency() : "Free") .
                ($option['default_selected'] ? " (Default)" : "") . "\n";
        }

        echo "\n";
    }

} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n";

    if ($e->getErrorData()) {
        echo "Error data: " . json_encode($e->getErrorData(), JSON_PRETTY_PRINT) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
