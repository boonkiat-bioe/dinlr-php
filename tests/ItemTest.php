<?php
namespace Nava\Dinlr\Tests;

use Nava\Dinlr\Client;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var array
     */
    protected $testConfig;
    protected static $locationId;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        echo "\n\n✅ TEST CASE: Items, Modifiers and Categories API";
        echo "\n==============================================================";
        echo "\nSetting up test configuration...";

        // Load config
        $this->testConfig = require __DIR__ . '/config.php';

        echo "\n• API URL: " . $this->testConfig['api_url'];

        // Retrieve location ID once and reuse it, to avoid API rate limits
        if (self::$locationId === null) {
            $client = new Client($this->testConfig);
            try {
                $locations  = $client->locations()->list();
            } catch (\Nava\Dinlr\Exception\ApiException $ex) {
                echo "\n❌ Error retrieving locations: " . $ex->getMessage() . ' ' . $ex->getCode();
                throw $ex;
            }

            if (count($locations) > 0) {
                self::$locationId = $locations->first()->getId();
                echo "\n• Using location ID: " . self::$locationId;
            } else {
                $this->markTestSkipped("No locations found for testing.");
            }
        }

        echo "\n• Restaurant ID: " . $this->testConfig['restaurant_id'];
        echo "\n--------------------------------------------------------------";
    }

    /**
     * Test getting all items
     */
    public function testGetItems()
    {
        echo "\n\nSTEP 1: Testing get all items";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);
        $locationId = self::$locationId;

        $items = $client->items()->list($locationId);

        echo "\n• Total items: " . count($items);

        if (count($items) > 0) {
            $item = $items->first();
            echo "\n• First item ID: " . $item->getId();
            echo "\n• First item name: " . $item->getName();

            // Test variants
            $variants = $item->getVariants();
            echo "\n• Variants count: " . count($variants);
            if (count($variants) > 0) {
                echo "\n• First variant name: " . $variants[0]['name'];
                echo "\n• First variant price: " . $variants[0]['price'];
            }

            // Test modifiers
            $modifiers = $item->getModifiers();
            echo "\n• Modifiers count: " . count($modifiers);
        }

        echo "\n✓ Items retrieved successfully";
        echo "\n--------------------------------------------------------------\n\n";

        $this->assertInstanceOf(\Nava\Dinlr\Models\ItemCollection::class, $items);
    }

    /**
     * Test getting all modifiers
     */
    public function testGetModifiers()
    {
        echo "\n\nSTEP 2: Testing get all modifiers";
        echo "\n--------------------------------------------------------------";

        $client = new Client($this->testConfig);
        $locationId = self::$locationId;
        $modifiers = $client->modifiers()->list($locationId);

        echo "\n• Total modifiers: " . count($modifiers);

        if (count($modifiers) > 0) {
            $modifier = $modifiers->first();
            echo "\n• First modifier ID: " . $modifier->getId();
            echo "\n• First modifier name: " . $modifier->getName();

            // Test modifier options
            $options = $modifier->getModifierOptions();
            echo "\n• Options count: " . count($options);
            if (count($options) > 0) {
                echo "\n• First option name: " . $options[0]['name'];
                echo "\n• First option price: " . $options[0]['price'];
            }
        }

        echo "\n✓ Modifiers retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\ModifierCollection::class, $modifiers);
    }

    /**
     * Test getting all categories
     */
    public function testGetCategories()
    {
        echo "\n\nSTEP 3: Testing get all categories";
        echo "\n--------------------------------------------------------------";

        $client     = new Client($this->testConfig);
        $categories = $client->categories()->list();

        echo "\n• Total categories: " . count($categories);

        if (count($categories) > 0) {
            $category = $categories->first();
            echo "\n• First category ID: " . $category->getId();
            echo "\n• First category name: " . $category->getName();
            echo "\n• Parent category: " . ($category->getParentCategory() ?: 'None');
        }

        echo "\n✓ Categories retrieved successfully";

        $this->assertInstanceOf(\Nava\Dinlr\Models\CategoryCollection::class, $categories);
    }
}
